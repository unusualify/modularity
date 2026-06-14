<?php

namespace Unusualify\Modularous\Entities\Traits\Core;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;
use Unusualify\Modularous\Traits\Traitify;

trait HasScopes
{
    use Traitify;

    public static function bootHasScopes()
    {
        static::setFeatureGlobalScopes();
    }

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
        if ($this->publishFieldIsTranslated('published')) {
            return $query->whereHas('translations', function ($query) {
                $query->where($this->getLocaleKey(), $this->resolvePublishScopeLocale())
                    ->where('published', true);
            });
        }

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
        $this->applyPublishDateVisibilityScope($query, 'publish_start_date', '<=', Carbon::now());
        $this->applyPublishDateVisibilityScope($query, 'publish_end_date', '>=', Carbon::now());

        return $query;
    }

    public function scopeDraft($query)
    {
        if ($this->publishFieldIsTranslated('published')) {
            return $query->whereHas('translations', function ($query) {
                $query->where($this->getLocaleKey(), $this->resolvePublishScopeLocale())
                    ->where('published', false);
            });
        }

        return $query->where("{$this->getTable()}.published", false);
    }

    /**
     * Whether a publish-related column is stored on the translation row for the active locale.
     */
    protected function publishFieldIsTranslated(string $field): bool
    {
        return method_exists($this, 'isTranslationAttribute')
            && $this->isTranslationAttribute($field);
    }

    /**
     * Locale used by publish/visibility scopes (current app locale unless the model overrides it).
     */
    protected function resolvePublishScopeLocale(): string
    {
        if (method_exists($this, 'locale')) {
            $locale = $this->locale();

            if (is_string($locale) && $locale !== '') {
                return $locale;
            }
        }

        return app()->getLocale();
    }

    /**
     * Apply publish window filtering for one date column on the correct table (main vs translation).
     *
     * @param Builder $query
     * @param string $field
     * @param string $operator
     * @param \DateTimeInterface|string $value
     */
    protected function applyPublishDateVisibilityScope($query, string $field, string $operator, $value): void
    {
        if (! $this->publishFieldIsApplicable($field)) {
            return;
        }

        if ($this->publishFieldIsTranslated($field)) {
            $query->whereHas('translations', function ($query) use ($field, $operator, $value) {
                $query->where($this->getLocaleKey(), $this->resolvePublishScopeLocale())
                    ->where(function ($query) use ($field, $operator, $value) {
                        $query->whereNull($field)
                            ->orWhere($field, $operator, $value);
                    });
            });

            return;
        }

        $table = $this->getTable();

        $query->where(function ($query) use ($table, $field, $operator, $value) {
            $query->whereNull("{$table}.{$field}")
                ->orWhere("{$table}.{$field}", $operator, $value);
        });
    }

    /**
     * Whether a publish date column participates in visibility filtering for this model.
     */
    protected function publishFieldIsApplicable(string $field): bool
    {
        if ($this->publishFieldIsTranslated($field)) {
            return true;
        }

        return $this->isFillable($field);
    }

    /**
     * Scope to filter records between two dates
     *
     * @param Builder $query
     * @param string $column
     * @param string $startDate
     * @param string $endDate
     * @return Builder
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
     * @param Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return Builder
     */
    public function scopeCreatedAtBetween($query, $startDate, $endDate)
    {
        return $query->between('created_at', $startDate, $endDate);
    }

    /**
     * Scope to filter records between two dates
     *
     * @param Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return Builder
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

    public static function setFeatureGlobalScopes()
    {
        $class = get_called_class();

        foreach (static::staticTraitsMethods('addGlobalScopes') as $method) {
            $scopes = $class::$method();
            foreach ($scopes as $scopeName => $scope) {
                $class::addGlobalScope($scopeName, $scope['scope']);
            }
        }
    }

    public static function getUncountableGlobalScopes(): array
    {
        $uncountableScopes = [];
        foreach (static::staticTraitsMethods('addGlobalScopes') as $method) {
            $scopes = static::$method();
            foreach ($scopes as $scopeName => $scope) {
                if (($scope['count'] ?? false) === false) {
                    $uncountableScopes[] = $scopeName;
                }
            }
        }

        return $uncountableScopes;
    }

    public function newCountQuery()
    {
        return $this->withoutGlobalScopes(static::getUncountableGlobalScopes())->newQuery();
    }
}
