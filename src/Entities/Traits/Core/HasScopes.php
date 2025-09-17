<?php

namespace Unusualify\Modularity\Entities\Traits\Core;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;

trait HasScopes
{
    public static function hasScope(string $scopeName): bool
    {
        $builder = static::query();

        // Check method exists
        if (method_exists($builder, $scopeName)) {
            return true;
        }

        // Check macro exists
        if ($builder->hasMacro($scopeName)) {
            return true;
        }

        // Check model scope
        $self = new static;
        if ($self->hasNamedScope($scopeName)) {
            return true;
        }

        return false;
    }

    public function scopePublished($query)
    {
        return $query->where("{$this->getTable()}.published", true);
    }

    public function scopePublishedInListings($query)
    {
        if ($this->isFillable('public')) {
            $query->where("{$this->getTable()}.public", true);

        }

        return $query->published()->visible();
    }

    public function scopeVisible($query)
    {
        if ($this->isFillable('publish_start_date')) {
            $query->where(function ($query) {
                $query->whereNull("{$this->getTable()}.publish_start_date")->orWhere("{$this->getTable()}.publish_start_date", '<=', Carbon::now());
            });

            if ($this->isFillable('publish_end_date')) {
                $query->where(function ($query) {
                    $query->whereNull("{$this->getTable()}.publish_end_date")->orWhere("{$this->getTable()}.publish_end_date", '>=', Carbon::now());
                });
            }
        }

        return $query;
    }

    public function scopeDraft($query)
    {
        return $query->where("{$this->getTable()}.published", false);
    }

    /**
     * Scope to filter records between two dates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetween($query, $column, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween("{$this->getTable()}.$column", [$startDate, $endDate]);
        }

        return $query;
    }

    /**
     * Scope to filter records between two dates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedAtBetween($query, $startDate, $endDate)
    {
        return $query->between('created_at', $startDate, $endDate);
    }

    /**
     * Scope to filter records between two dates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpdatedAtBetween($query, $startDate, $endDate)
    {
        return $query->between('updated_at', $startDate, $endDate);
    }

    public static function handleScopes($query, $scopes = [])
    {
        $likeOperator = 'LIKE';

        if (DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $likeOperator = 'ILIKE';
        }

        if (isset($scopes['exceptIds'])) {
            $query->whereNotIn((new static)->getTable() . '.id', $scopes['exceptIds']);
            unset($scopes['exceptIds']);
        }

        foreach ($scopes as $column => $value) {
            $studlyColumn = Str::studly($column);

            if (method_exists(static::class, 'scope' . $studlyColumn)) {
                if (! is_bool($value)) {
                    $query->{Str::camel($column)}($value);
                } else {
                    $query->{Str::camel($column)}();
                }
            } elseif (is_string($value) && method_exists(static::class, 'scope' . Str::studly($value))) {
                $query->{Str::camel($value)}();

            } else {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } elseif ($column[0] == '%') {
                    $value && ($value[0] == '!') ? $query->where(mb_substr($column, 1), "not $likeOperator", '%' . mb_substr($value, 1) . '%') : $query->where(mb_substr($column, 1), $likeOperator, '%' . $value . '%');
                } elseif (isset($value[0]) && $value[0] == '!') {
                    $query->where($column, '<>', mb_substr($value, 1));
                } elseif ($value !== '') {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }
}
