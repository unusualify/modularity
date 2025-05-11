<?php

if (! function_exists('hydrate_table_column')) {
    function hydrate_table_column(array $column)
    {
        // $this->hydrateHeaderSuffix($header);
        // add edit functionality to table title cell
        // if ($this->titleColumnKey == $header['key'] && ! isset($header['formatter'])) {
        //     $header['formatter'] = [
        //         'edit',
        //     ];
        // }

        // switch column
        if (isset($column['formatter']) && count($column['formatter']) && $column['formatter'][0] == 'switch') {
            $column['width'] = '20px';
        }

        // if (isset($header['sortable']) && $header['sortable']) {
        //     if (preg_match('/(.*)(_relation)/', $header['key'], $matches)) {
        //         $header['sortable'] = false;
        //     }
        // }

        if ($column['key'] == 'actions') {
            $column['width'] ??= '100px';
            $column['align'] ??= 'center';
            $column['sortable'] ??= false;
        }

        $column['visible'] ??= true;

        return $column;
    }
}

if (! function_exists('configure_table_column')) {
    function configure_table_column(array $column)
    {
        return array_merge_recursive_preserve(
            modularityConfig('default_header'),
            hydrate_table_column($column)
        );
    }
}

if (! function_exists('configure_table_columns')) {
    function configure_table_columns(array $columns)
    {
        return array_map('configure_table_column', $columns);
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
