<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Library;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Unusualify\Modularous\Entities\File;
use Unusualify\Modularous\Entities\Media;
use Unusualify\Modularous\Services\Library\OrphanUploadCleanup;
use Unusualify\Modularous\Tests\TestCase;

class OrphanUploadCleanupTest extends TestCase
{
    use RefreshDatabase;

    private OrphanUploadCleanup $cleanup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanup = new OrphanUploadCleanup;

        Config::set('filesystems.disks.test_media_library', [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/test_media_library'),
        ]);

        Config::set('filesystems.disks.test_file_library', [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/test_file_library'),
        ]);

        Config::set(modularousBaseKey() . '.media_library.endpoint_type', 'local');
        Config::set(modularousBaseKey() . '.media_library.disk', 'test_media_library');
        Config::set(modularousBaseKey() . '.media_library.local_path', 'uploads');
        Config::set(modularousBaseKey() . '.media_library.prefix_uuid_with_local_path', false);

        Config::set(modularousBaseKey() . '.file_library.endpoint_type', 'local');
        Config::set(modularousBaseKey() . '.file_library.disk', 'test_file_library');
        Config::set(modularousBaseKey() . '.file_library.local_path', 'uploads');
        Config::set(modularousBaseKey() . '.file_library.prefix_uuid_with_local_path', false);

        Storage::fake('test_media_library');
        Storage::fake('test_file_library');
    }

    public function test_it_deletes_orphan_media_folders(): void
    {
        $knownUuid = (string) Str::uuid();
        $orphanUuid = (string) Str::uuid();

        Storage::disk('test_media_library')->put("{$knownUuid}/known.jpg", 'known');
        Storage::disk('test_media_library')->put("{$orphanUuid}/orphan.jpg", 'orphan');

        Media::create([
            'uuid' => "{$knownUuid}/known.jpg",
            'filename' => 'known.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $result = $this->cleanup->cleanup();

        $this->assertSame(1, $result['deleted']);
        $this->assertContains($orphanUuid, $result['orphans']);
        $this->assertTrue(Storage::disk('test_media_library')->exists("{$knownUuid}/known.jpg"));
        $this->assertFalse(Storage::disk('test_media_library')->exists("{$orphanUuid}/orphan.jpg"));
    }

    public function test_it_deletes_orphan_file_folders(): void
    {
        $knownUuid = (string) Str::uuid();
        $orphanUuid = (string) Str::uuid();

        Storage::disk('test_file_library')->put("{$knownUuid}/known.pdf", 'known');
        Storage::disk('test_file_library')->put("{$orphanUuid}/orphan.pdf", 'orphan');

        File::create([
            'uuid' => "{$knownUuid}/known.pdf",
            'filename' => 'known.pdf',
            'size' => 5,
        ]);

        $result = $this->cleanup->cleanup();

        $this->assertSame(1, $result['deleted']);
        $this->assertContains($orphanUuid, $result['orphans']);
        $this->assertTrue(Storage::disk('test_file_library')->exists("{$knownUuid}/known.pdf"));
        $this->assertFalse(Storage::disk('test_file_library')->exists("{$orphanUuid}/orphan.pdf"));
    }

    public function test_it_keeps_folders_referenced_by_either_library_when_roots_are_shared(): void
    {
        Config::set(modularousBaseKey() . '.file_library.disk', 'test_media_library');

        $fileOnlyUuid = (string) Str::uuid();
        $orphanUuid = (string) Str::uuid();

        Storage::disk('test_media_library')->put("{$fileOnlyUuid}/document.pdf", 'file');
        Storage::disk('test_media_library')->put("{$orphanUuid}/orphan.jpg", 'orphan');

        File::create([
            'uuid' => "{$fileOnlyUuid}/document.pdf",
            'filename' => 'document.pdf',
            'size' => 8,
        ]);

        $result = $this->cleanup->cleanup();

        $this->assertSame(1, $result['deleted']);
        $this->assertContains($orphanUuid, $result['orphans']);
        $this->assertNotContains($fileOnlyUuid, $result['orphans']);
        $this->assertTrue(Storage::disk('test_media_library')->exists("{$fileOnlyUuid}/document.pdf"));
    }

    public function test_dry_run_does_not_delete_orphan_folders(): void
    {
        $orphanUuid = (string) Str::uuid();

        Storage::disk('test_media_library')->put("{$orphanUuid}/orphan.jpg", 'orphan');

        $result = $this->cleanup->cleanup(dryRun: true);

        $this->assertSame(0, $result['deleted']);
        $this->assertSame(1, $result['scanned']);
        $this->assertTrue(Storage::disk('test_media_library')->exists("{$orphanUuid}/orphan.jpg"));
    }
}
