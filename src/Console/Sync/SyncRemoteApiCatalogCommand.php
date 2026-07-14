<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Sync;

use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;

class SyncRemoteApiCatalogCommand extends BaseCommand
{
    use InteractsWithRemoteApiSyncPreview;

    protected $signature = 'modularous:sync-remote-api-catalog
        {module : Module name}
        {route : Route/submodule name}
        {catalog? : Catalog key from connector remoteApiConfiguration catalogs}
        {--dry-run : Preview without calling the remote API or writing cache}';

    protected $description = 'Fetch and print remote API catalog rows (uses connector cache).';

    protected $aliases = [
        'mod:sync:remote-api-catalog',
    ];

    public function handle(): int
    {
        $moduleName = $this->argument('module');
        $routeName = snakeCase($this->argument('route'));
        $catalogKey = $this->argument('catalog');
        $module = Modularous::findOrFail($moduleName);
        $repository = $module->getRepository($routeName);

        if (! $this->usesRemoteApiSourceTrait($repository)) {
            $this->error('Repository does not use RemoteApiSourceTrait or remote API connector is not configured.');

            return self::FAILURE;
        }

        $resolvedCatalogKey = is_string($catalogKey) ? $catalogKey : null;

        if ((bool) $this->option('dry-run')) {
            $preview = $repository->previewRemoteCatalog($resolvedCatalogKey);
            $this->renderRemoteApiCatalogPreview($preview);

            return self::SUCCESS;
        }

        $rows = $repository->listRemoteCatalog($resolvedCatalogKey);
        $this->info(sprintf('Fetched %d catalog row(s).', count($rows)));

        foreach ($rows as $row) {
            $this->line(json_encode($row, JSON_UNESCAPED_UNICODE));
        }

        return self::SUCCESS;
    }

    private function usesRemoteApiSourceTrait(object $repository): bool
    {
        return in_array(RemoteApiSourceTrait::class, class_uses_recursive($repository), true);
    }
}
