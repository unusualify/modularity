<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\FileLibrary;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as FilesystemManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use Mockery;
use Unusualify\Modularous\Services\FileLibrary\Disk;
use Unusualify\Modularous\Tests\TestCase;

class DiskTest extends TestCase
{
    public function test_get_url_returns_disk_url_for_configured_library_disk(): void
    {
        config()->set('modularous.file_library.disk', 'public');

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('url')
            ->once()
            ->with('files/report.pdf')
            ->andReturn('https://example.test/storage/files/report.pdf');

        $manager = Mockery::mock(FilesystemManager::class);
        $manager->shouldReceive('disk')
            ->once()
            ->with('public')
            ->andReturn($filesystem);

        $service = new Disk($manager, new Config(config()->all()));

        $this->assertSame(
            'https://example.test/storage/files/report.pdf',
            $service->getUrl('files/report.pdf')
        );
    }
}
