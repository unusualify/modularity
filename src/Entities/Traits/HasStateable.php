<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\State;
use Illuminate\Support\Facades\Log;
use Modules\SystemUtility\Entities\Stateable;
use Illuminate\Support\Str;

trait HasStateable {
    protected static $stateModel = 'Modules\SystemUtility\Entities\State';

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
            $model->createNonExistantStates($model);
        });
        self::retrieved(static function (Model $model) {
            $state = $model->states->first(function($state) {
                return $state->pivot->is_active === 1;
            });

            if (!is_null($state)) {
                // Get matching default state if exists
                $defaultState = collect($model->default_states)->firstWhere('code', $state->code);

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

                $model->setAttribute('_state', $state->id);
                $model->setAttribute(
                    '_status',
                    $model->previewState($state)
                );
            } else {
                $model->setAttribute(
                    '_status',
                    $model->previewWhenStateNull($state)
                );
            }
        });
        self::saving(static function (Model $model) {

            if(isset($model->_status)){
                $model->preserved_state = $model->_state;
            }

            $model->offsetUnset('_state');
            $model->offsetUnset('_status');
        });

        self::saved(static function (Model $model) {
            $newState = State::find($model->preserved_state);

            if(is_null($newState))
                return false;

            $currentActiveState = $model->states()
                ->wherePivot('is_active', 1)
                ->first();

            if ($currentActiveState && $currentActiveState->id !== $newState->id) {
                $model->states()->updateExistingPivot($currentActiveState->id, [
                    'is_active' => 0
                ]);
            }

            $existingRelationship = $model->states()
                ->where('state_id', $newState->id)
                ->first();

            if ($existingRelationship) {
                $model->states()->updateExistingPivot($newState->id, [
                    'is_active' => 1
                ]);
            } else {
                $model->states()->attach($newState->id, [
                    'is_active' => 1
                ]);
            }
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

        foreach ($defaultStates as $index => $state) {

            if(is_string($state))
                $stateData = [
                    'code' => $state
                ];
            else
                $stateData = $state;

            if(is_string($state)){
                foreach ($translationLangs as $lang) {
                    $stateData[$lang] = [
                        'name' => Str::headline($state),
                        'active' => true,
                    ];
                }
            }else {
                foreach ($translationLangs as $lang) {
                    $stateData[$lang] = [
                        'name' => Str::headline($state['code']),
                        'active' => true,
                    ];
                }
            }
            $allStates[] = $stateData;
        }

        if(!isset($model->inititalState))
            $initialState = $model->default_states[0];
        else
            $initialState = $model->initial_state;

        if(is_string($initialState)){
            $initialState = [
                'name' => Str::headline($initialState),
                'icon' => '$warning',
                'color' => 'warning'
            ];
        }

        if(!isset($model->default_state))
            $defaultState = $model->default_state;
        else
            $defaultState = $initialState;

        if(is_string($defaultState)){
            $defaultState = [
                'name' => Str::headline($defaultState),
                'icon' => '$warning',
                'color' => 'warning'
            ];
        }

        foreach ($allStates as $state) {

            if(is_string($state)){
                array_merge($state, [
                    'color' => $defaultState['color'],
                    'icon' => $defaultState['icon'],
                ]);

            }
            $_state = State::create($state);

            $pivotData = ['is_active' => false];

            if($state['code'] == $initialState['code']) {
                $pivotData['is_active'] = true;
            }

            $model->states()->attach($_state->id, $pivotData);
        }
    }

    public function getStateTranslationLanguages()
    {
        return [
            app()->getLocale()
        ];
    }

    public function previewState($state){
        return "<span variant='text' color='{$state->color}' prepend-icon='{$state->icon}'>{$state->translatedAttribute('name')[app()->getLocale()]}</span>";
    }
    public function previewWhenStateNull(){
        return "<span variant='text' color='' prepend-icon=''>No State</span>";
    }
}
