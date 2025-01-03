<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\SystemUtility\Entities\Stateable;
use Unusualify\Modularity\Entities\State;

trait HasStateable
{
    public $_preserved_stateable;

    protected static $stateModel = 'Modules\SystemUtility\Entities\State';

    protected $_stateableSourceFields = [];

    protected $_originalFillable = null;

    public static function bootHasStateable(): void
    {
        self::saving(static function (Model $model) {
            if (isset($model->_status)) {
                $model->_preserved_stateable = $model->_stateable;
            }
            $model->offsetUnset('_stateable');
            $model->offsetUnset('_status');
        });

        self::created(static function (Model $model) {
            $model->createNonExistantStates();
        });

        self::retrieved(static function (Model $model) {
            $state = $model->states->first(function ($state) {
                return $state->pivot->is_active === 1;
            });

            if (! is_null($state)) {
                $defaultState = collect($model->getDefaultStates())->firstWhere('code', $state->code);

                if ($defaultState) {
                    $attributes = array_merge(
                        $state->toArray(),
                        array_merge(
                            $defaultState,
                            $state->attributesToArray()
                        )
                    );
                    $state->fill($attributes);
                }

                $model->setAttribute('_stateable', $state->id);
                $model->setAttribute(
                    '_status',
                    method_exists($model, 'setStateablePreview')
                        ? $model->setStateablePreview($state)
                        : "<span variant='text' color='{$state->color}' prepend-icon='{$state->icon}'>{$state->translatedAttribute('name')[app()->getLocale()]}</span>"
                );
            } else {
                $model->setAttribute(
                    '_status',
                    method_exists($model, 'setStateablePreviewNull')
                        ? $model->setStateablePreviewNull()
                        : "<span variant='text' color='' prepend-icon=''>No State</span>"
                );
            }
        });

        self::saved(static function (Model $model) {
            $newState = State::find($model->_preserved_stateable);
            if (is_null($newState)) {
                return false;
            }

            $currentActiveState = $model->states()
                ->wherePivot('is_active', 1)
                ->first();

            if ($currentActiveState && $currentActiveState->id !== $newState->id) {
                $model->states()->updateExistingPivot($currentActiveState->id, [
                    'is_active' => 0,
                ]);
            }

            $existingRelationship = $model->states()
                ->where('state_id', $newState->id)
                ->first();

            if ($existingRelationship) {
                $model->states()->updateExistingPivot($newState->id, [
                    'is_active' => 1,
                ]);
            } else {
                $model->states()->attach($newState->id, [
                    'is_active' => 1,
                ]);
            }
        });
    }

    public function initializeHasStateable()
    {
        $this->defaultLocale = app()->getLocale();

        $this->mergeFillable(['_stateable']);
    }

    protected static function setFormattedState($state, $defaultAttributes = null)
    {
        if (is_null($defaultAttributes)) {
            $defaultAttributes = [
                'icon' => '$warning',
                'color' => 'warning',
            ];
        }

        if (is_string($state)) {
            $stateData = [
                'code' => $state,
            ];

            // Add translations for each language
            foreach (self::getStateableTranslationLanguages() as $lang) {
                $stateData[$lang] = [
                    'name' => Str::headline($state),
                    'active' => true,
                ];
            }

            // Merge with default attributes
            $stateData = array_merge($stateData, $defaultAttributes);
        } else {
            $stateData = $state;
            // If translations are not set, add them
            if (! array_key_exists(app()->getLocale(), $stateData)) {
                foreach (self::getStateableTranslationLanguages() as $lang) {
                    $stateData[$lang] = [
                        'name' => Str::headline(
                            isset($stateData['name']) && isset($stateData['name'][$lang])
                                ? $stateData['name'][$lang]
                                : ($stateData['code'] ?? $state['code'])
                        ),
                        'active' => true,
                    ];
                }
            }
            // Merge with default attributes if icon or color is not set
            if (! isset($stateData['icon']) || ! isset($stateData['color'])) {
                $stateData = array_merge($defaultAttributes, $stateData);
            }
        }

        return $stateData;
    }

    public static function getDefaultStates()
    {
        return array_map(function ($state) {
            // dd($this->setFormattedState($state, $this->getDefaultState()));
            return self::setFormattedState($state, self::getDefaultState());
        }, static::$default_states ?? []);
    }

    public static function getDefaultState()
    {
        if (isset(static::$default_state)) {
            if (! isset(static::$default_state['code'])) {
                throw new \InvalidArgumentException('Default state must have a code attribute');
            }

            return self::setFormattedState(static::$default_state);
        }

        return self::getInitialState() ?? [
            'code' => 'default',
            'icon' => '$warning',
            'color' => 'warning',
        ];
    }

    public static function getInitialState()
    {
        if (isset(static::$initial_state)) {
            return self::setFormattedState(static::$initial_state);
        }

        return isset(static::$default_states[0])
            ? self::setFormattedState(static::$default_states[0])
            : null;
    }

    public function createNonExistantStates()
    {
        $defaultStates = $this->getDefaultStates();
        $initialState = $this->getInitialState();

        foreach ($defaultStates as $state) {
            $_state = State::where('code', $state['code'])->first() ?? State::create($state);
            $isActive = $state['code'] === $initialState['code'];
            $this->states()->attach($_state->id, ['is_active' => $isActive]);
        }
    }

    public function states(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(
            static::$stateModel,
            'stateable',
            config('modularity.states.table', 'stateables'),
            'stateable_id',
            'state_id'
        )->withPivot('is_active');
    }

