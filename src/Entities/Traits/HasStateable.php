<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\SystemNotification\Events\StateableUpdated;
use Unusualify\Modularity\Entities\Scopes\StateableScopes;
use Unusualify\Modularity\Entities\State;
use Unusualify\Modularity\Entities\Stateable;

trait HasStateable
{
    use StateableScopes;

    public $_preserved_stateable;

    protected static $stateModel = 'Modules\SystemUtility\Entities\State';

    protected static $hasStateableFillable = [
        'initial_stateable',
        'stateable_id',
    ];

    protected $customInitialStateable = null;

    protected $modelStateableIsUpdating = false;

    protected $modelStateableIsUpdatingId = null;

    public static function bootHasStateable(): void
    {
        self::saving(static function (Model $model) {
            $model->startOffStateable();

            $model->stateableUpdatingCheck();

            $model->clearStateableFillable();
        });

        self::created(static function (Model $model) {
            $model->createNonExistantStates();
        });

        self::retrieved(static function (Model $model) {
            $state = $model->state;

            if (! is_null($state)) {
                $model->setAttribute('stateable_id', $state->id);
            }
        });

        self::saved(static function (Model $model) {
            $model->updateStateable();
        });
    }

    public function initializeHasStateable()
    {
        $this->append(['state_formatted']);

        $this->mergeFillable(static::$hasStateableFillable);
    }

    public function statees(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        $defaultStates = $this->getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        return $this->morphToMany(
            static::$stateModel,
            'stateable',
            // config('modularity.states.table', 'stateables'),
            modularityConfig('tables.stateables', 'um_stateables'),
            'stateable_id',
            'state_id'
        )
            ->orWhereIn('code', $defaultStateCodes);
        // ->withPivot('is_active');
    }

    public function states(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        $defaultStates = $this->getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        return $this->hasMany(
            static::$stateModel,
            'id',
            'created_at'
        )->orWhereIn('code', $defaultStateCodes);
    }

    public function state(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        $stateableTable = (new Stateable)->getTable();

        return $this->hasOneThrough(
            static::$stateModel,
            Stateable::class,
            'stateable_id',
            'id',
            'id',
            'state_id'
        )
        // ->where(modularityConfig('tables.stateables', 'um_stateables') . '.is_active', 1)
            ->where($stateableTable . '.stateable_type', get_class($this));
    }

