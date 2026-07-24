<?php

namespace Unusualify\Modularous\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Unusualify\Modularous\Contracts\CanBulkSheet;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\CoreController;
use Unusualify\Modularous\Http\Controllers\Traits\Table\TableActions;
use Unusualify\Modularous\Services\BulkCsv\BulkImportService;

/**
 * CSV bulk sheet: index toolbar action + panel routes ({@see bulkSheetTool}, dry-run, commit, export).
 *
 * Requires the host controller to implement {@see CanBulkSheet} and define {@see $moduleName} / {@see $routeName}
 * (see {@see CoreController}).
 *
 * Optional overrides: protected hooks {@see bulkSheetToolbarIntroFallback}, {@see bulkSheetToolHeadlineFallback},
 * {@see bulkSheetBreadcrumbsItems}, or extend public methods after composing this trait.
 *
 * {@see TableActions::setTableActions()} invokes
 * {@see setTableActionsManageBulkSheet()} when this trait is used.
 */
trait ManageBulkSheet
{
    protected function setTableActionsManageBulkSheet(): void
    {
        if (! $this instanceof CanBulkSheet || ! $this->module) {
            return;
        }

        $def = $this->bulkSheetToolbarDefinition();
        if ($def === [] || empty($def['href'])) {
            return;
        }

        $toolKey = $this->bulkSheetToolKey();
        if ($toolKey === '') {
            return;
        }

        $position = $def['position'] ?? 'append';

        $bulkTool = [
            'toolKey' => $toolKey,
            'sheetFields' => $this->bulkSheetFields(),
        ];

        $tooltipItems = array_map(function ($item) {
            return [
                'label' => ($item['label'] ?? $item['key']) . ($item['required'] ? ' *' : '') . ($item['aliases'] ? ' (' . implode(', ', $item['aliases']) . ')' : ''),
            ];
        }, $this->bulkSheetFields());

        if (! empty($def['intro'])) {
            $bulkTool['intro'] = $def['intro'];
        }

        $action = [
            'label' => $def['label'] ?? __('Bulk import / export'),
            'href' => $def['href'],
            'target' => $def['target'] ?? '_self',
            'icon' => $def['icon'] ?? 'mdi-tray-arrow-up',
            'bulkTool' => $bulkTool,
            'tooltipItems' => $tooltipItems,
        ];

        foreach ([
            'forceLabel', 'density', 'variant', 'color', 'textColor', 'allowedRoles', 'noSuperAdmin',
            'tooltip', 'tooltipLocation', 'componentProps', 'responsive', 'badge', 'badgeColor',
        ] as $optionalKey) {
            if (array_key_exists($optionalKey, $def)) {
                $action[$optionalKey] = $def[$optionalKey];
            }
        }

        if (isset($def['table_action_extras']) && is_array($def['table_action_extras'])) {
            $action = array_merge($action, $def['table_action_extras']);
        }

        $existing = is_array($this->tableActions ?? null) ? $this->tableActions : [];

        $this->tableActions = $position === 'prepend'
            ? array_values(array_merge([$action], $existing))
            : array_values(array_merge($existing, [$action]));
    }

    /**
     * `bulk_sheet` block from the current submodule route config.
     *
     * <pre>
     * 'bulk_sheet' => [
     *     'export_download_filename' => 'redirects-export.csv',
     *     'step_up_ability' => 'redirect.bulk_import',
     *     'preview_table_columns' => [
     *         ['title' => 'Line', 'key' => 'line', 'width' => '72px'],
     *         ['title' => 'OK', 'key' => 'valid', 'sortable' => false],
     *         ['title' => 'Action', 'key' => 'action'],
     *         ['title' => 'Locale', 'key' => 'locale'],
     *         ['title' => 'From', 'key' => 'from_path'],
     *         ['title' => 'To', 'key' => 'to_path'],
     *         ['title' => 'Errors', 'key' => 'errors', 'sortable' => false],
     *         ['title' => 'Warnings', 'key' => 'warnings', 'sortable' => false],
     *     ],
     * ]
     * </pre>
     *
     * @return array<string, mixed>
     */
    protected function bulkSheetRouteConfig(): array
    {
        if (! $this->module) {
            return [];
        }

        return (array) ($this->module->getRawRouteConfig($this->routeName)['bulk_sheet'] ?? []);
    }

    /**
     * Full raw route config row (e.g. url, headline, inputs, bulk_sheet).
     *
     * @return array<string, mixed>
     */
    protected function bulkSheetParentRouteConfig(): array
    {
        if (! $this->module) {
            return [];
        }

        return (array) $this->module->getRawRouteConfig($this->routeName);
    }

    /**
     * Tool key for the bulk sheet.
     */
    public function bulkSheetToolKey(): string
    {
        $override = $this->bulkSheetRouteConfig()['tool_key'] ?? null;
        if (is_string($override) && $override !== '') {
            return $override;
        }

        $mod = Str::lower((string) $this->moduleName);
        $route = Str::snake(Str::studly((string) $this->routeName));

        return $mod . '.' . $route;
    }

