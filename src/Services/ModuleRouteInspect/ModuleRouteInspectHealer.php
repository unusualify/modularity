<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect;

use Illuminate\Support\Facades\Artisan;

/**
 * Runs allowlisted remake commands for inspect findings (opt-in heal path only).
 *
 * Never call from sidebar / CoreController / getConfigFieldsByRoute (hot-path ADR).
 */
final class ModuleRouteInspectHealer
{
    public function __construct(
        private readonly ModuleRouteInspectRemedyMapper $mapper,
        private readonly ModuleRouteInspector $inspector,
    ) {
    }

    /**
     * Heal a single finding for a module route.
     *
     * @return array{ok: bool, dry_run: bool, remedy: array<string, mixed>, output: string, exit_code: int}
     */
    public function healFinding(
        string $module,
        string $route,
        string $findingCode,
        ?string $feature = null,
        bool $dryRun = true,
        bool $allowUnsafe = false,
    ): array {
        $entry = $this->findEntry($module, $route);
        $finding = $this->findFinding($entry, $findingCode, $feature);
        $remedy = $this->mapper->map($entry->module, $entry->route, $finding);

        if ($remedy === null || $remedy->action !== ModuleRouteInspectRemedy::ACTION_REMAKE) {
            throw new \InvalidArgumentException(
                "Finding [{$findingCode}] has no remake remedy."
            );
        }

        if ($remedy->command === null
            || ! in_array($remedy->command, ModuleRouteInspectRemedyMapper::ALLOWLISTED_REMAKE_COMMANDS, true)
        ) {
            throw new \InvalidArgumentException(
                'Remake command is not allowlisted for heal.'
            );
        }

        if (! $remedy->safe && ! $allowUnsafe) {
            throw new \InvalidArgumentException(
                'This remedy requires --force / unsafe heal confirmation.'
            );
        }

        $parameters = [];
        foreach ($remedy->arguments as $index => $argument) {
            $parameters[(string) $index] = $argument;
        }
        // Named arguments for artisan
        $parameters['module'] = $remedy->arguments[0] ?? $module;
        $parameters['route'] = $remedy->arguments[1] ?? $route;

        foreach ($remedy->options as $name => $value) {
            if ($name === 'dry-run') {
                $parameters['--dry-run'] = $dryRun;

                continue;
            }
            if ($value === true) {
                $parameters['--' . $name] = true;
            } elseif ($value !== false && $value !== null) {
                $parameters['--' . $name] = $value;
            }
        }

        if ($dryRun) {
            $parameters['--dry-run'] = true;
        } else {
            unset($parameters['--dry-run']);
        }

        // Drop numeric keys if named present
        unset($parameters['0'], $parameters['1']);

        $exitCode = Artisan::call($remedy->command, $parameters);
        $output = Artisan::output();

        return [
            'ok' => $exitCode === 0,
            'dry_run' => $dryRun,
            'remedy' => $remedy->toArray(),
            'output' => $output,
            'exit_code' => $exitCode,
        ];
    }

    /**
     * Collect remake remedies for a report (CLI --suggest).
     *
     * @return list<array{module: string, route: string, code: string, remedy: array<string, mixed>}>
     */
    public function suggestFromReport(ModuleRouteInspectReport $report): array
    {
        $suggestions = [];

        foreach ($report->entries as $entry) {
            foreach ($entry->findings as $finding) {
                $remedy = $this->mapper->map($entry->module, $entry->route, $finding);
                if ($remedy === null || $remedy->action !== ModuleRouteInspectRemedy::ACTION_REMAKE) {
                    continue;
                }

                $suggestions[] = [
                    'module' => $entry->module,
                    'route' => $entry->route,
                    'code' => $finding->code,
                    'feature' => $finding->feature,
                    'remedy' => $remedy->toArray(),
                ];
            }
        }

        return $suggestions;
    }

    private function findEntry(string $module, string $route): ModuleRouteInspectEntry
    {
        $report = $this->inspector->inspect($module, $route);
        $entry = $report->entries[0] ?? null;

        if ($entry === null) {
            throw new \InvalidArgumentException("No inspect entry for {$module}::{$route}.");
        }

        return $entry;
    }

    private function findFinding(
        ModuleRouteInspectEntry $entry,
        string $code,
        ?string $feature,
    ): ModuleRouteInspectFinding {
        foreach ($entry->findings as $finding) {
            if ($finding->code !== $code) {
                continue;
            }
            if ($feature !== null && $finding->feature !== null && $finding->feature !== $feature) {
                continue;
            }

            return $finding;
        }

        throw new \InvalidArgumentException(
            "Finding [{$code}] not present on {$entry->module}::{$entry->route}."
        );
    }
}
