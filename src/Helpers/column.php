<?php

use Unusualify\Modularity\Hydrates\HeaderHydrator;
use Unusualify\Modularity\Module;

if (! function_exists('configure_table_columns')) {
    function configure_table_columns(array $columns, ?Module $module = null, ?string $routeName = null)
    {
        return array_map(fn ($column) => (new HeaderHydrator($column, $module, $routeName))->hydrate(), $columns);
    }
}

if (! function_exists('hydrate_table_column_translation')) {
    function hydrate_table_column_translation(array $column)
    {
        if (! isset($column['title'])) {
            return;
        }

        $title = $column['title'];

        $tableHeader = 'table-headers.' . $title;

        $translation = __($tableHeader);

        if (! is_array($translation) && $translation !== $tableHeader) {
            $column['title'] = $translation;
        }

        return $column;
    }
}

if (! function_exists('hydrate_table_columns_translations')) {
    function hydrate_table_columns_translations(array $columns)
    {
        return array_map('hydrate_table_column_translation', $columns);
    }
}
