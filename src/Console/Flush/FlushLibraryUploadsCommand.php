<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Flush;

use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Services\Library\OrphanUploadCleanup;

class FlushLibraryUploadsCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * @var string
     */
    protected $signature = 'modularous:flush:library-uploads
        {--dry-run : List orphan upload folders without deleting them}
    ';

    /**
     * @var string[]
     */
    protected $aliases = [
        'modularous:library-uploads:flush',
    ];

    /**
     * @var string
     */
    protected $description = 'Delete local media and file library upload folders that have no database record';

    public function handle(): int
    {
        $cleanup = app(OrphanUploadCleanup::class);
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run enabled. No files will be deleted.');
        }

        $this->info('Scanning local media and file library uploads for orphan UUID folders...');

        $result = $cleanup->cleanup($dryRun);
        $libraries = implode(', ', $result['libraries']);

        if ($result['scanned'] === 0) {
            $this->comment($libraries !== '' ? "No orphan UUID folders found for: {$libraries}." : 'No local media or file library disks configured.');

            return self::SUCCESS;
        }

        $action = $dryRun ? 'would delete' : 'deleted';
        $this->info("{$action} {$result['deleted']} orphan folder(s) across: {$libraries}.");

        foreach ($result['orphans'] as $folder) {
            $this->line("  - {$folder}");
        }

        return self::SUCCESS;
    }
}
