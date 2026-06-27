<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Sync;

trait InteractsWithRemoteApiSyncPreview
{
    /**
     * @param array<string, mixed> $preview
     */
    protected function renderRemoteApiConfigurationSummary(array $preview): void
    {
        /** @var array<string, mixed> $configuration */
        $configuration = $preview['configuration'] ?? [];

        $this->components->twoColumnDetail('Module', (string) ($configuration['module'] ?? ''));
        $this->components->twoColumnDetail('Route', (string) ($configuration['route'] ?? ''));
        $this->components->twoColumnDetail('Base URL', (string) ($configuration['base_url'] ?? ''));
        $this->components->twoColumnDetail('Endpoint', (string) ($configuration['endpoint'] ?? ''));
        $this->components->twoColumnDetail('Remote ID column', (string) ($configuration['remote_id_column'] ?? ''));
        $this->components->twoColumnDetail('Token configured', ($configuration['token_configured'] ?? false) ? 'yes' : 'no');
        $this->components->twoColumnDetail('Cache enabled', ($configuration['cache_enabled'] ?? false) ? 'yes' : 'no');

        $includes = $configuration['includes'] ?? [];

        if ($includes !== []) {
            $this->components->twoColumnDetail('Includes', implode(', ', $includes));
        }
    }

    /**
     * @param array<string, mixed> $preview
     */
    protected function renderRemoteApiSyncAllPreview(array $preview): void
    {
        $this->components->warn('[dry-run] Remote API sync preview — no HTTP requests and no database writes.');

        $this->newLine();
        $this->components->info('Connector');
        $this->renderRemoteApiConfigurationSummary($preview);
        $this->components->twoColumnDetail('Model', (string) ($preview['model'] ?? ''));
        $this->components->twoColumnDetail('Would fetch', (string) ($preview['would_fetch'] ?? ''));

        $records = $preview['records'] ?? [];

        if ($records !== []) {
            $this->newLine();
            $this->components->info('Local records that would be updated');

            $this->table(
                ['Action', 'Local ID', 'Remote ID', 'Label'],
                array_map(
                    static fn (array $record): array => [
                        '[dry-run] update',
                        (string) ($record['local_id'] ?? ''),
                        (string) ($record['remote_id'] ?? ''),
                        (string) ($record['label'] ?? ''),
                    ],
                    $records
                )
            );
        }

        $withoutRemoteId = $preview['without_remote_id'] ?? [];

        if ($withoutRemoteId !== []) {
            $this->newLine();
            $this->components->info('Local records without remote ID (skipped until linked)');

            $this->table(
                ['Local ID', 'Label'],
                array_map(
                    static fn (array $record): array => [
                        (string) ($record['local_id'] ?? ''),
                        (string) ($record['label'] ?? ''),
                    ],
                    $withoutRemoteId
                )
            );
        }

        /** @var array<string, int> $summary */
        $summary = $preview['summary'] ?? [];

        $this->newLine();
        $this->line(sprintf(
            '[dry-run] Would sync remote list: update %d linked local record(s), skip %d without remote ID.',
            $summary['would_update'] ?? 0,
            $summary['without_remote_id'] ?? 0,
        ));
        $this->line('[dry-run] New remote records would be created after list fetch (count unknown without HTTP).');
        $this->comment('Run without --dry-run to execute sync.');
    }

    /**
     * @param array<string, mixed> $preview
     */
    protected function renderRemoteApiSyncRecordPreview(array $preview): void
    {
        $this->components->warn('[dry-run] Remote API sync preview — no HTTP requests and no database writes.');

        $this->newLine();
        $this->components->info('Connector');
        $this->renderRemoteApiConfigurationSummary($preview);
        $this->components->twoColumnDetail('Would fetch', (string) ($preview['would_fetch'] ?? ''));

        $this->newLine();
        $this->table(
            ['Action', 'Remote ID', 'Local ID', 'Label'],
            [[
                sprintf('[dry-run] %s', (string) ($preview['action'] ?? '')),
                (string) ($preview['remote_id'] ?? ''),
                (string) ($preview['local_id'] ?? 'n/a'),
                (string) ($preview['label'] ?? 'n/a'),
            ]]
        );

        $this->comment('Run without --dry-run to execute sync.');
    }

    /**
     * @param array<string, mixed> $preview
     */
    protected function renderRemoteApiCatalogPreview(array $preview): void
    {
        $this->components->warn('[dry-run] Remote API catalog preview — no HTTP requests and no cache writes.');

        $this->newLine();
        $this->components->info('Connector');
        $this->renderRemoteApiConfigurationSummary($preview);
        $this->components->twoColumnDetail('Catalog key', (string) ($preview['catalog_key'] ?? '(default list)'));
        $this->components->twoColumnDetail('Would fetch', (string) ($preview['would_fetch'] ?? ''));

        $catalogs = $preview['available_catalogs'] ?? [];

        if ($catalogs !== []) {
            $this->newLine();
            $this->components->info('Available catalog keys');
            $this->line('  · ' . implode("\n  · ", $catalogs));
        }

        $this->newLine();
        $this->comment('Run without --dry-run to fetch catalog rows.');
    }
}
