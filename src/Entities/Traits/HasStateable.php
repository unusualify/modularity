<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\State;
use Illuminate\Support\Facades\Log;
use Modules\SystemUtility\Entities\Stateable;
use Illuminate\Support\Str;


trait HasStateable {
    protected static $stateModel = 'Modules\SystemUtility\Entities\State';
    /**
     * Cached source model fields.
     *
     * @var array
     */
    protected $_stateableSourceFields = [];

    /**
     * Original fillable attributes.
     *
     * @var array|null
     */
    protected $_originalFillable = null;

    public function states(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(
            static::$stateModel,
            'stateable',
            config('modularity.states.table', 'stateables'),
            'stateable_id',
            'state_id'
        )->withPivot('is_active', 'color', 'icon');
    }

    public function state(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            static::$stateModel,  // Target model (State)
            Stateable::class, // Intermediate table
            'stateable_id',      // Foreign key on stateables
            'id',                // Foreign key on states
            'id',                // Local key on this model
            'state_id'           // Local key on stateables
        )
        ->where(config('modularity.states.table', 'stateables').'.stateable_type', get_class($this))
        ->where(config('modularity.states.table', 'stateables').'.is_active', 1);
    }

    public static function bootHasStateable(): void
    {

        self::created(static function (Model $model) {

            // if (!isset($model->initial_state)) {
            //     Log::warning('No initial state found in created event');
            //     return;
            // }

            $model->createNonExistantStates($model);

        });

        self::retrieved(static function (Model $model) {
            $state = $model->states->first(function($state) {
                return $state->pivot->is_active === 1;
            });
            $model->setAttribute(
                '_state',
                $state->id
            );
            $model->setAttribute(
                '_status',
                "<v-chip variant='text' color='warning' prepend-icon='{$state->pivot->icon}'>{$state->translatedAttribute('name')[app()->getLocale()]}</v-chip>"
            );

        });

        self::saving(static function (Model $model) {

            if(isset($model->_status)){
                //Updating the model
                $model->preserved_state = $model->_state;
            }
            // Clean up temporary attributes
            $model->offsetUnset('_state');
            $model->offsetUnset('_status');

        });

        self::saved(static function (Model $model) {
            $newState = State::find($model->preserved_state);
            // Get current active state (if exists)
            if(is_null($newState))
                return false;
            $currentActiveState = $model->states()
                ->wherePivot('is_active', 1)
                ->first();

            // If current state exists and is different from new state
            if ($currentActiveState && $currentActiveState->id !== $newState->id) {
                // Set current state's is_active to 0
                $model->states()->updateExistingPivot($currentActiveState->id, [
                    'is_active' => 0
                ]);
            }
            // Check if relationship exists with new state
            $existingRelationship = $model->states()
            ->where('state_id', $newState->id)
            ->first();

            if ($existingRelationship) {
                // Update existing relationship to active
                $model->states()->updateExistingPivot($newState->id, [
                    'is_active' => 1
                ]);
            } else {
                // Create new relationship
                $model->states()->attach($newState->id, [
                    'is_active' => 1
                ]);
            }

            // dd($model->states()->get());
        });
    }

    public function initializeHasStateable()
    {
        $this->mergeFillable(['_state']);
    }

    public function createNonExistantStates($model)
    {
        $defaultStates = $model->default_states;
        $translationLangs = $model->getStateTranslationLanguages();
        $allStates = [];
        // dd($translationLangs);
        foreach ($defaultStates as $index => $state) {
            $stateData = [
                'code' => $state
            ];

            // Generate language specific structures
            foreach ($translationLangs as $lang) {
                $stateData[$lang] = [
                    'name' => Str::headline($state),
                    'active' => true,
                ];
            }

            $allStates[] = $stateData;
        }
        $initialState  = $model->initial_state;
        if(is_string($initialState)){
            $initialState = [
                'name' => $model->initial_state,
                'icon' => '$warning',
                'color' => 'warning'
            ];
        }
        // dd($initialState);
        foreach ($allStates as $state){

            $pivotData = [
                'is_active' => false,
                'color' => $model->initial_state['color'] ?? 'warning',
                'icon' => $model->initial_state['icon'] ?? '$warning',
            ];

            $_state = State::where('code', $state['code'])->first();
            // dd($_state);
            if(is_null($_state)){
                $_state = State::create($state);
            }
            // dd($state, $initialState['code']);
            if($state['code'] == $initialState['code'])
                // dd($state, $initialState['code']);
                $pivotData['is_active'] = true;

            $model->states()->attach($_state->id, $pivotData);

        }
    }

    public function getStateTranslationLanguages()
    {

        return [
            app()->getLocale()
        ];
    }
}
