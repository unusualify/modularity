<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Str;

trait StateableTrait
{
    public function getStateableFilterList(): array
    {
        $model = $this->getModel();
        $query = $model::query();
        $scopes = [];

        // foreach ($this->traitsMethods('filter') as $method) {
        //     $this->$method($query, $scopes);
        // }
        $this->filter($query, $scopes);

        $defaultStates = $model::getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        return $model::getStateModel()::whereIn('code', $defaultStateCodes)
            ->get()
            ->map(function ($state) use ($query) {
                $q = clone $query;
                $studlyCode = Str::studly($state->code);

                // $number = $q->isStateableCount($state->code);

                return [
                    'name' => $state->name ?? $state->translations->first()->name,
                    'code' => $state->code,
                    'slug' => "isStateable{$studlyCode}",
                    'number' => $q->isStateableCount($state->code),
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

    public function getTableFiltersStateableTrait($scope): array
    {
        $model = $this->getModel();

        $defaultStates = $model::getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        return $model::getStateModel()::whereIn('code', $defaultStateCodes)
            ->get()
            ->map(function ($state) use ($scope) {
                $studlyCode = Str::studly($state->code);

                return [
                    'name' => $state->name ?? $state->translations->first()->name,
                    'code' => $state->code,
                    'slug' => "isStateable{$studlyCode}",
                    'methods' => 'getCountByStatusSlug',
                    'params' => [$state->code, $scope],
                    ...(isset(static::$stateableFilterUserRoles) ? ['allowedRoles' => static::$stateableFilterUserRoles] : []),
                ];
            })
            ->sortBy(function ($state) use ($defaultStateCodes) {
                return array_search($state['code'], $defaultStateCodes);
            })
            ->values()
            ->toArray();
    }

    public function getCountByStatusSlugStateableTrait($slug, $scope = [])
    {
        $model = $this->getModel();

        if ($model) {

            $defaultStates = $model::getDefaultStates();
            $defaultStateCodes = array_column($defaultStates, 'code');

            if (in_array($slug, $defaultStateCodes)) {
                $query = $model::query();
                $scopes = [];

                $this->filter($query, $scopes);

                return $query->isStateableCount($slug);
            }
        }

        return false;
    }

    public function getStateableList($itemValue = 'name')
    {
        $model = $this->getModel();
        $defaultStates = $model::getDefaultStates();
        $defaultStateCodes = array_column($defaultStates, 'code');

        return $model::getStateModel()::whereIn('code', $defaultStateCodes)
            ->get()
            ->sortBy(function ($state) use ($defaultStateCodes) {
                return array_search($state->code, $defaultStateCodes);
            })
            ->map(function ($state) use ($itemValue) {
                return [
                    'id' => $state->id,
                    $itemValue => $state->name,
                ];
            })
            ->values()
            ->toArray();
    }
}
