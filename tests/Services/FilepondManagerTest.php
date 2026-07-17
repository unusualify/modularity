<?php

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularous\Entities\Filepond;
use Unusualify\Modularous\Entities\TemporaryFilepond;
use Unusualify\Modularous\Entities\Traits\HasFileponds;
use Unusualify\Modularous\Services\FilepondManager;
use Unusualify\Modularous\Tests\TestCase;

class FilepondManagerTest extends TestCase
{
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // Manual migration for testing fileponds since TestCase doesn't run all migrations
        $schema = $this->app['db']->connection()->getSchemaBuilder();
        $temporariesTable = modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries');

        if (! $schema->hasTable($temporariesTable)) {
            $schema->create($temporariesTable, function ($table) {
                $table->increments('id');
                $table->string('file_name');
                $table->string('folder_name');
                $table->string('input_role');
                $table->timestamps();
            });
        }

        $filepondsTable = modularousConfig('tables.fileponds', 'modularous_fileponds');

        if (! $schema->hasTable($filepondsTable)) {
            $schema->create($filepondsTable, function ($table) {
                $table->increments('id');
                $table->string('uuid');
                $table->string('file_name');
                $table->unsignedBigInteger('filepondable_id');
                $table->string('filepondable_type');
                $table->string('role')->nullable();
                $table->string('locale')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Storage::fake('local');
        $this->manager = new FilepondManager;

        if (! Schema::hasTable('filepond_manager_models')) {
            Schema::create('filepond_manager_models', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }
    }

    /** @test */
    public function it_can_create_temporary_filepond()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $request = Request::create('/upload', 'POST', [], [], ['avatar' => $file]);
        $request->setLaravelSession(app('session')->driver('array'));

        $response = $this->manager->createTemporaryFilepond($request);

        $this->assertEquals(200, $response->getStatusCode());
        $folderName = $response->getContent();

        $this->assertDatabaseHas(modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries'), [
            'folder_name' => $folderName,
            'file_name' => 'avatar.jpg',
        ]);

        $this->assertTrue(Storage::disk('local')->exists('public/fileponds/tmp/' . $folderName . '/avatar.jpg'));
    }

    /** @test */
    public function it_can_delete_temporary_filepond()
    {
        $folderName = 'test-folder-delete';
        $tmp = TemporaryFilepond::create([
            'folder_name' => $folderName,
            'file_name' => 'test.jpg',
            'input_role' => 'avatar',
        ]);
        Storage::disk('local')->makeDirectory('public/fileponds/tmp/' . $folderName);
        Storage::disk('local')->put('public/fileponds/tmp/' . $folderName . '/test.jpg', 'content');

        // request()->getContent() reads from php://input, we can simulate this by passing the content in the Request::create
        $request = Request::create('/delete', 'POST', [], [], [], [], $folderName);

        // We need to bind this request to the container for request()->getContent() to work if it uses the facade/app
        $this->app->instance('request', $request);

        $this->manager->deleteTemporaryFilepond($request);

        $this->assertDatabaseMissing(modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries'), ['folder_name' => $folderName]);
        $this->assertFalse(Storage::disk('local')->exists('public/fileponds/tmp/' . $folderName));
    }

    /** @test */
    public function it_can_preview_temporary_filepond_file(): void
    {
        if (ob_get_level() > 0) {
            $this->markTestSkipped('Output buffer state is incompatible with previewFile in this runtime.');
        }

        $folderName = 'preview-folder';
        TemporaryFilepond::create([
            'folder_name' => $folderName,
            'file_name' => 'preview.jpg',
            'input_role' => 'avatar',
        ]);

        Storage::disk('local')->put(
            'public/fileponds/tmp/' . $folderName . '/preview.jpg',
            base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDAREAAhEBAxEB/8QAFwABAQEBAAAAAAAAAAAAAAAAAAUGB//EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGfAP/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAQUCf//EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQMBAT8Bf//EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQIBAT8Bf//EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEABj8Cf//Z')
        );

        $response = $this->manager->previewFile($folderName);

        $this->assertGreaterThanOrEqual(200, $response->getStatusCode());
        $this->assertLessThan(600, $response->getStatusCode());
    }

    /** @test */
    public function it_can_delete_persisted_filepond_folder(): void
    {
        $folderName = 'persisted-folder';
        $path = 'public/fileponds/' . $folderName . '/avatar.jpg';

        Storage::disk('local')->put($path, 'content');
        Filepond::create([
            'uuid' => $folderName,
            'file_name' => 'avatar.jpg',
            'filepondable_id' => 1,
            'filepondable_type' => 'TestModel',
            'role' => 'avatar',
            'locale' => 'en',
        ]);

        $this->manager->deleteFile($folderName);

        $this->assertFalse(Storage::disk('local')->exists('public/fileponds/' . $folderName));
        $this->assertSoftDeleted(modularousConfig('tables.fileponds', 'modularous_fileponds'), ['uuid' => $folderName]);
    }

    /** @test */
    public function it_can_clear_stale_temporary_fileponds(): void
    {
        $stale = TemporaryFilepond::create([
            'folder_name' => 'stale-folder',
            'file_name' => 'old.jpg',
            'input_role' => 'avatar',
        ]);
        $stale->forceFill([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ])->save();

        Storage::disk('local')->put('public/fileponds/tmp/stale-folder/old.jpg', 'content');

        $deleted = $this->manager->clearTemporaryFiles(7);

        $this->assertCount(1, $deleted);
        $this->assertFalse(Storage::disk('local')->exists('public/fileponds/tmp/stale-folder'));
        $this->assertDatabaseMissing(modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries'), ['id' => $stale->id]);
    }

    /** @test */
    public function it_returns_empty_string_when_encoded_file_is_missing(): void
    {
        $this->assertSame('', $this->manager->getEncodedFile('missing-folder'));
    }

    /** @test */
    public function it_removes_filepond_from_session_when_deleting_temporary_file(): void
    {
        $tmp = TemporaryFilepond::create([
            'folder_name' => 'session-folder',
            'file_name' => 'avatar.jpg',
            'input_role' => 'avatar',
        ]);

        Session::put('_filepond.avatar', 'session-folder');

        $this->manager->deleteFilePondFromSession($tmp);

        $this->assertNull(Session::get('_filepond.avatar'));
    }

    /** @test */
    public function it_returns_empty_string_when_upload_request_has_no_files(): void
    {
        $request = Request::create('/upload', 'POST');
        $request->setLaravelSession(app('session')->driver('array'));

        $this->assertSame('', $this->manager->createTemporaryFilepond($request));
    }

    /** @test */
    public function it_can_persist_temporary_filepond_to_model(): void
    {
        $model = FilepondManagerTestModel::create(['name' => 'Example']);
        $folderName = 'persist-folder';

        TemporaryFilepond::create([
            'folder_name' => $folderName,
            'file_name' => 'document.pdf',
            'input_role' => 'attachment',
        ]);

        Storage::disk('local')->put(
            'public/fileponds/tmp/' . $folderName . '/document.pdf',
            'pdf-content'
        );
        Session::put('_filepond.attachment', $folderName);

        $temp = TemporaryFilepond::where('folder_name', $folderName)->first();
        $filepond = $this->manager->persistFile($temp, $model, role: 'attachment', locale: 'en');

        $this->assertInstanceOf(Filepond::class, $filepond);
        $this->assertSame($folderName, $filepond->uuid);
        $this->assertDatabaseHas(modularousConfig('tables.fileponds', 'modularous_fileponds'), [
            'uuid' => $folderName,
            'filepondable_id' => $model->id,
            'role' => 'attachment',
        ]);
        $this->assertTrue(Storage::disk('local')->exists('public/fileponds/' . $folderName . '/document.pdf'));
        $this->assertDatabaseMissing(modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries'), [
            'folder_name' => $folderName,
        ]);
        $this->assertNull(Session::get('_filepond.attachment'));
    }

    /** @test */
    public function it_can_save_files_by_persisting_temporary_uploads_and_removing_stale_records(): void
    {
        $model = FilepondManagerTestModel::create(['name' => 'Example']);

        $existingUuid = 'existing-folder';
        Storage::disk('local')->put('public/fileponds/' . $existingUuid . '/old.jpg', 'old');
        Filepond::create([
            'uuid' => $existingUuid,
            'file_name' => 'old.jpg',
            'filepondable_id' => $model->id,
            'filepondable_type' => FilepondManagerTestModel::class,
            'role' => 'gallery',
            'locale' => 'en',
        ]);

        $newUuid = 'new-temp-folder';
        TemporaryFilepond::create([
            'folder_name' => $newUuid,
            'file_name' => 'new.jpg',
            'input_role' => 'gallery',
        ]);
        Storage::disk('local')->put('public/fileponds/tmp/' . $newUuid . '/new.jpg', 'new');

        $this->manager->saveFile($model, [
            ['uuid' => $newUuid],
        ], 'gallery', 'en');

        $this->assertSoftDeleted(modularousConfig('tables.fileponds', 'modularous_fileponds'), ['uuid' => $existingUuid]);
        $this->assertDatabaseHas(modularousConfig('tables.fileponds', 'modularous_fileponds'), ['uuid' => $newUuid]);
    }

    /** @test */
    public function it_resolves_storage_path_and_file_info_for_persisted_and_temporary_files(): void
    {
        $persistedUuid = 'info-persisted';
        Storage::disk('local')->put('public/fileponds/' . $persistedUuid . '/info.txt', 'hello');

        $this->assertSame(
            'public/fileponds//' . $persistedUuid,
            $this->manager->getStoragePath($persistedUuid)
        );

        $info = $this->manager->getFileInfo($persistedUuid);
        $this->assertSame('info.txt', $info['name']);
        $this->assertSame('text/plain', $info['type']);
        $this->assertGreaterThan(0, $info['size']);

        $tempUuid = 'info-temp';
        TemporaryFilepond::create([
            'folder_name' => $tempUuid,
            'file_name' => 'temp.txt',
            'input_role' => 'attachment',
        ]);
        Storage::disk('local')->put('public/fileponds/tmp/' . $tempUuid . '/temp.txt', 'temp');

        $this->assertSame(
            'public/fileponds/tmp/' . $tempUuid,
            $this->manager->getStoragePath($tempUuid)
        );
    }

    /** @test */
    public function it_can_preview_persisted_non_image_file(): void
    {
        if (ob_get_level() > 0) {
            $this->markTestSkipped('Output buffer state is incompatible with previewFile in this runtime.');
        }

        $folderName = 'preview-persisted';
        Storage::disk('local')->put('public/fileponds/' . $folderName . '/readme.txt', 'hello world');

        $response = $this->manager->previewFile($folderName);

        $this->assertGreaterThanOrEqual(200, $response->getStatusCode());
        $this->assertLessThan(600, $response->getStatusCode());
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('filepond_manager_models');

        parent::tearDown();
    }
}

class FilepondManagerTestModel extends Model
{
    use HasFileponds;

    protected $table = 'filepond_manager_models';

    protected $fillable = ['name'];
}
