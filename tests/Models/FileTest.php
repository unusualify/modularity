<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Entities\File;
use Unusualify\Modularity\Services\FileLibrary\FileService;
use Unusualify\Modularity\Tests\ModelTestCase;

class FileTest extends ModelTestCase
{
    use RefreshDatabase;

    protected File $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = File::factory()->create([
            'uuid' => 'test-uuid-1',
            'filename' => 'test-file.jpg',
            'size' => 1048576, // 1MB
        ]);
    }

    public function test_get_table_file()
    {
        $file = new File;
        $this->assertEquals(modularityConfig('tables.files', 'um_files'), $file->getTable());
    }

    public function test_fillable_attributes()
    {
        $fillable = ['uuid', 'filename', 'size', 'custom_creator_id', 'custom_creator_type', 'custom_guard_name'];

        $this->assertEquals($fillable, $this->file->getFillable());
    }

    public function test_size_attribute_conversion()
    {
        $this->assertEquals(1048576, $this->file->size);
        $this->assertEquals(1, $this->file->size_in_mb);
        $this->assertEquals('1024 Kb', $this->file->size_for_human);
    }

    public function test_can_delete_safely_when_no_fileables()
    {
        $this->assertTrue($this->file->canDeleteSafely());
    }

    public function test_can_delete_safely_when_has_fileables()
    {
        // Insert a fileable record
        DB::table(modularityConfig('tables.fileables'))->insert([
            'file_id' => $this->file->id,
            'fileable_id' => 1,
            'fileable_type' => 'test_type',
            'role' => 'test',
            'locale' => 'en',
        ]);

        $this->assertFalse($this->file->canDeleteSafely());
    }

    public function test_scope_unused()
    {
        // Create another file
        $usedFile = File::factory()->create();

        // Make the file used by adding a fileable record
        DB::table(modularityConfig('tables.fileables'))->insert([
            'file_id' => $usedFile->id,
            'fileable_id' => 1,
            'fileable_type' => 'test_type',
            'role' => 'test',
            'locale' => 'en',
        ]);

        $unusedFiles = File::unused();

        $this->assertCount(1, $unusedFiles);
        $this->assertEquals($this->file->id, $unusedFiles->first()->id);
    }

    public function test_mediable_format()
    {
        $expected = [
            'id' => $this->file->id,
            'name' => 'test-file.jpg',
            'src' => FileService::getUrl('test-uuid-1'),
            'original' => FileService::getUrl('test-uuid-1'),
            'size' => '1024 Kb',
            'filesizeInMb' => '1.00',
        ];

        $this->assertEquals($expected, $this->file->mediableFormat());
    }

    public function test_has_timestamps()
    {
        $this->assertTrue($this->file->timestamps);
        $this->assertNotNull($this->file->created_at);
        $this->assertNotNull($this->file->updated_at);
    }

    public function test_has_creator_trait()
    {
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Traits\HasCreator::class,
            class_uses_recursive($this->file)
        ));
    }
}
