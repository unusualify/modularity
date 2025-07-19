<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

trait ApiRelationships
{
    /**
     * Load relationships dynamically
     *
     * @param mixed $model
     * @param array $relationships
     * @return mixed
     */
    protected function loadRelationships($model, array $relationships)
    {
        if (empty($relationships)) {
            return $model;
        }

        $validRelationships = $this->validateRelationships($relationships);

        if (method_exists($model, 'load')) {
            return $model->load($validRelationships);
        }

        return $model;
    }

    /**
     * Validate relationships against available includes
     *
     * @param array $relationships
     * @return array
     */
    protected function validateRelationships(array $relationships): array
    {
        return array_intersect($relationships, $this->availableIncludes);
    }

    /**
     * Parse nested relationships
     *
     * @param array $relationships
     * @return array
     */
    protected function parseNestedRelationships(array $relationships): array
    {
        $parsed = [];

        foreach ($relationships as $relationship) {
            if (strpos($relationship, '.') !== false) {
                $parts = explode('.', $relationship);
                $current = &$parsed;

                foreach ($parts as $part) {
                    if (!isset($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            } else {
                $parsed[$relationship] = [];
            }
        }

        return $parsed;
    }

    /**
     * Check if relationship is allowed
     *
     * @param string $relationship
     * @return bool
     */
    protected function isRelationshipAllowed(string $relationship): bool
    {
        return in_array($relationship, $this->availableIncludes);
    }

    /**
     * Get relationship count
     *
     * @param mixed $model
     * @param string $relationship
     * @return int
     */
    protected function getRelationshipCount($model, string $relationship): int
    {
        if (!method_exists($model, $relationship)) {
            return 0;
        }

        $relation = $model->$relationship();

        if (method_exists($relation, 'count')) {
            return $relation->count();
        }

        return 0;
    }

    /**
     * Eager load relationships with constraints
     *
     * @param mixed $query
     * @param array $relationships
     * @return mixed
     */
    protected function eagerLoadWithConstraints($query, array $relationships)
    {
        foreach ($relationships as $relationship) {
            if (strpos($relationship, ':') !== false) {
                [$relation, $constraint] = explode(':', $relationship, 2);

                if ($this->isRelationshipAllowed($relation)) {
                    $query->with([$relation => function ($q) use ($constraint) {
                        $this->applyRelationshipConstraint($q, $constraint);
                    }]);
                }
            } else {
                if ($this->isRelationshipAllowed($relationship)) {
                    $query->with($relationship);
                }
            }
        }

        return $query;
    }

    /**
     * Apply constraint to relationship query
     *
     * @param mixed $query
     * @param string $constraint
     * @return void
     */
    protected function applyRelationshipConstraint($query, string $constraint): void
    {
        // Parse constraint (e.g., "limit:10", "where:active,1")
        $parts = explode(',', $constraint);
        $method = array_shift($parts);

        switch ($method) {
            case 'limit':
                $query->limit((int) $parts[0]);
                break;
            case 'where':
                if (count($parts) >= 2) {
                    $query->where($parts[0], $parts[1]);
                }
                break;
            case 'orderBy':
                $direction = $parts[1] ?? 'asc';
                $query->orderBy($parts[0], $direction);
                break;
        }
    }
}
