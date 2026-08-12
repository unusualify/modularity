<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Blueprint;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;

/**
 * Catalog of ModuleRoute Blueprint fields (ADR field map).
 *
 * @see docs/src/pages/system-reference/adr-module-route-blueprint.md
 */
final class BlueprintFieldCatalog
{
    /**
     * @return array<string, array{
     *     surface: string,
     *     class_suffix: string,
     *     stub: string,
     *     interface: class-string,
     *     legacy_config_key: string,
     *     nested_key: string,
     *     default: bool,
     *     return_doc: string
     * }>
     */
    public static function definitions(): array
    {
        return [
            'inputs' => [
                'surface' => 'Form',
                'class_suffix' => 'FormInputs',
                'stub' => 'blueprint-form-inputs',
                'interface' => ModuleRouteInputsProvider::class,
                'legacy_config_key' => 'inputs',
                'nested_key' => 'form.inputs',
                'default' => true,
                'return_doc' => 'list<array<string, mixed>>',
            ],
            'columns' => [
                'surface' => 'Index',
                'class_suffix' => 'IndexColumns',
                'stub' => 'blueprint-index-columns',
                'interface' => ModuleRouteHeadersProvider::class,
                'legacy_config_key' => 'headers',
                'nested_key' => 'index.columns',
                'default' => true,
                'return_doc' => 'list<array<string, mixed>>',
            ],
            'options' => [
                'surface' => 'Index',
                'class_suffix' => 'IndexOptions',
                'stub' => 'blueprint-index-options',
                'interface' => ModuleRouteTableOptionsProvider::class,
                'legacy_config_key' => 'table_options',
                'nested_key' => 'index.options',
                'default' => false,
                'return_doc' => 'array<string, mixed>',
            ],
            'index_with' => [
                'surface' => 'Index',
                'class_suffix' => 'IndexWith',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'index_with',
                'nested_key' => 'index.with',
                'default' => false,
                'return_doc' => 'array<string, mixed>|list<string>',
            ],
            'index_appends' => [
                'surface' => 'Index',
                'class_suffix' => 'IndexAppends',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'index_appends',
                'nested_key' => 'index.appends',
                'default' => false,
                'return_doc' => 'list<string>',
            ],
            'filters' => [
                'surface' => 'Index',
                'class_suffix' => 'IndexFilters',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'table_filters',
                'nested_key' => 'index.filters',
                'default' => false,
                'return_doc' => 'array<string, mixed>',
            ],
            'advanced_filters' => [
                'surface' => 'Index',
                'class_suffix' => 'IndexAdvancedFilters',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'filters',
                'nested_key' => 'index.advanced_filters',
                'default' => false,
                'return_doc' => 'array<string, mixed>',
            ],
            'actions' => [
                'surface' => 'Index',
                'class_suffix' => 'IndexActions',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'table_actions',
                'nested_key' => 'index.actions',
                'default' => false,
                'return_doc' => 'list<array<string, mixed>>',
            ],
            'row_actions' => [
                'surface' => 'Index',
                'class_suffix' => 'IndexRowActions',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'table_row_actions',
                'nested_key' => 'index.row_actions',
                'default' => false,
                'return_doc' => 'array<string, mixed>',
            ],
            'form_options' => [
                'surface' => 'Form',
                'class_suffix' => 'FormOptions',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'form_options',
                'nested_key' => 'form.options',
                'default' => false,
                'return_doc' => 'array<string, mixed>',
            ],
            'form_with' => [
                'surface' => 'Form',
                'class_suffix' => 'FormWith',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'form_with',
                'nested_key' => 'form.with',
                'default' => false,
                'return_doc' => 'array<string, mixed>|list<string>',
            ],
            'form_appends' => [
                'surface' => 'Form',
                'class_suffix' => 'FormAppends',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'form_appends',
                'nested_key' => 'form.appends',
                'default' => false,
                'return_doc' => 'list<string>',
            ],
            'form_actions' => [
                'surface' => 'Form',
                'class_suffix' => 'FormActions',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'form_actions',
                'nested_key' => 'form.actions',
                'default' => false,
                'return_doc' => 'array<string, mixed>|list<array<string, mixed>>',
            ],
            'bulk_sheet' => [
                'surface' => 'BulkSheet',
                'class_suffix' => 'BulkSheet',
                'stub' => 'blueprint-provider',
                'interface' => ModuleRouteBlueprintProvider::class,
                'legacy_config_key' => 'bulk_sheet',
                'nested_key' => 'bulk_sheet',
                'default' => false,
                'return_doc' => 'array<string, mixed>',
            ],
        ];
    }

    /**
     * Normalize CLI / alias field name to catalog key.
     */
    public static function normalize(string $field): ?string
    {
        $field = strtolower(trim(str_replace(['-', ' '], '_', $field)));

        $aliases = [
            'inputs' => 'inputs',
            'form_inputs' => 'inputs',
            'form.inputs' => 'inputs',
            'columns' => 'columns',
            'headers' => 'columns',
            'index_columns' => 'columns',
            'index.columns' => 'columns',
            'options' => 'options',
            'table_options' => 'options',
            'index_options' => 'options',
            'index.options' => 'options',
            'with' => 'index_with',
            'index_with' => 'index_with',
            'index.with' => 'index_with',
            'appends' => 'index_appends',
            'index_appends' => 'index_appends',
            'index.appends' => 'index_appends',
            'filters' => 'filters',
            'table_filters' => 'filters',
            'index_filters' => 'filters',
            'index.filters' => 'filters',
            'advanced_filters' => 'advanced_filters',
            'index.advanced_filters' => 'advanced_filters',
            'actions' => 'actions',
            'table_actions' => 'actions',
            'index_actions' => 'actions',
            'index.actions' => 'actions',
            'row_actions' => 'row_actions',
            'table_row_actions' => 'row_actions',
            'index.row_actions' => 'row_actions',
            'form_options' => 'form_options',
            'form.options' => 'form_options',
            'form_with' => 'form_with',
            'form.with' => 'form_with',
            'form_appends' => 'form_appends',
            'form.appends' => 'form_appends',
            'form_actions' => 'form_actions',
            'form.actions' => 'form_actions',
            'bulk_sheet' => 'bulk_sheet',
            'bulksheet' => 'bulk_sheet',
            'bulk' => 'bulk_sheet',
        ];

        return $aliases[$field] ?? (isset(self::definitions()[$field]) ? $field : null);
    }

    /**
     * @return list<string>
     */
    public static function defaultFieldKeys(): array
    {
        return array_keys(array_filter(
            self::definitions(),
            static fn (array $def): bool => (bool) $def['default']
        ));
    }

    /**
     * @return list<string>
     */
    public static function allFieldKeys(): array
    {
        return array_keys(self::definitions());
    }

    /**
     * @return array{
     *     surface: string,
     *     class_suffix: string,
     *     stub: string,
     *     interface: class-string,
     *     legacy_config_key: string,
     *     nested_key: string,
     *     default: bool,
     *     return_doc: string
     * }
     */
    public static function get(string $field): array
    {
        $key = self::normalize($field);
        if ($key === null) {
            throw new \InvalidArgumentException(
                "Unknown Blueprint field [{$field}]. Expected one of: " . implode(', ', self::allFieldKeys())
            );
        }

        return self::definitions()[$key];
    }
}