    public function stateable(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Stateable::class, 'stateable');
    }

    public function hydrateState(State $state)
    {
        $stateConfig = $this->getRawStateConfiguration($state->code);

        // Fill with icon and color from configuration
        $state->fill(Arr::only($stateConfig, ['icon', 'color']));

        // Set translation fields if they exist in configuration
        if (! empty($stateConfig)) {
            foreach (self::getStateableTranslationLanguages() as $locale) {
                if (isset($stateConfig[$locale])) {
                    // Get or create translation for this locale
                    $findIndex = $state->translations->search(function ($translation) use ($locale) {
                        return $translation->locale === $locale;
                    });
                    if ($findIndex !== false) {
                        // Fill translation with data from configuration
                        foreach ($stateConfig[$locale] as $field => $value) {
                            if (in_array($field, $state->getTranslatedAttributes())) {
                                $state->translations[$findIndex]->setAttribute($field, $value);
                            }
                        }

                    }

                }
            }
        }

        return $state;
    }

    /**
     * Get the state relationship with configuration applied.
     *
     * @return mixed
     */
    public function getStateAttribute()
    {
        $originalState = $this->getRelationValue('state');

        if (! $originalState) {
            return null;
        }

        return $this->hydrateState($originalState);
    }

    public static function getStateModel()
    {
        return static::$stateModel;
    }

    public static function getStateableTranslationLanguages()
    {
        return [
            app()->getLocale(),
        ];
    }

    protected function stateableCode(): Attribute
    {
        return new Attribute(
            get: fn () => $this->state ? $this->state->code : null,
        );
    }

    protected function stateFormatted(): Attribute
    {
        // $state = $this->state;
        $state = $this->getStateAttribute();

        return new Attribute(
            get: fn () => method_exists($this, 'setStateFormatted')
                ? $this->setStateFormatted($state)
                : ($state
                    ? "<span class='text-{$state->color} mdi-{$state->icon}'>{$state->name}</span>"
                    : "<span class='text-grey mdi-alert-circle-outline'>No State</span>")
        );
    }

    /**
     * @return array
     */
    protected static function formatStateableState(array|string $state, array $defaultAttributes = [])
    {
        $defaultAttributes = array_merge([
            'icon' => '$info',
            'color' => 'info',
        ], $defaultAttributes ?? [], Arr::only(is_array($state) ? $state : [], ['icon', 'color']));

        $code = null;
        if (is_string($state)) {
            if ($state === '') {
                throw new \InvalidArgumentException('State cannot be empty string');
            }
            $code = $state;
        } elseif (is_array($state)) {
            try {
                $code = $state['code'];
            } catch (\Throwable $th) {
                throw new \InvalidArgumentException('State must have a code attribute');
            }
        }

        $defaultName = Str::headline($code);
        $nameAttribute = (is_array($state) && isset($state['name'])) ? $state['name'] : $defaultName;

        $translations = array_reduce(self::getStateableTranslationLanguages(), function ($carry, $lang) use ($defaultName, $nameAttribute) {
            if (is_string($nameAttribute)) {
                $name = $nameAttribute;
            } elseif (is_array($nameAttribute) && isset($nameAttribute[$lang])) {
                $name = $nameAttribute[$lang];
            } else {
                $name = $defaultName;
            }

            $carry[$lang] = [
                'name' => $name,
                'active' => true,
            ];

            return $carry;
        }, []);

        return [
            ...$defaultAttributes,
            ...$translations,
            'code' => $code,
        ];
    }

    public static function getDefaultState()
    {
        if (isset(static::$default_state)) {
            if (! is_array(static::$default_state)) {
                throw new \InvalidArgumentException('Default state must be an array');
            }

            return Arr::only(static::$default_state, ['icon', 'color']);
        }

        return self::getInitialState() ?? [
            'code' => 'default',
            'icon' => '$info',
            'color' => 'info',
        ];
    }

    public static function getInitialState()
    {
        if (isset(static::$initial_state)) {
            return self::formatStateableState(static::$initial_state ?? []);
        }

        return isset(static::$default_states[0])
            ? self::formatStateableState(static::$default_states[0] ?? [])
            : null;
    }

    public static function getDefaultStates()
    {
        return array_map(function ($state) {
            return self::formatStateableState($state, self::getDefaultState());
        }, static::$default_states ?? []);
    }

    public static function getRawStateConfiguration($code)
    {
        $state = [];

        foreach (self::getDefaultStates() as $state) {
            if (is_string($state)) {
                if ($state === $code) {
                    break;
                }
            } elseif (is_array($state) && isset($state['code'])) {
                if ($state['code'] === $code) {
                    $state = $state;

                    break;
                }
            }
        }

        return $state;
    }

    public static function getStateConfiguration($code)
    {
        $state = self::getRawStateConfiguration($code);

        return Arr::only($state, ['icon', 'color']);
    }

    protected function createNonExistantStates()
    {
        $defaultStates = $this->getDefaultStates();
        $initialState = $this->customInitialStateable ?? $this->getInitialState();

        foreach ($defaultStates as $state) {
            $stateModel = State::where('code', $state['code'])->first() ?? State::create($state);
            $isActive = $state['code'] === $initialState['code'];
            // $this->states()->attach($stateModel->id, ['is_active' => $isActive]);
            if ($isActive) {
                $this->stateable()->updateOrCreate(['stateable_id' => $this->id, 'stateable_type' => get_class($this)], ['state_id' => $stateModel->id]);
            }
        }
    }

    /**
     * Get the default stateables with the number of items in each state.
     *
     * @param array $scopes
     * @return array
     *
     * @deprecated Use StateableTrait::getStateableFilterList instead
     */
    public static function defaultStateables($scopes = [])
    {
        $defaultStates = self::getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        return State::whereIn('code', $defaultStateCodes)
            ->get()
            ->map(function ($state) use ($scopes) {
                $studlyCode = Str::studly($state->code);

                // dd(static::query()->toRawSql());
                $number = static::handleScopes(static::query(), $scopes)
                    ->isStateableCount($state->code);

                return [
                    'name' => $state->name ?? $state->translations->first()->name,
                    'code' => $state->code,
                    'slug' => "isStateable{$studlyCode}",
                    'number' => $number,
                ];
            })
            ->sortBy(function ($state) use ($defaultStateCodes) {
                return array_search($state['code'], $defaultStateCodes);
            })
            ->filter(function ($state) {
                return $state['number'] > 0;
            })
            ->values()
            ->toArray();
    }

    protected function clearStateableFillable()
    {
        $this->offsetUnset('_stateable');
        $this->offsetUnset('_status');
        foreach (static::$hasStateableFillable as $field) {
            $this->offsetUnset($field);
        }
    }

    protected function startOffStateable()
    {
        if (isset($this->initial_stateable)) {
            $defaultStates = $this->getDefaultStates();

            $initialStateFound = collect($defaultStates)->firstWhere('code', $this->initial_stateable);

            if ($initialStateFound) {
                $this->customInitialStateable = $initialStateFound;
            }
        }
    }

    protected function setPreservedStateable()
    {
        if (isset($this->_status)) {
            dd(
                $this->_status,
                $this->_preserved_stateable,
                $this->_stateable,
            );
            $this->_preserved_stateable = $this->_stateable;
        }
    }

    protected function stateableUpdatingCheck()
    {
        if (isset($this->stateable_id) && $this->stateable->id !== $this->stateable_id) {
            $this->modelStateableIsUpdating = true;
            $this->modelStateableIsUpdatingId = $this->stateable_id;
        }
    }

    protected function isModelStateableUpdating()
    {
        return $this->modelStateableIsUpdating;
    }

    protected function getModelStateableUpdatingId()
    {
        return $this->modelStateableIsUpdatingId;
    }

    protected function updateStateable()
    {
        if (! $this->modelStateableIsUpdating) {
            return;
        }

        $newState = State::find($this->modelStateableIsUpdatingId);

        if (is_null($newState)) {
            return;
        }

        $oldState = $this->state;

        $this->stateable()->update(['state_id' => $newState->id]);

        if ($oldState && $oldState->code !== $newState->code) {
            $cloneModel = clone $this;
            $cloneModel->refresh();
            StateableUpdated::dispatch($cloneModel, $cloneModel->state, $oldState);
        }
    }

    public static function syncStateData(): array
    {
        $defaultStates = self::getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        $absentStateCodes = array_diff($defaultStateCodes, State::pluck('code')->toArray());

        $absentStates = array_values(array_filter($defaultStates, function ($state) use ($absentStateCodes) {
            return in_array($state['code'], $absentStateCodes);
        }));

        $newStates = [];

        foreach ($absentStates as $absentState) {
            $newState = State::create($absentState);
            $newStates[] = $newState;
        }

        return $newStates;
    }
}