    /**
     * Suggested filename for Content-Disposition on export.
     */
    public function bulkSheetExportDownloadFilename(): string
    {
        $cfg = $this->bulkSheetRouteConfig()['export_download_filename'] ?? null;

        return is_string($cfg) && $cfg !== '' ? $cfg : $this->moduleName . '-' . $this->routeName . '-export.csv';
    }

    /**
     * Submodule-specific UI strings for {@see BulkSheet.vue} (server-resolved, overrides vue `messages.bulk.*`).
     * Keys: intro, columns, csv_file, … (see page props).
     *
     * @return array<string, string>
     */
    protected function bulkSheetInertiaUiStrings(): array
    {
        return [];
    }

    protected function resolveBulkSheetIntro(): string
    {
        $ui = $this->bulkSheetInertiaUiStrings();
        if (isset($ui['intro']) && is_string($ui['intro']) && $ui['intro'] !== '') {
            return $ui['intro'];
        }

        $cfg = $this->bulkSheetRouteConfig();
        $intro = $cfg['toolbar_intro'] ?? null;
        if (is_string($intro) && $intro !== '') {
            return __($intro);
        }

        return $this->bulkSheetToolbarIntroFallback();
    }

    /**
     * @return array<string, string>
     */
    protected function bulkSheetUiPropsForInertia(): array
    {
        $merged = $this->bulkSheetInertiaUiStrings();
        $merged['intro'] = $this->resolveBulkSheetIntro();

        return array_filter($merged, static fn ($v) => is_string($v) && $v !== '');
    }

    /**
     * @return array<string, mixed>
     */
    public function bulkSheetToolbarDefinition(): array
    {
        if (! $this->module) {
            return [];
        }

        $prefix = $this->module->panelRouteNamePrefix();
        $cfg = $this->bulkSheetRouteConfig();
        $names = $this->bulkSheetWebRouteNames();
        $label = $cfg['toolbar_label'] ?? null;

        return [
            'position' => $cfg['toolbar_position'] ?? 'append',
            'label' => is_string($label) && $label !== '' ? __($label) : $this->bulkSheetToolbarLabel(),
            'icon' => $cfg['toolbar_icon'] ?? 'mdi-tray-arrow-up',
            'variant' => $cfg['toolbar_variant'] ?? 'tonal',
            'color' => $cfg['toolbar_color'] ?? 'secondary',
            'href' => $prefix . Str::snake($this->routeName) . '.' . $names['tool'],
            'target' => $cfg['toolbar_target'] ?? '_self',
            'intro' => $this->resolveBulkSheetIntro(),
        ];
    }

    /**
     * @return array{tool: string, dryRun: string, commit: string, export: string}
     */
    protected function defaultBulkSheetWebRouteNames(): array
    {
        return [
            'tool' => 'bulk.tool',
            'dryRun' => 'bulk.dryRun.web',
            'commit' => 'bulk.commit.web',
            'export' => 'bulk.export.web',
        ];
    }

    /**
     * @return array{tool: string, dryRun: string, commit: string, export: string}
     */
    public function bulkSheetWebRouteNames(): array
    {
        $cfg = $this->bulkSheetRouteConfig()['web_route_names'] ?? [];
        $defaults = $this->defaultBulkSheetWebRouteNames();

        return [
            'tool' => (string) ($cfg['tool'] ?? $defaults['tool']),
            'dryRun' => (string) ($cfg['dryRun'] ?? $defaults['dryRun']),
            'commit' => (string) ($cfg['commit'] ?? $defaults['commit']),
            'export' => (string) ($cfg['export'] ?? $defaults['export']),
        ];
    }

    public function bulkSheetStepUpAbility(): ?string
    {
        $v = $this->bulkSheetRouteConfig()['step_up_ability'] ?? null;

        return is_string($v) && $v !== '' ? $v : null;
    }

    public function bulkSheetToolHeadline(): string
    {
        $ui = $this->bulkSheetInertiaUiStrings();
        if (isset($ui['headline']) && is_string($ui['headline']) && $ui['headline'] !== '') {
            return $ui['headline'];
        }

        $h = $this->bulkSheetRouteConfig()['tool_headline'] ?? null;
        if (is_string($h) && $h !== '') {
            return __($h);
        }

        return $this->bulkSheetToolHeadlineFallback();
    }

    public function bulkSheetToolBrowserTitle(): string
    {
        $ui = $this->bulkSheetInertiaUiStrings();
        if (isset($ui['browser_title']) && is_string($ui['browser_title']) && $ui['browser_title'] !== '') {
            return $ui['browser_title'];
        }

        $t = $this->bulkSheetRouteConfig()['browser_title'] ?? null;
        if (is_string($t) && $t !== '') {
            return __($t);
        }

        return $this->bulkSheetToolHeadline();
    }

    public function bulkSheetPreviewTableColumns(): ?array
    {
        $cols = $this->bulkSheetRouteConfig()['preview_table_columns'] ?? null;

        return is_array($cols) && $cols !== [] ? $cols : null;
    }

    protected function bulkSheetToolbarLabel(): string
    {
        return __('Import / export (CSV)');
    }

