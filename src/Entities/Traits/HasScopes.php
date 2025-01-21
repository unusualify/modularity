<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PDO;

trait HasScopes
{
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

    public function scopeOnlyTrashed($query)
    {
        return $query->whereNotNull("{$this->getTable()}.deleted_at");
    }

    public static function handleScopes($query, $scopes = [])
    {
        $likeOperator = 'LIKE';

        if (DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $likeOperator = 'ILIKE';
        }

        if (isset($scopes['exceptIds'])) {
            $query->whereNotIn(static::getTable() . '.id', $scopes['exceptIds']);
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
