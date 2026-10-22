<?php

namespace Unusualify\Modularous\Repositories\Logic;

use Illuminate\Database\Concerns\CompilesJsonPaths;
use Illuminate\Database\Query\Builder;
use Unusualify\Modularous\Facades\Modularous;

trait CollationSelector
{
    use CompilesJsonPaths;

    private $collationSelectorColumns = [
        'char',
        'varchar',
        'tinytext',
        'text',
        'mediumtext',
        'longtext',
        'enum',
        'set',
    ];

    /**
     * @var bool
     */
    protected $shouldUseSearchCollation = false;

    /**
     * Set the should use search collation value.
     *
     * @param bool $value
     */
    public function setShouldUseSearchCollation($value)
    {
        $this->shouldUseSearchCollation = $value;

        return $this;
    }

    /**
     * Determine if the query is a collation query.
     *
     * @return bool
     */
    protected function isCollationQuery($query)
    {
        return $query->getConnection()->getDriverName() === 'mysql';
    }

    /**
     * Determine if the query should use collation for search.
     *
     * @return bool
     */
    public function shouldUseSearchCollation($query)
    {
        return (Modularous::shouldUseCollationForSearch() || $this->shouldUseSearchCollation) && $this->isCollationQuery($query);
    }

    public function getCollationSelectorColumns()
    {
        return $this->collationSelectorColumns;
    }

    /**
     * Add search collation to the query.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function addSearchCollationToQuery($query, string $field, $value, $model = null)
    {
        $config = $query->getConnection()->getConfig();
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';
        $isRelationshipModel = ! is_null($model);
        $model = $model ?? $this->getModel();
        $columnTypes = method_exists($model, 'getColumnTypes') ? $model->getColumnTypes() : [];

        $fieldParts = explode('.', $field);
        $fieldName = array_pop($fieldParts);

        // Check if this is a JSON field (contains -> or ->>)
        if (str_contains($fieldName, '->')) {
            // For JSON fields, cast to CHAR first to avoid binary collation issues
            $wrappedField = $query->getGrammar()->wrap($fieldName);

            return $query->whereRaw('CAST(' . $wrappedField . ' AS CHAR) COLLATE ' . $collation . ' LIKE ?', ['%' . $value . '%']);
        }

        if (isset($columnTypes[$fieldName]) && in_array($columnTypes[$fieldName], $this->getCollationSelectorColumns())) {
            $wrappedField = $query->getGrammar()->wrap($fieldName);
            $fieldParts[] = $wrappedField;

            return $query->orWhereRaw(
                implode('.', $fieldParts) . " COLLATE {$collation} LIKE ?",
                ['%' . $value . '%']
            );
        }

        return $query->orWhere($field, $this->getLikeOperator(), '%' . $value . '%');

    }
}
