<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Blueprint\Concerns;

use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Console\Blueprint\BlueprintClassWriter;
use Unusualify\Modularous\Console\Blueprint\BlueprintConfigSourceExtractor;
use Unusualify\Modularous\Console\Blueprint\BlueprintFieldCatalog;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\ModuleRoutePresentation\ModuleRoutePresentationResolver;

/**
 * Shared dry-run / plan output for Blueprint scaffold commands.
 *
 * @mixin BaseCommand
 */
trait ReportsBlueprintPlan
{
    /**
     * @param array<string, mixed>|list<mixed>|string $items
     * @return array{
     *     action: string,
     *     field: string,
     *     nested: string,
     *     legacy: string,
     *     path: string,
     *     fqcn: string,
     *     seed: string
     * }
     */
    protected function blueprintPlanRow(
        BlueprintClassWriter $writer,
        Module $module,
        string $routeStudly,
        string $fieldKey,
        array|string $items,
        bool $force,
        bool $fromConfig,
    ): array {
        $def = BlueprintFieldCatalog::get($fieldKey);
        $path = $writer->path($module, $routeStudly, $fieldKey);
        $exists = $this->filesystem->exists($path);

        $action = match (true) {
            $exists && ! $force => 'skip',
            $exists && $force => 'overwrite',
            default => 'create',
        };

        return [
            'action' => $action,
            'field' => $fieldKey,
            'nested' => $def['nested_key'],
            'legacy' => $def['legacy_config_key'],
            'path' => $path,
            'fqcn' => $writer->fqcn($module, $routeStudly, $fieldKey),
            'seed' => $this->blueprintSeedSummary($items, $fromConfig),
        ];
    }

    /**
     * @param array<string, mixed>|list<mixed>|string $items
     */
    protected function blueprintSeedSummary(array|string $items, bool $fromConfig): string
    {
        if (! $fromConfig) {
            return 'empty stub';
        }

        if (is_string($items)) {
            $bytes = mb_strlen($items);

            return $items === '[]' || $items === ''
                ? 'from-config: empty source'
                : "from-config: source ({$bytes} bytes)";
        }

        if ($items === []) {
            return 'from-config: empty';
        }

        $count = count($items);

        return array_is_list($items)
            ? "from-config: {$count} items"
            : "from-config: {$count} keys";
    }

    /**
     * Prefer raw config.php source (keeps __() / Component::); fall back to evaluated array.
     *
     * @param array<string, mixed> $routeConfig
     * @return array<string, mixed>|list<mixed>|string
     */
    protected function resolveBlueprintSeed(
        Module $module,
        string $routeStudly,
        string $fieldKey,
        array $routeConfig,
        bool $fromConfig,
    ): array|string {
        if (! $fromConfig) {
            return [];
        }

        $def = BlueprintFieldCatalog::get($fieldKey);
        $configPath = $module->getConfigPath();
        $source = (new BlueprintConfigSourceExtractor)->extract(
            $configPath,
            snakeCase($routeStudly),
            $def['legacy_config_key'],
            $def['nested_key'],
        );

        if (is_string($source) && $source !== '') {
            return $source;
        }

        return ModuleRoutePresentationResolver::readConfigPayloadFromArray(
            $routeConfig,
            $def['legacy_config_key']
        );
    }

    /**
     * @param  list<array{
     *     action: string,
     *     field: string,
     *     nested: string,
     *     legacy: string,
     *     path: string,
     *     fqcn: string,
     *     seed: string
     * }>  $rows
     */
    protected function printBlueprintPlan(array $rows, bool $dryRun): void
    {
        if ($dryRun) {
            $this->warn('[dry-run] No files will be written.');
        }

        $this->table(
            ['Action', 'Field', 'Nested', 'Legacy', 'Seed', 'Class'],
            array_map(
                static fn (array $row): array => [
                    $row['action'],
                    $row['field'],
                    $row['nested'],
                    $row['legacy'],
                    $row['seed'],
                    class_basename($row['fqcn']),
                ],
                $rows
            )
        );

        foreach ($rows as $row) {
            $prefix = $dryRun ? '[dry-run] ' : '';
            $this->line(sprintf(
                '%s%s → %s',
                $prefix,
                $row['action'],
                $row['path']
            ));
            $this->line('         ' . $row['fqcn']);
        }
    }

    /**
     * Paste-ready nested index/form class wiring (short arrays + ::class).
     *
     * @param  list<array{
     *     action?: string,
     *     nested: string,
     *     fqcn: string
     * }>  $rows
     */
    protected function formatBlueprintNestedConfigSnippet(array $rows): string
    {
        $index = [];
        $form = [];
        $root = [];

        foreach ($rows as $row) {
            $nested = $row['nested'];
            $classExpr = '\\' . ltrim($row['fqcn'], '\\') . '::class';

            if (! str_contains($nested, '.')) {
                $root[$nested] = $classExpr;

                continue;
            }

            $leaf = mb_substr($nested, mb_strrpos($nested, '.') + 1);

            if (str_starts_with($nested, 'index.')) {
                $index[$leaf] = $classExpr;
            } elseif (str_starts_with($nested, 'form.')) {
                $form[$leaf] = $classExpr;
            }
        }

        if ($index === [] && $form === [] && $root === []) {
            return '// (nothing to wire)';
        }

        $lines = [];

        if ($index !== []) {
            $lines[] = "'index' => [";
            foreach ($index as $leaf => $classExpr) {
                $lines[] = "    '{$leaf}' => {$classExpr},";
            }
            $lines[] = '],';
        }

        if ($form !== []) {
            $lines[] = "'form' => [";
            foreach ($form as $leaf => $classExpr) {
                $lines[] = "    '{$leaf}' => {$classExpr},";
            }
            $lines[] = '],';
        }

        foreach ($root as $key => $classExpr) {
            $lines[] = "'{$key}' => {$classExpr},";
        }

        $lines[] = '// Legacy flat keys are commented out by default (--keep-flats to retain).';

        return implode("\n", $lines);
    }

    /**
     * @param  list<array{
     *     action: string,
     *     field: string,
     *     nested: string,
     *     legacy: string,
     *     path: string,
     *     fqcn: string,
     *     seed: string
     * }>  $rows
     */
    protected function printBlueprintWriteConfigPlan(array $rows, string $routeSnake, bool $dryRun = true): void
    {
        $this->newLine();
        $prefix = $dryRun ? '[dry-run] ' : '';
        $this->comment("{$prefix}--write-config persist under routes.{$routeSnake}:");
        $this->line($this->formatBlueprintNestedConfigSnippet($rows));
    }
}
