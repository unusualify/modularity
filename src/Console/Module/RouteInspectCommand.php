<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectEntry;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectHealer;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectRemedyMapper;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspector;

class RouteInspectCommand extends Command
{
    protected $signature = 'modularous:route:inspect
        {module? : Module name (omit to inspect all enabled modules)}
        {route? : Route name (requires module)}
        {--json : Machine-readable JSON output for Web UI}
        {--findings-only : Only routes that have findings}
        {--feature= : Comma-separated feature keys to filter (e.g. revisions,cmr)}
        {--suggest : Print suggested remake artisan lines for healable findings}
        {--heal : Run safe allowlisted remakes for mapped findings (dry-run by default)}
        {--no-dry-run : With --heal, apply remakes (writes files)}
        {--force-heal : Allow unsafe remedies that require remake --force}';

    protected $description = 'Inspect module routes: enable/disable status, feature traits, and consistency findings.';

    protected $aliases = [
        'modularous:route:doctor',
    ];

    public function handle(
        ModuleRouteInspector $inspector,
        ModuleRouteInspectRemedyMapper $remedyMapper,
        ModuleRouteInspectHealer $healer,
    ): int {
        $module = $this->argument('module');
        $route = $this->argument('route');

        if ($route !== null && ($module === null || $module === '')) {
            $this->error('The route argument requires a module argument.');

            return self::FAILURE;
        }

        $featureFilter = $this->parseFeatureFilter();

        try {
            $report = $inspector->inspect(
                is_string($module) && $module !== '' ? $module : null,
                is_string($route) && $route !== '' ? $route : null,
            )->filter(
                findingsOnly: (bool) $this->option('findings-only'),
                featureKeys: $featureFilter,
            );
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $entriesPayload = $remedyMapper->attachToReport($report);

        if ($this->option('json')) {
            $this->line(json_encode($entriesPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($report->isEmpty()) {
            $this->warn('No module routes matched the inspect criteria.');

            return self::SUCCESS;
        }

        $highlightKeys = (array) config('modularous.module_route_inspect.table_feature_keys', [
            'translation',
            'singular',
            'cmr',
            'revisions',
            'publishable',
            'slug',
        ]);

        $headers = array_merge(
            ['Module', 'Route', 'Status', 'Parent'],
            array_map(static fn (string $key): string => Str::headline($key), $highlightKeys),
            ['Other', 'Findings']
        );

        $rows = [];
        foreach ($report->entries as $entry) {
            $rows[] = $this->entryToRow($entry, $highlightKeys);
        }

        $this->table($headers, $rows);

        $findingCount = $report->findingCount();
        if ($findingCount > 0) {
            $this->newLine();
            $this->warn("Findings: {$findingCount}");
            foreach ($report->entries as $entry) {
                foreach ($entry->findings as $finding) {
                    $this->line(sprintf(
                        '  [%s] %s::%s — %s (%s)',
                        strtoupper($finding->severity),
                        $entry->module,
                        $entry->route,
                        $finding->message,
                        $finding->code
                    ));
                }
            }
        } else {
            $this->info('No findings.');
        }

        $suggestions = $healer->suggestFromReport($report);

        if ($this->option('suggest') || $this->option('heal')) {
            $this->newLine();
            if ($suggestions === []) {
                $this->info('No remake suggestions.');
            } else {
                $this->info('Suggested remakes:');
                foreach ($suggestions as $suggestion) {
                    $line = $suggestion['remedy']['artisan'] ?? '';
                    $this->line(sprintf(
                        '  %s::%s [%s] %s',
                        $suggestion['module'],
                        $suggestion['route'],
                        $suggestion['code'],
                        $line
                    ));
                }
            }
        }

        if ($this->option('heal')) {
            return $this->runHeal($healer, $suggestions);
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<array{module: string, route: string, code: string, feature?: ?string, remedy: array<string, mixed>}>  $suggestions
     */
    private function runHeal(ModuleRouteInspectHealer $healer, array $suggestions): int
    {
        $dryRun = ! (bool) $this->option('no-dry-run');
        $allowUnsafe = (bool) $this->option('force-heal');
        $failed = false;

        $this->newLine();
        $this->info($dryRun ? 'Healing (dry-run)…' : 'Healing (writing files)…');

        foreach ($suggestions as $suggestion) {
            $safe = (bool) ($suggestion['remedy']['safe'] ?? false);
            if (! $safe && ! $allowUnsafe) {
                $this->warn(sprintf(
                    '  Skip unsafe %s::%s [%s] (pass --force-heal to allow).',
                    $suggestion['module'],
                    $suggestion['route'],
                    $suggestion['code']
                ));

                continue;
            }

            try {
                $result = $healer->healFinding(
                    $suggestion['module'],
                    $suggestion['route'],
                    $suggestion['code'],
                    $suggestion['feature'] ?? null,
                    dryRun: $dryRun,
                    allowUnsafe: $allowUnsafe,
                );
                $this->line(sprintf(
                    '  [%s] %s::%s — exit %d',
                    $result['ok'] ? 'ok' : 'fail',
                    $suggestion['module'],
                    $suggestion['route'],
                    $result['exit_code']
                ));
                if (trim($result['output']) !== '') {
                    foreach (explode("\n", trim($result['output'])) as $outLine) {
                        $this->line('    ' . $outLine);
                    }
                }
                if (! $result['ok']) {
                    $failed = true;
                }
            } catch (\Throwable $e) {
                $failed = true;
                $this->error(sprintf(
                    '  %s::%s [%s] — %s',
                    $suggestion['module'],
                    $suggestion['route'],
                    $suggestion['code'],
                    $e->getMessage()
                ));
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  list<string>  $highlightKeys
     * @return list<string>
     */
    private function entryToRow(ModuleRouteInspectEntry $entry, array $highlightKeys): array
    {
        $present = $entry->presentFeatureKeys();
        $highlighted = array_fill_keys($highlightKeys, true);
        $other = array_values(array_filter(
            $present,
            static fn (string $key): bool => ! isset($highlighted[$key])
        ));

        $row = [
            $entry->module,
            $entry->route,
            $entry->enabled ? 'enabled' : 'disabled',
            $entry->parent ? 'yes' : '',
        ];

        foreach ($highlightKeys as $key) {
            $state = $entry->features[$key] ?? null;
            $row[] = ($state['present'] ?? false) ? 'yes' : '';
        }

        $row[] = $other === [] ? '' : implode(',', $other);
        $row[] = $entry->hasFindings()
            ? (string) count($entry->findings)
            : '';

        return $row;
    }

    /**
     * @return list<string>
     */
    private function parseFeatureFilter(): array
    {
        $raw = $this->option('feature');
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $part): string => strtolower(trim($part)),
            explode(',', $raw)
        )));
    }
}
