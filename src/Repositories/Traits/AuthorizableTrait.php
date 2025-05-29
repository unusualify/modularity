<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait AuthorizableTrait
{
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @return void
     */
    public function filterAuthorizableTrait($query, &$scopes)
    {
        // set, cast, unset or manipulate the scopes by using query and scopes
        // $scopes['hasAuthorization'] = true;
    }

    public function getTableFiltersAuthorizableTrait($scope): array
    {
        $model = $this->getModel();

        $tableFilters = [];

        if ($model->hasAuthorizationUsage()) {
            $tableFilters[] = [
                'name' => ___('listing.filter.authorized'),
                'slug' => 'authorized',
                'methods' => 'getCountFor',
                'params' => ['hasAnyAuthorization'],
            ];

            $tableFilters[] = [
                'name' => ___('listing.filter.unauthorized'),
                'slug' => 'unauthorized',
                'methods' => 'getCountFor',
                'params' => ['unauthorized'],
            ];
        }

        $tableFilters[] = [
            'name' => ___('listing.filter.your-authorizations'),
            'slug' => 'your-authorizations',
            'methods' => 'getCountFor',
            'params' => ['isAuthorizedToYou'],
        ];

        return $tableFilters;
    }
}
