<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Media;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularity\Entities\Traits\HasImages;
use Unusualify\Modularity\Services\MediaLibrary\ImageService;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasImagesTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;
    protected $media1;
    protected $media2;
    protected $media3;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test model table
        Schema::create('test_mediable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create test model instance
        $this->model = new TestMediableModel(['name' => 'Test Model']);
        $this->model->save();

        // Create test media
        $this->media1 = Media::factory()->create([
            'uuid' => 'test-media-uuid-1',
            'filename' => 'image1.jpg',
            'alt_text' => 'Alt text 1',
            'caption' => 'Caption 1',
            'width' => 1920,
            'height' => 1080,
        ]);

        $this->media2 = Media::factory()->create([
            'uuid' => 'test-media-uuid-2',
            'filename' => 'image2.png',
            'alt_text' => 'Alt text 2',
            'caption' => 'Caption 2',
            'width' => 1280,
            'height' => 720,
        ]);

        $this->media3 = Media::factory()->create([
            'uuid' => 'test-media-uuid-3',
            'filename' => 'image3.gif',
            'alt_text' => 'Alt text 3',
            'caption' => 'Caption 3',
            'width' => 800,
            'height' => 600,
        ]);
    }

    public function test_trait_initialization()
    {
        // Test that the trait is properly used
        $this->assertTrue(in_array(
            HasImages::class,
            class_uses_recursive($this->model)
        ));
    }

    public function test_medias_relationship()
    {
        // Test the morphToMany relationship
        $relationship = $this->model->medias();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $relationship);
        $this->assertEquals(Media::class, get_class($relationship->getRelated()));
        $this->assertEquals(modularityConfig('tables.mediables', 'um_mediables'), $relationship->getTable());
    }

    public function test_has_image()
    {
        // Initially no image
        $this->assertFalse($this->model->hasImage('avatar'));

        // Attach image
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'avatar',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([]),
        ]);

        $this->model->refresh();

        // Now should have image
        $this->assertTrue($this->model->hasImage('avatar'));
        $this->assertTrue($this->model->hasImage('avatar', 'default'));
        $this->assertFalse($this->model->hasImage('avatar', 'thumbnail'));
        $this->assertFalse($this->model->hasImage('banner'));
    }

    public function test_image_url_retrieval()
    {
        // Attach image
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'hero',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 10,
            'crop_y' => 20,
            'crop_w' => 1900,
            'crop_h' => 1060,
            'metadatas' => json_encode([]),
        ]);

        // Test image URL retrieval
        $imageUrl = $this->model->image('hero');
        $expectedUrl = ImageService::getUrlWithCrop($this->media1->uuid, [
            'crop_x' => 10,
            'crop_y' => 20,
            'crop_w' => 1900,
            'crop_h' => 1060,
        ], []);

        $this->assertEquals($expectedUrl, $imageUrl);
    }

    public function test_image_url_with_parameters()
    {
        // Attach image
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'thumbnail',
            'crop' => 'square',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 500,
            'crop_h' => 500,
            'metadatas' => json_encode([]),
        ]);

        // Test with parameters
        $params = ['w' => 200, 'h' => 200];
        $imageUrl = $this->model->image('thumbnail', 'square', $params);

        $expectedUrl = ImageService::getUrlWithCrop($this->media1->uuid, [
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 500,
            'crop_h' => 500,
        ], $params);

        $this->assertEquals($expectedUrl, $imageUrl);
    }

    public function test_image_cms_mode()
    {
        // Attach image
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'admin_preview',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([]),
        ]);

        // Test CMS mode
        $cmsUrl = $this->model->image('admin_preview', 'default', [], false, true);
        $expectedUrl = ImageService::getCmsUrl($this->media1->uuid, [
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
        ]);

        $this->assertEquals($expectedUrl, $cmsUrl);
    }

    public function test_image_with_fallback()
    {
        // Test with fallback enabled (should return null when no image)
        $imageUrl = $this->model->image('nonexistent', 'default', [], true);
        $this->assertNull($imageUrl);

        // Test without fallback (should return transparent fallback)
        $imageUrl = $this->model->image('nonexistent', 'default', [], false);
        $this->assertEquals(ImageService::getTransparentFallbackUrl(), $imageUrl);
    }

    public function test_images_list_retrieval()
    {
        // Attach multiple images with same role and crop
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'gallery',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([]),
        ]);

        $this->model->medias()->attach($this->media2->id, [
            'role' => 'gallery',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1280,
            'crop_h' => 720,
            'metadatas' => json_encode([]),
        ]);

        // Test images list
        $imagesList = $this->model->images('gallery');
        $this->assertCount(2, $imagesList);

        $expectedUrl1 = ImageService::getUrlWithCrop($this->media1->uuid, [
            'crop_x' => 0, 'crop_y' => 0, 'crop_w' => 1920, 'crop_h' => 1080
        ], []);
        $expectedUrl2 = ImageService::getUrlWithCrop($this->media2->uuid, [
            'crop_x' => 0, 'crop_y' => 0, 'crop_w' => 1280, 'crop_h' => 720
        ], []);

        $this->assertContains($expectedUrl1, $imagesList);
        $this->assertContains($expectedUrl2, $imagesList);
    }

    public function test_images_with_crops()
    {
        // Attach images with different crops
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'banner',
            'crop' => 'desktop',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 600,
            'metadatas' => json_encode([]),
        ]);

        $this->model->medias()->attach($this->media1->id, [
            'role' => 'banner',
            'crop' => 'mobile',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 800,
            'crop_h' => 400,
            'metadatas' => json_encode([]),
        ]);

        // Test images with crops
        $params = ['desktop' => ['w' => 1200], 'mobile' => ['w' => 600]];
        $imagesWithCrops = $this->model->imagesWithCrops('banner', $params);

        $this->assertArrayHasKey($this->media1->id, $imagesWithCrops);
        $this->assertArrayHasKey('desktop', $imagesWithCrops[$this->media1->id]);
        $this->assertArrayHasKey('mobile', $imagesWithCrops[$this->media1->id]);
    }

    public function test_image_as_array()
    {
        // Attach image with metadata
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'featured',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([
                'altText' => ['en' => 'Custom alt text'],
                'caption' => ['en' => 'Custom caption'],
                'video' => ['en' => 'https://example.com/video.mp4']
            ]),
        ]);

        $imageArray = $this->model->imageAsArray('featured');

        $this->assertArrayHasKey('src', $imageArray);
        $this->assertArrayHasKey('width', $imageArray);
        $this->assertArrayHasKey('height', $imageArray);
        $this->assertArrayHasKey('alt', $imageArray);
        $this->assertArrayHasKey('caption', $imageArray);
        $this->assertArrayHasKey('video', $imageArray);

        $this->assertEquals(1920, $imageArray['width']);
        $this->assertEquals(1080, $imageArray['height']);
    }

    public function test_images_as_arrays()
    {
        // Attach multiple images
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'slideshow',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([
                'altText' => ['en' => 'Custom alt text'],
                'caption' => ['en' => 'Custom caption'],
                'video' => ['en' => 'https://example.com/video.mp4']
            ]),
        ]);

        $this->model->medias()->attach($this->media2->id, [
            'role' => 'slideshow',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1280,
            'crop_h' => 720,
            'metadatas' => json_encode([
                'altText' => ['en' => 'Custom alt text'],
                'caption' => ['en' => 'Custom caption'],
                'video' => ['en' => 'https://example.com/video.mp4']
            ]),
        ]);

        $imagesArrays = $this->model->imagesAsArrays('slideshow');
        $this->assertCount(2, $imagesArrays);
        $this->assertArrayHasKey('src', $imagesArrays[0]);
        $this->assertArrayHasKey('src', $imagesArrays[1]);
    }

    public function test_images_as_arrays_with_crops()
    {
        // Attach images with different crops
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'product',
            'crop' => 'main',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([
                'altText' => ['en' => 'Custom alt text'],
                'caption' => ['en' => 'Custom caption'],
                'video' => ['en' => 'https://example.com/video.mp4']
            ]),
        ]);

        $this->model->medias()->attach($this->media1->id, [
            'role' => 'product',
            'crop' => 'thumb',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 300,
            'crop_h' => 300,
            'metadatas' => json_encode([
                'altText' => ['en' => 'Custom alt text'],
                'caption' => ['en' => 'Custom caption'],
                'video' => ['en' => 'https://example.com/video.mp4']
            ]),
        ]);

        $imagesArraysWithCrops = $this->model->imagesAsArraysWithCrops('product');

        $this->assertArrayHasKey($this->media1->id, $imagesArraysWithCrops);
        $this->assertArrayHasKey('main', $imagesArraysWithCrops[$this->media1->id]);
        $this->assertArrayHasKey('thumb', $imagesArraysWithCrops[$this->media1->id]);
    }

    public function test_image_alt_text()
    {
        // Test with pivot metadata
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'hero',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([
                'altText' => ['en' => 'Custom alt text from metadata']
            ]),
        ]);

        $altText = $this->model->imageAltText('hero');
        $this->assertEquals('Custom alt text from metadata', $altText);

        // Test fallback to media alt_text
        $this->model->medias()->attach($this->media2->id, [
            'role' => 'banner',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1280,
            'crop_h' => 720,
            'metadatas' => json_encode([
                'altText' => ['en' => 'Custom alt text'],
                'caption' => ['en' => 'Custom caption'],
                'video' => ['en' => 'https://example.com/video.mp4']
            ]),
        ]);

        $this->model->refresh();

        $altText = $this->model->imageAltText('banner');
        $this->assertEquals('Custom alt text', $altText);
    }

    public function test_image_caption()
    {
        // Test with pivot metadata
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'article',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([
                'caption' => ['en' => 'Custom caption from metadata']
            ]),
        ]);

        $caption = $this->model->imageCaption('article');
        $this->assertEquals('Custom caption from metadata', $caption);

        // Test fallback to media caption
        $this->model->medias()->attach($this->media2->id, [
            'role' => 'news',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1280,
            'crop_h' => 720,
            'metadatas' => json_encode([
                'caption' => ['en' => 'News caption'],
            ]),
        ]);

        $this->model->refresh();

        $caption = $this->model->imageCaption('news');
        $this->assertEquals('News caption', $caption);
    }

    public function test_image_video()
    {
        // Test with video metadata
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'cover',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([
                'video' => ['en' => 'https://example.com/video.mp4']
            ]),
        ]);

        $video = $this->model->imageVideo('cover');
        $this->assertEquals('https://example.com/video.mp4', $video);

        // Test with string video (legacy format)
        $this->model->medias()->attach($this->media2->id, [
            'role' => 'legacy',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1280,
            'crop_h' => 720,
            'metadatas' => json_encode([
                'video' => 'https://example.com/legacy-video.mp4'
            ]),
        ]);

        $this->model->refresh();

        $video = $this->model->imageVideo('legacy');
        $this->assertEquals('https://example.com/legacy-video.mp4', $video);
    }

    public function test_image_object()
    {
        // Attach image
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'profile',
            'crop' => 'square',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 500,
            'crop_h' => 500,
            'metadatas' => json_encode([]),
        ]);

        $imageObject = $this->model->imageObject('profile', 'square');
        $this->assertInstanceOf(Media::class, $imageObject);
        $this->assertEquals($this->media1->id, $imageObject->id);
        $this->assertEquals('profile', $imageObject->pivot->role);
        $this->assertEquals('square', $imageObject->pivot->crop);
    }

    public function test_image_objects()
    {
        // Attach multiple images with same role and crop
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'portfolio',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([]),
        ]);

        $this->model->medias()->attach($this->media2->id, [
            'role' => 'portfolio',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1280,
            'crop_h' => 720,
            'metadatas' => json_encode([]),
        ]);

        $imageObjects = $this->model->imageObjects('portfolio');
        $this->assertCount(2, $imageObjects);
        $this->assertInstanceOf(Media::class, $imageObjects->first());
    }

    public function test_low_quality_image_placeholder()
    {
        // Attach image with LQIP data
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'hero',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'lqip_data' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD...',
            'metadatas' => json_encode([]),
        ]);

        $lqip = $this->model->lowQualityImagePlaceholder('hero');
        $this->assertEquals('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD...', $lqip);

        // Test without LQIP data (should return transparent fallback)
        $this->model->medias()->attach($this->media2->id, [
            'role' => 'banner',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1280,
            'crop_h' => 720,
            'metadatas' => json_encode([]),
        ]);

        $lqip = $this->model->lowQualityImagePlaceholder('banner');
        $this->assertEquals(ImageService::getTransparentFallbackUrl(), $lqip);

        // Test with fallback enabled
        $lqip = $this->model->lowQualityImagePlaceholder('nonexistent', 'default', [], true);
        $this->assertNull($lqip);
    }

    public function test_social_image()
    {
        // Attach image
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'og_image',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1200,
            'crop_h' => 630,
            'metadatas' => json_encode([]),
        ]);

        $socialUrl = $this->model->socialImage('og_image');
        $expectedUrl = ImageService::getSocialUrl($this->media1->uuid, [
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1200,
            'crop_h' => 630,
        ]);

        $this->assertEquals($expectedUrl, $socialUrl);

        // Test with fallback
        $socialUrl = $this->model->socialImage('nonexistent', 'default', [], true);
        $this->assertNull($socialUrl);

        // Test without fallback
        $socialUrl = $this->model->socialImage('nonexistent');
        $this->assertEquals(ImageService::getSocialFallbackUrl(), $socialUrl);
    }

    public function test_cms_image()
    {
        // Attach image
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'admin_thumb',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 300,
            'crop_h' => 200,
            'metadatas' => json_encode([]),
        ]);

        $cmsUrl = $this->model->cmsImage('admin_thumb');
        $expectedUrl = ImageService::getCmsUrl($this->media1->uuid, [
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 300,
            'crop_h' => 200,
        ]);

        $this->assertEquals($expectedUrl, $cmsUrl);
    }

    public function test_default_cms_image()
    {
        // Attach any image to the model
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'any_role',
            'crop' => 'any_crop',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([]),
        ]);

        $defaultCmsUrl = $this->model->defaultCmsImage();
        $this->assertNotNull($defaultCmsUrl);

        // Test with no images
        $emptyModel = new TestMediableModel(['name' => 'Empty Model']);
        $emptyModel->save();

        $defaultCmsUrl = $emptyModel->defaultCmsImage();
        $this->assertEquals(ImageService::getTransparentFallbackUrl([]), $defaultCmsUrl);
    }

    public function test_locale_support()
    {
        Config::set('media_library.translated_form_fields', true);

        $testModelWithLocale = new class extends Model
        {
            use ModelHelpers, HasImages;

            protected $table = 'test_mediable_models';
            protected $fillable = ['name'];
        };

        $modelWithLocale = new $testModelWithLocale(['name' => 'Locale Model']);
        $modelWithLocale->save();

        // Attach images with different locales
        $modelWithLocale->medias()->attach($this->media1->id, [
            'role' => 'banner',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([]),
        ]);

        $modelWithLocale->medias()->attach($this->media2->id, [
            'role' => 'banner',
            'crop' => 'default',
            'locale' => 'fr',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1280,
            'crop_h' => 720,
            'metadatas' => json_encode([]),
        ]);

        // Test locale-specific retrieval
        $enImage = $modelWithLocale->image('banner', 'default', [], false, false, null, 'en');
        $frImage = $modelWithLocale->image('banner', 'default', [], false, false, null, 'fr');

        $this->assertNotEquals($enImage, $frImage);

        Config::set('media_library.translated_form_fields', false);
    }

    public function test_find_media_method_with_complex_scenario()
    {
        $testModelWithComplexMedia = new class extends Model
        {
            use ModelHelpers, HasImages;

            protected $table = 'test_mediable_models';
            protected $fillable = ['name'];

            // Make findMedia public for testing
            public function findMediaPublic($role, $crop = 'default', $locale = null)
            {
                return $this->findMedia($role, $crop, $locale);
            }
        };

        $complexModel = new $testModelWithComplexMedia(['name' => 'Complex Model']);
        $complexModel->save();

        // Attach medias with different roles, crops, and locales
        $complexModel->medias()->attach($this->media1->id, [
            'role' => 'header',
            'crop' => 'desktop',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 400,
            'metadatas' => json_encode([]),
        ]);

        $complexModel->medias()->attach($this->media2->id, [
            'role' => 'header',
            'crop' => 'mobile',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 800,
            'crop_h' => 200,
            'metadatas' => json_encode([]),
        ]);

        $complexModel->medias()->attach($this->media3->id, [
            'role' => 'sidebar',
            'crop' => 'default',
            'locale' => 'fr',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 300,
            'crop_h' => 400,
            'metadatas' => json_encode([]),
        ]);

        // Test finding specific media
        $headerDesktop = $complexModel->findMediaPublic('header', 'desktop', 'en');
        $headerMobile = $complexModel->findMediaPublic('header', 'mobile', 'en');
        $sidebarFr = $complexModel->findMediaPublic('sidebar', 'default', 'fr');
        $nonExistent = $complexModel->findMediaPublic('nonexistent', 'default', 'en');

        $this->assertEquals($this->media1->id, $headerDesktop->id);
        $this->assertEquals($this->media2->id, $headerMobile->id);
        $this->assertEquals($this->media3->id, $sidebarFr->id);
        $this->assertNull($nonExistent);
    }

    public function test_model_events_media_detachment()
    {
        // Attach some media
        $this->model->medias()->attach($this->media1->id, [
            'role' => 'test',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 1920,
            'crop_h' => 1080,
            'metadatas' => json_encode([]),
        ]);

        // Verify attachment
        $this->assertDatabaseHas(modularityConfig('tables.mediables', 'twill_mediables'), [
            'media_id' => $this->media1->id,
            'mediable_id' => $this->model->id,
            'mediable_type' => get_class($this->model),
        ]);

        // Delete the model (should trigger media detachment)
        $this->model->delete();

        // Verify detachment
        $this->assertDatabaseMissing(modularityConfig('tables.mediables', 'twill_mediables'), [
            'media_id' => $this->media1->id,
            'mediable_id' => $this->model->id,
            'mediable_type' => get_class($this->model),
        ]);
    }

    public function test_icon_attribute_functionality()
    {
        $testModelWithIcon = new class extends Model
        {
            use HasImages;

            protected $table = 'test_mediable_models';
            protected $fillable = ['name'];

            // Mock getRouteInputs to simulate icon input
            public function getRouteInputs()
            {
                return [
                    [
                        'type' => 'image',
                        'isIcon' => true,
                        'name' => 'icon'
                    ]
                ];
            }
        };

        $iconModel = new $testModelWithIcon(['name' => 'Icon Model']);
        $iconModel->save();

        // Attach image for icon
        $iconModel->medias()->attach($this->media1->id, [
            'role' => 'images',
            'crop' => 'default',
            'locale' => 'en',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 100,
            'crop_h' => 100,
            'metadatas' => json_encode([]),
        ]);

        // Retrieve the model to trigger the retrieved event
        $retrievedModel = $testModelWithIcon::find($iconModel->id);

        // Check if _icon attribute is set
        $this->assertEquals(ImageService::getRawUrl($this->media1->uuid), $retrievedModel->_icon);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

// Basic test model that uses HasImages trait
class TestMediableModel extends Model
{
    use HasImages;

    protected $table = 'test_mediable_models';
    protected $fillable = ['name'];

    // Mock getRouteInputs for basic functionality
    public function getRouteInputs()
    {
        return [];
    }
}
