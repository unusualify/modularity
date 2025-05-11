<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Entities\Process;

trait HasProcesses
{
    /**
     * The relationships on this model that themselves
     * use the Processable trait.
     *
     * e.g. ['pressReleasePackages','pressReleasePackageAddons']
     *
     * @return string[]
     */
    protected static function processableRelationships(): array
    {
        return static::$hasProcessesRelationships ?? [];
    }

    protected function getProcessesRawSqlAndBindings($additionalWheres = [])
    {
        $sqlParts = [];
        $bindings = [];
        $parentKey = $this->getKey();

        foreach (static::processableRelationships() as $relationName) {
            // skip non-existent or non-methods
            if (! method_exists($this, $relationName)) {
                continue;
            }

            // grab the HasMany (or MorphMany, etc.) instance
            $relation = $this->{$relationName}();
            $relatedModel = $relation->getRelated();
            $table = $relatedModel->getTable();
            $foreignKey = $relation->getForeignKeyName();
            $type = get_class($relatedModel);

            // dd($additionalWheres);
            $additionalWheresSql = Collection::make($additionalWheres)->map(function ($value, $column) {
                return "{$column} = '{$value}'";
            })->implode(' AND ');

            if (count($additionalWheres) > 0) {
                $additionalWheresSql = " AND {$additionalWheresSql}";
            }

            // build one chunk of the WHERE‐IN
            $sqlParts[] = "
                (processable_type = ?
                    {$additionalWheresSql}
                 AND processable_id IN (
                   SELECT id
                   FROM `{$table}`
                   WHERE `{$foreignKey}` = ?
                 )
                )
            ";

            $bindings[] = $type;
            $bindings[] = $parentKey;
        }

        // if nothing matched, force an always‐false clause
        $fullSql = count($sqlParts)
            ? implode(' OR ', $sqlParts)
            : '0 = 1';

        return [$fullSql, $bindings];
    }

    /**
     * Return a HasMany relation whose only WHERE clauses
     * come from our dynamic sub‐queries.
     */
    public function processes(): HasMany
    {
        [$fullSql, $bindings] = $this->getProcessesRawSqlAndBindings();

        // wrap in noConstraints so Laravel does NOT auto‐inject
        // `where processable_id = parent.id`
        return Relation::noConstraints(function () use ($fullSql, $bindings) {
            return $this
                ->hasMany(Process::class, 'processable_id', 'id')
                ->whereRaw($fullSql, $bindings);
        });
    }

    public function confirmedProcesses(): HasMany
    {
        [$fullSql, $bindings] = $this->getProcessesRawSqlAndBindings([
            'status' => 'confirmed',
        ]);

        // dd($fullSql, $bindings);

        // wrap in noConstraints so Laravel does NOT auto‐inject
        // `where processable_id = parent.id`
        return Relation::noConstraints(function () use ($fullSql, $bindings) {
            return $this
                ->hasMany(Process::class, 'processable_id', 'id')
                ->whereRaw($fullSql, $bindings);
        });
    }

    public function rejectedProcesses(): HasMany
    {
        [$fullSql, $bindings] = $this->getProcessesRawSqlAndBindings([
            'status' => 'rejected',
        ]);

        // dd($fullSql, $bindings);

        // wrap in noConstraints so Laravel does NOT auto‐inject
        // `where processable_id = parent.id`
        return Relation::noConstraints(function () use ($fullSql, $bindings) {
            return $this
                ->hasMany(Process::class, 'processable_id', 'id')
                ->whereRaw($fullSql, $bindings);
        });
    }
}
