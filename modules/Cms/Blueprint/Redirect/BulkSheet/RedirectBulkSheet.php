<?php

declare(strict_types=1);

namespace Modules\Cms\Blueprint\Redirect\BulkSheet;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

/**
 * CSV bulk sheet options + column schema for Cms Redirect.
 *
 * Imperative import/validate/commit/export stay on {@see \Modules\Cms\Http\Controllers\RedirectController}.
 */
final class RedirectBulkSheet implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'export_download_filename' => 'redirects-export.csv',
            'step_up_ability' => 'redirect.bulk_import',
            'preview_table_columns' => [
                ['title' => 'Line', 'key' => 'line', 'width' => '72px'],
                ['title' => 'OK', 'key' => 'valid', 'sortable' => false],
                ['title' => 'Action', 'key' => 'action'],
                ['title' => 'Locale', 'key' => 'locale'],
                ['title' => 'From', 'key' => 'from_path'],
                ['title' => 'To', 'key' => 'to_path'],
                ['title' => 'Errors', 'key' => 'errors', 'sortable' => false],
                ['title' => 'Warnings', 'key' => 'warnings', 'sortable' => false],
            ],
            'api_route_names' => [
                'dryRun' => 'bulk.dryRun',
                'commit' => 'bulk.commit',
                'export' => 'bulk.export',
            ],
            'fields' => [
                ['key' => 'locale', 'label' => 'Locale', 'required' => true, 'aliases' => ['locale']],
                ['key' => 'from_path', 'label' => 'From path', 'required' => true, 'aliases' => ['from', 'source']],
                ['key' => 'to_path', 'label' => 'To path', 'required' => true, 'aliases' => ['to', 'target', 'destination']],
                ['key' => 'status_code', 'label' => 'Status code', 'required' => false, 'aliases' => ['code']],
                ['key' => 'is_active', 'label' => 'Active', 'required' => false, 'aliases' => ['active']],
            ],
        ];
    }
}
