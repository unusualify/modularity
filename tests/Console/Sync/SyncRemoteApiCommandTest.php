<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console\Sync;

use Illuminate\Support\Facades\Artisan;
use Unusualify\Modularous\Tests\TestCase;

class SyncRemoteApiCommandTest extends TestCase
{
    public function test_sync_remote_api_command_exposes_dry_run_option(): void
    {
        $definition = Artisan::all()['modularous:sync-remote-api']->getDefinition();

        $this->assertTrue($definition->hasOption('dry-run'));
    }

    public function test_sync_remote_api_catalog_command_exposes_dry_run_option(): void
    {
        $definition = Artisan::all()['modularous:sync-remote-api-catalog']->getDefinition();

        $this->assertTrue($definition->hasOption('dry-run'));
    }
}
