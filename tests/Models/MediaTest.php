<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Entities\Media;
use Unusualify\Modularity\Services\MediaLibrary\ImageService;
use Unusualify\Modularity\Tests\ModelTestCase;

class MediaTest extends ModelTestCase
{
    use RefreshDatabase;

    protected Media $media;

    protected function setUp(): void
    {
        parent::setUp();

        $this->media = Media::factory()->create([
            'uuid' => 'test-uuid-1',
            'filename' => 'test-image.jpg',
            'alt_text' => 'Test Alt Text',
            'caption' => 'Test Caption',
            'width' => 1920,
            'height' => 1080,
        ]);
    }

    public function test_get_table_media()
    {
        $media = new Media;
        $this->assertEquals(modularityConfig('tables.medias', 'um_medias'), $media->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'uuid',
            'filename',
            'alt_text',
            'caption',
            'width',
            'height',
            // HasCreator fillable
            'custom_creator_id',
            'custom_creator_type',
            'custom_guard_name',
        ];

        // Get base fillable attributes without extra metadata fields
        $baseFillable = array_intersect($expectedFillable, $this->media->getFillable());
        $this->assertEquals($expectedFillable, $baseFillable);
    }

    public function test_dimensions_attribute()
    {
        $this->assertEquals('1920x1080', $this->media->dimensions);
    }

    public function test_alt_text_from_filename()
    {
        $testCases = [
            'my-image.jpg' => 'My Image',
            'my_image@2x.jpg' => 'My Image',
            'my.complex-image_name.jpg' => 'My Complex Image Name',
        ];

        foreach ($testCases as $filename => $expected) {
            $this->assertEquals($expected, $this->media->altTextFrom($filename));
        }
    }

    public function test_can_delete_safely_when_no_mediables()
    {
        $this->assertTrue($this->media->canDeleteSafely());
    }

    public function test_can_delete_safely_when_has_mediables()
    {
        // Insert a mediable record
        DB::table(modularityConfig('tables.mediables'))->insert([
            'media_id' => $this->media->id,
            'mediable_id' => 1,
            'mediable_type' => 'test_type',
            'role' => 'test',
            'locale' => 'en',
            'metadatas' => json_encode([]),
            'crop' => 'default',
        ]);

        $this->assertFalse($this->media->canDeleteSafely());
    }

    public function test_is_referenced()
    {
        $this->assertFalse($this->media->isReferenced());

        // Add a reference
        DB::table(modularityConfig('tables.mediables'))->insert([
            'media_id' => $this->media->id,
            'mediable_id' => 1,
            'mediable_type' => 'test_type',
            'role' => 'test',
            'locale' => 'en',
            'metadatas' => json_encode([]),
            'crop' => 'default',
        ]);

        $this->assertTrue($this->media->isReferenced());
    }

    public function test_scope_unused()
    {
        // Create another media
        $usedMedia = Media::factory()->create();

        // Make the media used by adding a mediable record
        DB::table(modularityConfig('tables.mediables'))->insert([
            'media_id' => $usedMedia->id,
            'mediable_id' => 1,
            'mediable_type' => 'test_type',
            'role' => 'test',
            'locale' => 'en',
            'metadatas' => json_encode([]),
            'crop' => 'default',
        ]);

        $unusedMedia = Media::unused();

        $this->assertCount(1, $unusedMedia);
        $this->assertEquals($this->media->id, $unusedMedia->first()->id);
    }

    public function test_mediable_format()
    {
        $expected = [
            'id' => $this->media->id,
            'name' => 'test-image.jpg',
            'thumbnail' => ImageService::getCmsUrl('test-uuid-1', ['h' => '256']),
            'original' => ImageService::getRawUrl('test-uuid-1'),
            'medium' => ImageService::getUrl('test-uuid-1', ['h' => '430']),
            'width' => 1920,
            'height' => 1080,
            'tags' => Collection::make([]),
            'deleteUrl' => moduleRoute('media', adminRouteNamePrefix() . '.media-library', 'destroy', ['media' => $this->media->id]),
            'updateUrl' => route(Route::hasAdmin('media-library.media.single-update')),
            'updateBulkUrl' => route(Route::hasAdmin('media-library.media.bulk-update')),
            'deleteBulkUrl' => route(Route::hasAdmin('media-library.media.bulk-delete')),
            'metadatas' => [
                'default' => [
                    'caption' => 'Test Caption',
                    'altText' => 'Test Alt Text',
                    'video' => null,
                ],
                'custom' => [
                    'caption' => null,
                    'altText' => null,
                    'video' => null,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->media->mediableFormat());
    }

    public function test_replace_media()
    {
        // Create a referenced media
        DB::table(modularityConfig('tables.mediables'))->insert([
            'media_id' => $this->media->id,
            'mediable_id' => 1,
            'mediable_type' => 'test_type',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([]),
            'role' => 'test',
            'locale' => 'en',
        ]);

        $newData = [
            'width' => 1280,
            'height' => 720,
        ];

        $this->media->replace($newData);

        $mediable = DB::table(modularityConfig('tables.mediables'))
            ->where('media_id', $this->media->id)
            ->first();

        $this->assertEquals(0, $mediable->crop_x);
        $this->assertEquals(0, $mediable->crop_y);
        $this->assertEquals(1280, $mediable->crop_w);
        $this->assertEquals(720, $mediable->crop_h);
    }

    public function test_delete_media()
    {
        // Test successful deletion when no references
        $this->assertTrue($this->media->delete());
        $this->assertSoftDeleted(modularityConfig('tables.medias'), ['id' => $this->media->id]);

        // Test failed deletion when referenced
        $referencedMedia = Media::factory()->create();
        DB::table(modularityConfig('tables.mediables'))->insert([
            'media_id' => $referencedMedia->id,
            'mediable_id' => 1,
            'mediable_type' => 'test_type',
            'role' => 'test',
            'locale' => 'en',
            'metadatas' => json_encode([]),
        ]);

        $this->assertFalse($referencedMedia->delete());
        $this->assertDatabaseHas(modularityConfig('tables.medias'), ['id' => $referencedMedia->id]);
    }

    public function test_force_delete_media()
    {
        // Test successful deletion when no references
        $this->assertTrue($this->media->forceDelete());
        $this->assertDatabaseMissing(modularityConfig('tables.medias'), ['id' => $this->media->id]);
    }

    public function test_has_timestamps()
    {
        $this->assertTrue($this->media->timestamps);
        $this->assertNotNull($this->media->created_at);
        $this->assertNotNull($this->media->updated_at);
    }

    public function test_has_creator_trait()
    {
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Traits\HasCreator::class,
            class_uses_recursive($this->media)
        ));
    }
}
