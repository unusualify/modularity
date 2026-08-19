<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect;

/**
 * Maps inspect findings to remake / manual remedies (heal contract).
 *
 * Only CMR + revisions remakes are automated; other companions stay manual tips.
 */
final class ModuleRouteInspectRemedyMapper
{
    /** @var list<string> */
    public const ALLOWLISTED_REMAKE_COMMANDS = [
        'modularous:remake:cmr',
        'modularous:remake:revisions',
    ];

    /**
     * Enrich an entry array with `remedy` on each finding.
     *
     * @return array<string, mixed>
     */
    public function attachToEntry(ModuleRouteInspectEntry $entry): array
    {
        $data = $entry->toArray();
        $data['findings'] = [];

        foreach ($entry->findings as $finding) {
            $row = $finding->toArray();
            $remedy = $this->map($entry->module, $entry->route, $finding);
            if ($remedy !== null) {
                $row['remedy'] = $remedy->toArray();
            }
            $data['findings'][] = $row;
        }

        return $data;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function attachToReport(ModuleRouteInspectReport $report): array
    {
        return array_map(
            fn (ModuleRouteInspectEntry $entry): array => $this->attachToEntry($entry),
            $report->entries
        );
    }

    public function map(string $module, string $route, ModuleRouteInspectFinding $finding): ?ModuleRouteInspectRemedy
    {
        $feature = $finding->feature
            ?? $this->featureFromMessage($finding->message);

        return match ($finding->code) {
            'cmr_missing_front_controller' => $this->remakeCmr($module, $route, safe: true),
            'cmr_front_not_cms_controller' => $this->remakeCmr($module, $route, safe: false, force: true, tip: 'Needs --force to convert an existing Front controller.'),
            'cmr_cms_controller_unavailable' => new ModuleRouteInspectRemedy(
                action: ModuleRouteInspectRemedy::ACTION_MANUAL,
                tip: 'Install/enable the Cms module so Modules\\Cms\\Http\\Controllers\\Front\\CmsController is available.',
            ),
            'missing_repository_companion', 'missing_model_companion' => $this->companionRemedy($module, $route, $feature),
            'missing_status' => new ModuleRouteInspectRemedy(
                action: ModuleRouteInspectRemedy::ACTION_MANUAL,
                tip: sprintf('Enable the route: php artisan modularous:route:enable %s %s', $module, $route),
            ),
            'orphan_status' => new ModuleRouteInspectRemedy(
                action: ModuleRouteInspectRemedy::ACTION_MANUAL,
                tip: 'Remove the orphan status key or add matching route config.',
            ),
            'missing_model', 'missing_repository' => new ModuleRouteInspectRemedy(
                action: ModuleRouteInspectRemedy::ACTION_MANUAL,
                tip: 'Scaffold with modularous:make:route or restore the missing class.',
            ),
            default => null,
        };
    }

    private function companionRemedy(string $module, string $route, ?string $feature): ModuleRouteInspectRemedy
    {
        return match ($feature) {
            'cmr' => $this->remakeCmr($module, $route, safe: true),
            'revisions' => $this->remakeRevisions($module, $route, safe: true),
            default => new ModuleRouteInspectRemedy(
                action: ModuleRouteInspectRemedy::ACTION_MANUAL,
                tip: $feature
                    ? sprintf('No remake command for feature [%s]; align model/repository traits manually.', $feature)
                    : 'Align model/repository companion traits manually.',
            ),
        };
    }

    private function remakeCmr(string $module, string $route, bool $safe, bool $force = false, ?string $tip = null): ModuleRouteInspectRemedy
    {
        $options = ['dry-run' => true];
        if ($force) {
            $options['force'] = true;
        }

        return new ModuleRouteInspectRemedy(
            action: ModuleRouteInspectRemedy::ACTION_REMAKE,
            command: 'modularous:remake:cmr',
            arguments: [$module, $route],
            options: $options,
            safe: $safe && ! $force,
            tip: $tip,
        );
    }

    private function remakeRevisions(string $module, string $route, bool $safe): ModuleRouteInspectRemedy
    {
        return new ModuleRouteInspectRemedy(
            action: ModuleRouteInspectRemedy::ACTION_REMAKE,
            command: 'modularous:remake:revisions',
            arguments: [$module, $route],
            options: ['dry-run' => true],
            safe: $safe,
            tip: null,
        );
    }

    private function featureFromMessage(string $message): ?string
    {
        if (preg_match('/\(([a-z0-9_]+)\)\.\s*$/', $message, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