    private function bulkSheetToolbarIntroFallback(): string
    {
        return __('messages.bulk.intro');
    }

    private function bulkSheetToolHeadlineFallback(): string
    {
        return __('messages.bulk.headline');
    }

    /**
     * GET — Inertia shell for CSV import/export (POST targets {@see bulkSheetDryRun} / {@see bulkSheetCommit}).
     */
    public function bulkSheetTool(Request $request): Response
    {
        if (! $this instanceof CanBulkSheet || ! $this->module) {
            abort(404);
        }

        $this->shareInertiaStoreVariables();

        $prefix = $this->module->panelRouteNamePrefix() . Str::snake($this->routeName) . '.';
        $names = $this->bulkSheetWebRouteNames();
        $toolKey = $this->bulkSheetToolKey();

        $pageTitle = $this->bulkSheetToolBrowserTitle() . ' - ' . Modularous::pageTitle();
        $data = [
            'pageTitle' => $pageTitle,
            'headerTitle' => $this->bulkSheetToolHeadline(),
            '_mainConfiguration' => [
                'navigation' => $this->bulkSheetNavigationWithBreadcrumbs(),
            ],
        ];

        return Inertia::render($this->bulkSheetInertiaComponent(), [
            'toolKey' => $toolKey,
            'bulkToolSheet' => $this->bulkSheetFields(),
            'bulkSheetUi' => $this->bulkSheetUiPropsForInertia(),
            'bulkSheetEndpoints' => [
                'dryRun' => route($prefix . $names['dryRun']),
                'commit' => route($prefix . $names['commit']),
                'export' => route($prefix . $names['export']),
            ],
            'bulkSheetPreviewTableColumns' => $this->bulkSheetPreviewTableColumns(),
            'endpoints' => new \stdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    final public function bulkSheetDryRun(Request $request): JsonResponse
    {
        if (! $this instanceof CanBulkSheet || ! $this->module) {
            abort(404);
        }

        $data = $request->validate([
            'csv' => 'required|string|max:2097152',
            'tool_key' => ['nullable', 'string', 'max:128'],
        ]);

        $toolKey = (string) ($data['tool_key'] ?? $this->bulkSheetToolKey());
        $this->assertBulkSheetToolKey($toolKey);

        /** @var BulkImportService $bulk */
        $bulk = $this->app->make(BulkImportService::class);

        return response()->json($bulk->import($data['csv'], true, $this, $toolKey));
    }

    final public function bulkSheetCommit(Request $request): JsonResponse
    {
        if (! $this instanceof CanBulkSheet || ! $this->module) {
            abort(404);
        }

        $data = $request->validate([
            'csv' => 'required|string|max:2097152',
            'tool_key' => ['nullable', 'string', 'max:128'],
        ]);

        $toolKey = (string) ($data['tool_key'] ?? $this->bulkSheetToolKey());
        $this->assertBulkSheetToolKey($toolKey);

        /** @var BulkImportService $bulk */
        $bulk = $this->app->make(BulkImportService::class);

        return response()->json($bulk->import($data['csv'], false, $this, $toolKey));
    }

    final public function bulkSheetExport(Request $request): StreamedResponse
    {
        if (! $this instanceof CanBulkSheet || ! $this->module) {
            abort(404);
        }

        $request->validate([
            'tool_key' => ['nullable', 'string', 'max:128'],
        ]);

        $toolKey = (string) $request->input('tool_key', $this->bulkSheetToolKey());
        $this->assertBulkSheetToolKey($toolKey);

        /** @var BulkImportService $bulk */
        $bulk = $this->app->make(BulkImportService::class);

        return $bulk->streamExport($this);
    }

    /**
     * Inertia page component (under `Pages/`), without path or extension.
     */
    protected function bulkSheetInertiaComponent(): string
    {
        return 'BulkSheet';
    }

    /**
     * @return array<string, mixed>
     */
    protected function bulkSheetNavigationWithBreadcrumbs(): array
    {
        $navigation = get_modularous_navigation_config();
        $navigation['breadcrumbs'] = $this->bulkSheetBreadcrumbsItems();

        return $navigation;
    }

    /**
     * @return list<array{title: string, href?: string, disabled?: bool}>
     */
    protected function bulkSheetBreadcrumbsItems(): array
    {
        $parentRouteName = $this->module->panelRouteNamePrefix() . 'index';
        $routeName = $this->module->panelRouteNamePrefix() . snakeCase($this->routeName) . '.index';

        return [
            [
                'title' => headline($this->moduleName),
                'href' => Route::has($parentRouteName) ? route($parentRouteName) : null,
            ],
            [
                'title' => headline($this->routeName),
                'href' => Route::has($routeName) ? route($routeName) : null,
            ],
            [
                'title' => $this->bulkSheetToolHeadlineFallback(),
                'disabled' => true,
            ],
        ];
    }

    protected function assertBulkSheetToolKey(string $toolKey): void
    {
        if ($toolKey !== $this->bulkSheetToolKey()) {
            abort(403, 'Invalid bulk sheet tool for this route.');
        }
    }
}
