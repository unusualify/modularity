<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

trait ApiFiltering
{
    /**
     * Available filters
     *
     * @var array
     */
    protected $availableFilters = [];


    /**
     * Available search
     *
     * @var array
     */
    protected $availableSearch = [];

    /**
     * Available filters
     *
     * @var array
     */
    protected $availableScopes = [];

    /**
     * Get filters from request
     *
     * @return array
     */
    protected function getFilters(): array
    {
        $filters = [];

        foreach ($this->availableFilters as $filter) {
            if ($this->request->has($filter)) {
                $filters[$filter] = $this->request->get($filter);
            }
        }

        // Handle search parameter
        $searchParam = $this->request->get('q') ?? $this->request->get('search');

        if ($searchParam && !empty($this->availableSearch)) {
            $filters['searches'] = $this->availableSearch;
            $filters['search'] = $searchParam;

            foreach ($this->availableSearch as $field) {
                $filters[$field] = $searchParam;
            }
        }

        // Handle date range filters
        if ($this->request->has('created_from')) {
            $filters['created_from'] = $this->request->get('created_from');
        }

        if ($this->request->has('created_to')) {
            $filters['created_to'] = $this->request->get('created_to');
        }

        return $filters;
    }

    /**
     * Get scopes from request
     *
     * @return array
     */
    protected function getScopes(): array
    {
        $scopes = [];

        // Handle status filters
        if ($this->request->has('status')) {
            $scopes['status'] = $this->request->get('status');
        }

        // Handle published filter
        if ($this->request->has('published')) {
            $scopes['published'] = $this->request->get('published') === 'true';
        }

        // Handle trashed filter
        if ($this->request->has('trashed') && class_uses_recursive($this->repository->getModel())
            && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($this->repository->getModel()))) {
            $trashed = $this->request->get('trashed');
            if ($trashed === 'only') {
                $scopes['onlyTrashed'] = true;
            } elseif ($trashed === 'with') {
                $scopes['withTrashed'] = true;
            }
        }

        // Handle scopes parameter
        $scopesParam = $this->request->get('scopes', '');

        if ($scopesParam && !empty($this->availableScopes)) {
            $requestScopes = explode(',', $scopesParam);
            foreach ($requestScopes as $scope) {
                if (in_array($scope, $this->availableScopes)) {
                    $scopes[$scope] = true;
                }
            }
        }

        return $scopes;
    }

    /**
     * Validate filters from request
     *
     * @return array
     */
    protected function validateFilters(): array
    {
        $rules = [
            'search' => 'string|max:255',
            'created_from' => 'date',
            'created_to' => 'date|after_or_equal:created_from',
            'status' => 'string|in:active,inactive,pending',
            'published' => ['sometimes', function($attribute, $value, $fail) {
                if ($value !== null && $value !== 'true' && $value !== 'false') {
                    $fail('The '.$attribute.' must be either true or false.');
                }
            }],
            'trashed' => 'string|in:only,with,without',
            'scopes' => ['sometimes', function($attribute, $value, $fail) {
                if (!is_string($value) && !is_array($value)) {
                    $fail('The '.$attribute.' must be either a string or an array.');
                }

                $scopes = is_string($value) ? explode(',', $value) : $value;
                foreach($scopes as $scope) {
                    if (!in_array($scope, $this->availableScopes)) {
                        $fail('The '.$attribute.' contains invalid scope: '.$scope);
                    }
                }
            }],
        ];

        // Add validation rules for available filters
        foreach ($this->availableFilters as $filter) {
            $rules[$filter] = 'string|max:255';
        }

        return $this->validateApi($rules);
    }
}
