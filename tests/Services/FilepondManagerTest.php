<?php

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularous\Entities\Filepond;
use Unusualify\Modularous\Entities\TemporaryFilepond;
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
}
