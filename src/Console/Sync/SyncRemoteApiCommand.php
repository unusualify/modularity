<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Sync;

use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;

class SyncRemoteApiCommand extends BaseCommand
{
    use InteractsWithRemoteApiSyncPreview;

    protected $signature = 'modularous:sync-remote-api
        {module : Module name}
        {route : Route/submodule name}
        {--id= : Sync a single remote id}
        {--local-id= : Sync using a local record id (reads remote_id)}
        {--dry-run : Preview without writing to the database or calling the remote API}';

    protected $description = 'Sync records from a configured Remote API connector into the CMS cache.';

    protected $aliases = [
        'mod:sync:remote-api',
    ];

    public function handle(): int
    {
        $moduleName = $this->argument('module');
        $routeName = snakeCase($this->argument('route'));
        $module = Modularous::findOrFail($moduleName);
        $repository = $module->getRepository($routeName);

        if (! $this->usesRemoteApiSourceTrait($repository)) {
            $this->error('Repository does not use RemoteApiSourceTrait or remote API connector is not configured.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $remoteId = $this->option('id');
        $localId = $this->option('local-id') !== null ? (int) $this->option('local-id') : null;

        if ($remoteId !== null || $localId !== null) {
            if ($dryRun) {
                $preview = $repository->previewSyncFromRemote($localId, $remoteId);
                $this->renderRemoteApiSyncRecordPreview($preview);

                return self::SUCCESS;
            }

            $result = $repository->syncFromRemote($localId, $remoteId);

            $this->info($result['created']
                ? 'Created local record from remote API.'
                : 'Updated local record from remote API.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $preview = $repository->previewSyncAllFromRemote();
            $this->renderRemoteApiSyncAllPreview($preview);

            return self::SUCCESS;
        }

        $result = $repository->syncAllFromRemote();
        $this->info(sprintf(
            'Synced %d records (%d created, %d updated).',
            $result['total'],
            $result['created'],
            $result['updated']
        ));

        return self::SUCCESS;
    }

    private function usesRemoteApiSourceTrait(object $repository): bool
    {
        return in_array(RemoteApiSourceTrait::class, class_uses_recursive($repository), true);
    }
}