    public function state(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            static::$stateModel,
            Stateable::class,
            'stateable_id',
            'id',
            'id',
            'state_id'
        )->where(config('modularity.states.table', 'stateables') . '.stateable_type', get_class($this))
            ->where(config('modularity.states.table', 'stateables') . '.is_active', 1);
    }

    public static function getStateableTranslationLanguages()
    {
        return [
            app()->getLocale(),
        ];
    }

    public static function getStateableList($itemValue = 'name')
    {
        $defaultStates = self::getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        return State::whereIn('code', $defaultStateCodes)
            ->get()
            ->sortBy(function($state) use ($defaultStateCodes, $itemValue)  {
                return array_search($state->code, $defaultStateCodes);
            })
            ->map(function($state) use ($itemValue) {
                return [
                    'id' => $state->id,
                    $itemValue => $state->name,
                ];
            })
            ->values()
            ->toArray();
    }

    public static function getStateableFilterList()
    {
        $defaultStates = self::getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        return State::whereIn('code', $defaultStateCodes)
            ->get()
            ->map(function($state) use ($defaultStates) {
                $studlyCode = Str::studly($state->code);
                return [
                    'name' => $state->name ?? $state->translations->first()->name,
                    'code' => $state->code,
                    'slug' => "stateable{$studlyCode}",
                    'number' => self::stateableCount($state->code),
                    // 'number' => static::query()->where('stateable', $state->code)->count(),
                ];
            })
            ->sortBy(function($state) use ($defaultStateCodes)  {
                return array_search($state['code'], $defaultStateCodes);
            })
            ->filter(function($state) {
                return $state['number'] > 0;
            })
            ->values()
            ->toArray();
    }

    public function scopeStateable($query, $code)
    {
        return $query->whereHas('state', function ($q) use ($code) {
            $q->where($q->getModel()->getTable() . '.code', $code);
        });
    }

    public function scopeStateableCount($query, $code)
    {
        return $query->stateable($code)->count();
    }

    public function __call($method, $parameters)
    {
        // if (Str::startsWith($method, 'stateable') && !Str::endsWith($method, 'Count')) {
        //     dd($method, $parameters);
        //     return $this->stateable(Str::after($method, 'scopeStateable'));
        // }

        return parent::__call($method, $parameters);
    }

    public function scopeDistributed()
    {
        return $this->scopeAuthorized($this)
            ->whereHas('states', function ($q) {
                $q->where('code', 'distributed')
                    ->where('stateables.is_active', 1);
            });
    }

    public function scopeDistributedCount()
    {

        return $this->scopeDistributed()->count();

    }

    // Since countries doesn't have a relation with States this can be added to PressRelease model
    public function scopeDistributedCountries()
    {
        // return $this->scopeDistributed()
        //     ->join('press_release_packages', 'press_releases.id', '=', 'press_release_packages.press_release_id')
        //     ->join('umod_snapshots', function($join) {
        //         $join->on('press_release_packages.id', '=', 'umod_snapshots.snapshotable_id')
        //             ->where('umod_snapshots.snapshotable_type', '=', 'Modules\PressRelease\Entities\PressReleasePackage');
        //     })
        //     ->join('packages', function($join) {
        //         $join->on('packages.id', '=', 'umod_snapshots.source_id')
        //             ->where('umod_snapshots.source_type', '=', 'Modules\Package\Entities\Package');
        //     })
        //     ->select(\DB::raw('COUNT(DISTINCT packages.packageable_id) as country_count'))
        //     ->where('packages.packageable_type', '=', 'Modules\Package\Entities\PackageCountry')
        //     ->value('country_count');
        return $this->scopeDistributed()
            ->join('press_release_packages', 'press_releases.id', '=', 'press_release_packages.press_release_id')
            ->join('umod_snapshots', function ($join) {
                $join->on('press_release_packages.id', '=', 'umod_snapshots.snapshotable_id')
                    ->where('umod_snapshots.snapshotable_type', '=', 'Modules\PressRelease\Entities\PressReleasePackage');
            })
            ->join('packages', function ($join) {
                $join->on('packages.id', '=', 'umod_snapshots.source_id')
                    ->where('umod_snapshots.source_type', '=', 'Modules\Package\Entities\Package');
            })
            ->leftJoin('package_regions', function ($join) {
                $join->on('package_regions.id', '=', 'packages.packageable_id')
                    ->where('packages.packageable_type', '=', 'Modules\Package\Entities\PackageRegion');
            })
            ->leftJoin('package_countries as region_countries', 'region_countries.package_region_id', '=', 'package_regions.id')
            ->leftJoin('package_countries as direct_countries', function ($join) {
                $join->on('direct_countries.id', '=', 'packages.packageable_id')
                    ->where('packages.packageable_type', '=', 'Modules\Package\Entities\PackageCountry');
            })
            ->select(\DB::raw('COUNT(DISTINCT COALESCE(region_countries.id, direct_countries.id)) as country_count'))
            ->value('country_count');

        // $test = $this->scopeDistributed()
        // ->join('press_release_packages', 'press_releases.id', '=', 'press_release_packages.press_release_id')
        // ->join('umod_snapshots', function($join) {
        //     $join->on('press_release_packages.id', '=', 'umod_snapshots.snapshotable_id')
        //         ->where('umod_snapshots.snapshotable_type', '=', 'Modules\PressRelease\Entities\PressReleasePackage');
        // })
        // ->join('packages', function($join) {
        //     $join->on('packages.id', '=', 'umod_snapshots.source_id')
        //         ->where('umod_snapshots.source_type', '=', 'Modules\Package\Entities\Package');
        // })
        // ->select(\DB::raw('COUNT(DISTINCT packages.packageable_id) as country_count'))
        // ->where('packages.packageable_type', '=', 'Modules\Package\Entities\PackageCountry')
        // ->value('country_count');
        // dd($test);
    }
}
