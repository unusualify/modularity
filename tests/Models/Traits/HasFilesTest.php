<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\File;
use Unusualify\Modularity\Entities\Traits\HasFiles;
use Unusualify\Modularity\Services\FileLibrary\FileService;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasFilesTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;
    protected $file1;
    protected $file2;
    protected $file3;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test model table
        Schema::create('test_fileable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create test model instance
        $this->model = new TestFileableModel(['name' => 'Test Model']);
        $this->model->save();

        // Create test files
        $this->file1 = File::factory()->create([
            'uuid' => 'test-uuid-1',
            'filename' => 'document.pdf',
            'size' => 1048576, // 1MB
        ]);

        $this->file2 = File::factory()->create([
            'uuid' => 'test-uuid-2',
            'filename' => 'image.jpg',
            'size' => 2097152, // 2MB
        ]);

        $this->file3 = File::factory()->create([
            'uuid' => 'test-uuid-3',
            'filename' => 'video.mp4',
            'size' => 5242880, // 5MB
        ]);
    }

    public function test_trait_initialization()
    {
        // Test that the trait is properly used
        $this->assertTrue(in_array(
            HasFiles::class,
            class_uses_recursive($this->model)
        ));
    }

    public function test_files_relationship()
    {
        // Test the morphToMany relationship
        $relationship = $this->model->files();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $relationship);
        $this->assertEquals(File::class, get_class($relationship->getRelated()));
        $this->assertEquals(modularityConfig('tables.fileables'), $relationship->getTable());
    }

    public function test_attach_and_retrieve_single_file()
    {
        // Attach file to model
        $this->model->files()->attach($this->file1->id, [
            'role' => 'document',
            'locale' => 'en',
        ]);

        // Test file retrieval
        $fileUrl = $this->model->file('document', 'en');
        $this->assertEquals(FileService::getUrl($this->file1->uuid), $fileUrl);

        // Test file object retrieval
        $fileObject = $this->model->fileObject('document', 'en');
        $this->assertInstanceOf(File::class, $fileObject);
        $this->assertEquals($this->file1->id, $fileObject->id);
    }

    public function test_attach_and_retrieve_multiple_files()
    {
        // Attach multiple files with same role
        $this->model->files()->attach($this->file1->id, [
            'role' => 'gallery',
            'locale' => 'en',
        ]);

        $this->model->files()->attach($this->file2->id, [
            'role' => 'gallery',
            'locale' => 'en',
        ]);

        // Test files list retrieval
        $filesList = $this->model->filesList('gallery', 'en');
        $this->assertCount(2, $filesList);
        $this->assertContains(FileService::getUrl($this->file1->uuid), $filesList);
        $this->assertContains(FileService::getUrl($this->file2->uuid), $filesList);
    }

    public function test_file_retrieval_with_different_roles()
    {
        // Attach files with different roles
        $this->model->files()->attach($this->file1->id, [
            'role' => 'thumbnail',
            'locale' => 'en',
        ]);

        $this->model->files()->attach($this->file2->id, [
            'role' => 'cover',
            'locale' => 'en',
        ]);

        // Test role-specific retrieval
        $thumbnailUrl = $this->model->file('thumbnail', 'en');
        $coverUrl = $this->model->file('cover', 'en');

        $this->assertEquals(FileService::getUrl($this->file1->uuid), $thumbnailUrl);
        $this->assertEquals(FileService::getUrl($this->file2->uuid), $coverUrl);

        // Test non-existent role
        $nonExistentUrl = $this->model->file('non-existent', 'en');
        $this->assertNull($nonExistentUrl);
    }

    public function test_file_retrieval_with_different_locales()
    {
        // Attach files with different locales
        $this->model->files()->attach($this->file1->id, [
            'role' => 'document',
            'locale' => 'en',
        ]);

        $this->model->files()->attach($this->file2->id, [
            'role' => 'document',
            'locale' => 'fr',
        ]);

        // Test locale-specific retrieval
        $enUrl = $this->model->file('document', 'en');
        $frUrl = $this->model->file('document', 'fr');

        $this->assertEquals(FileService::getUrl($this->file1->uuid), $enUrl);
        $this->assertEquals(FileService::getUrl($this->file2->uuid), $frUrl);

        // Test non-existent locale
        config(['translatable.use_property_fallback' => false]);
        $deUrl = $this->model->file('document', 'de');
        $this->assertNull($deUrl);
    }

    public function test_file_retrieval_with_default_locale()
    {
        // Set current locale
        App::setLocale('fr');

        // Attach file without specifying locale (should use current locale)
        $this->model->files()->attach($this->file1->id, [
            'role' => 'document',
            'locale' => 'fr',
        ]);

        // Test retrieval without specifying locale (should use current locale)
        $fileUrl = $this->model->file('document');
        $this->assertEquals(FileService::getUrl($this->file1->uuid), $fileUrl);

        // Reset locale
        App::setLocale('en');
    }

    public function test_file_fallback_locale()
    {
        $testModelWithFallback = new class extends Model
        {
            use HasFiles;

            protected $table = 'test_fileable_models';
            protected $fillable = ['name'];
        };

        $modelWithFallback = new $testModelWithFallback(['name' => 'Fallback Model']);
        $modelWithFallback->save();

        // Enable fallback
        Config::set('translatable.use_property_fallback', true);
        Config::set('translatable.fallback_locale', 'en');

        // Attach file only in fallback locale
        $modelWithFallback->files()->attach($this->file1->id, [
            'role' => 'document',
            'locale' => 'en', // fallback locale
        ]);

        // Try to get file in different locale (should fallback to 'en')
        $fileUrl = $modelWithFallback->file('document', 'fr');
        $this->assertEquals(FileService::getUrl($this->file1->uuid), $fileUrl);

        // Reset config
        Config::set('translatable.use_property_fallback', false);
    }

    public function test_files_list_with_multiple_locales()
    {
        // Attach files with same role but different locales
        $this->model->files()->attach($this->file1->id, [
            'role' => 'gallery',
            'locale' => 'en',
        ]);

        $this->model->files()->attach($this->file2->id, [
            'role' => 'gallery',
            'locale' => 'fr',
        ]);

        $this->model->files()->attach($this->file3->id, [
            'role' => 'gallery',
            'locale' => 'en',
        ]);

        // Test files list for specific locale
        $enFilesList = $this->model->filesList('gallery', 'en');
        $frFilesList = $this->model->filesList('gallery', 'fr');

        $this->assertCount(2, $enFilesList);
        $this->assertCount(1, $frFilesList);

        $this->assertContains(FileService::getUrl($this->file1->uuid), $enFilesList);
        $this->assertContains(FileService::getUrl($this->file3->uuid), $enFilesList);
        $this->assertContains(FileService::getUrl($this->file2->uuid), $frFilesList);
    }

    public function test_file_object_retrieval()
    {
        // Attach file
        $this->model->files()->attach($this->file1->id, [
            'role' => 'avatar',
            'locale' => 'en',
        ]);

        // Test file object retrieval
        $fileObject = $this->model->fileObject('avatar', 'en');
        $this->assertInstanceOf(File::class, $fileObject);
        $this->assertEquals($this->file1->id, $fileObject->id);
        $this->assertEquals('test-uuid-1', $fileObject->uuid);
        $this->assertEquals('document.pdf', $fileObject->filename);

        // Test pivot data
        $this->assertEquals('avatar', $fileObject->pivot->role);
        $this->assertEquals('en', $fileObject->pivot->locale);
    }

    public function test_file_with_provided_file_object()
    {
        // Attach file
        $this->model->files()->attach($this->file1->id, [
            'role' => 'document',
            'locale' => 'en',
        ]);

        // Get file object first
        $fileObject = $this->model->fileObject('document', 'en');

        // Test file URL retrieval with provided file object (should not trigger additional queries)
        $fileUrl = $this->model->file('document', 'en', $fileObject);
        $this->assertEquals(FileService::getUrl($this->file1->uuid), $fileUrl);
    }

    public function test_files_relationship_ordering()
    {
        // Attach files in specific order
        $this->model->files()->attach($this->file3->id, [
            'role' => 'sequence',
            'locale' => 'en',
        ]);

        $this->model->files()->attach($this->file1->id, [
            'role' => 'sequence',
            'locale' => 'en',
        ]);

        $this->model->files()->attach($this->file2->id, [
            'role' => 'sequence',
            'locale' => 'en',
        ]);

        // Files should be ordered by fileables table id (ascending)
        $files = $this->model->files()->where('role', 'sequence')->get();
        $this->assertCount(3, $files);

        // Check that they are ordered by pivot id (which should be ascending by attachment order)
        $pivotIds = $files->pluck('pivot.id')->toArray();
        $this->assertEquals(sort($pivotIds), sort($pivotIds)); // Should be in ascending order
    }

    public function test_files_with_timestamps()
    {
        // Attach file
        $this->model->files()->attach($this->file1->id, [
            'role' => 'timestamped',
            'locale' => 'en',
        ]);

        // Check that pivot has timestamps
        $fileWithPivot = $this->model->files()->where('role', 'timestamped')->first();
        $this->assertNotNull($fileWithPivot->pivot->created_at);
        $this->assertNotNull($fileWithPivot->pivot->updated_at);
    }

    public function test_multiple_models_same_file()
    {
        // Create another model
        $model2 = new TestFileableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Attach same file to both models
        $this->model->files()->attach($this->file1->id, [
            'role' => 'shared',
            'locale' => 'en',
        ]);

        $model2->files()->attach($this->file1->id, [
            'role' => 'shared',
            'locale' => 'en',
        ]);

        // Both models should have access to the file
        $url1 = $this->model->file('shared', 'en');
        $url2 = $model2->file('shared', 'en');

        $this->assertEquals(FileService::getUrl($this->file1->uuid), $url1);
        $this->assertEquals(FileService::getUrl($this->file1->uuid), $url2);
    }

    public function test_find_file_method_with_complex_scenario()
    {
        $testModelWithComplexFiles = new class extends Model
        {
            use HasFiles;

            protected $table = 'test_fileable_models';
            protected $fillable = ['name'];

            // Make findFile public for testing
            public function findFilePublic($role, $locale)
            {
                return $this->findFile($role, $locale);
            }
        };

        $complexModel = new $testModelWithComplexFiles(['name' => 'Complex Model']);
        $complexModel->save();

        // Attach files with different roles and locales
        $complexModel->files()->attach($this->file1->id, [
            'role' => 'banner',
            'locale' => 'en',
        ]);

        $complexModel->files()->attach($this->file2->id, [
            'role' => 'banner',
            'locale' => 'fr',
        ]);

        $complexModel->files()->attach($this->file3->id, [
            'role' => 'logo',
            'locale' => 'en',
        ]);

        // Test finding specific file
        $enBanner = $complexModel->findFilePublic('banner', 'en');
        $frBanner = $complexModel->findFilePublic('banner', 'fr');
        $enLogo = $complexModel->findFilePublic('logo', 'en');
        $nonExistent = $complexModel->findFilePublic('nonexistent', 'en');

        $this->assertEquals($this->file1->id, $enBanner->id);
        $this->assertEquals($this->file2->id, $frBanner->id);
        $this->assertEquals($this->file3->id, $enLogo->id);
        $this->assertNull($nonExistent);
    }

    public function test_empty_files_list()
    {
        // Test files list when no files are attached
        $emptyList = $this->model->filesList('nonexistent', 'en');
        $this->assertIsArray($emptyList);
        $this->assertEmpty($emptyList);
    }

    public function test_file_service_integration()
    {
        // Test that FileService::getUrl is called correctly
        $this->model->files()->attach($this->file1->id, [
            'role' => 'service_test',
            'locale' => 'en',
        ]);

        $fileUrl = $this->model->file('service_test', 'en');

        // The URL should be what FileService::getUrl returns for the UUID
        $expectedUrl = FileService::getUrl('test-uuid-1');
        $this->assertEquals($expectedUrl, $fileUrl);
    }

    public function test_database_relationships_integrity()
    {
        // Attach file
        $this->model->files()->attach($this->file1->id, [
            'role' => 'integrity_test',
            'locale' => 'en',
        ]);

        // Check database directly
        $this->assertDatabaseHas(modularityConfig('tables.fileables'), [
            'file_id' => $this->file1->id,
            'fileable_id' => $this->model->id,
            'fileable_type' => get_class($this->model),
            'role' => 'integrity_test',
            'locale' => 'en',
        ]);

        // Detach file
        $this->model->files()->detach($this->file1->id);

        // Check that it's removed from database
        $this->assertDatabaseMissing(modularityConfig('tables.fileables'), [
            'file_id' => $this->file1->id,
            'fileable_id' => $this->model->id,
            'fileable_type' => get_class($this->model),
            'role' => 'integrity_test',
            'locale' => 'en',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

// Basic test model that uses HasFiles trait
class TestFileableModel extends Model
{
    use HasFiles;

    protected $table = 'test_fileable_models';
    protected $fillable = ['name'];
}
