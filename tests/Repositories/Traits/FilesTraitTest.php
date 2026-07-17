<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Mockery\MockInterface;
use Unusualify\Modularous\Entities\File as LibraryFile;
use Unusualify\Modularous\Entities\Traits\HasFiles;
use Unusualify\Modularous\Repositories\Traits\FilesTrait;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\Repositories\TestRepository;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class FilesTraitTest extends RepositoryTestCase
{
    use RepositorySources;

    /**
     * input schema for the files trait
     *
     * @var array
     */
    protected $schema = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(FilesTestRepository::class);

        $this->schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false,
            ],
        ];
    }

    public function test_set_columns_files_trait_collects_file_inputs()
    {
        $repo = new class
        {
            use FilesTrait;
        };

        $columns = $repo->setColumnsFilesTrait([], [
            'photo' => ['name' => 'photo', 'type' => 'file'],
            'avatar' => ['name' => 'avatar', 'type' => 'image'],
            'title' => ['name' => 'title', 'type' => 'text'],
        ]);

        $this->assertSame(['photo'], $columns['FilesTrait']);
    }

    public function test_hydrate_files_trait_hydrates_files()
    {
        // $temporaryFile = TemporaryFilepond::create([
        //     'file_name' => 'test.jpg',
        //     'input_role' => 'photo',
        // ]);

        $fields = [
            'photo' => [

            ],
            'name' => 'test',
        ];

        $mock = $this->partialMock(FilesTestRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getColumns')->andReturn(['photo']);
        });

        $object = new FilesTestModel;
        $object = $mock->hydrateFilesTrait($object, $fields);

        $this->assertCount(0, $object->files);
    }

    public function test_attach_uploaded_file_to_model_via_files_trait_non_translated_role()
    {
        // Arrange: create a file record as if uploaded to the file library
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-1.pdf',
            'filename' => 'file-1.pdf',
            'size' => 100,
        ]);
        $file2 = LibraryFile::create([
            'uuid' => 'uploads/folder/file-2.pdf',
            'filename' => 'file-2.pdf',
            'size' => 100,
        ]);

        // Schema defines a non-translated file role 'file-1'
        $schema = [
            'custom-files' => [
                'type' => 'input-file',
                'name' => 'custom-files',
                'translated' => false,
            ],
        ];

        // Arrange: create a model to attach files to
        $model = $this->repository->create([
            'name' => 'With File',
            'custom-files' => [
                [
                    'id' => $file->id,
                ],
                [
                    'id' => $file2->id,
                ],
            ],
        ], $schema);

        // Payload mimicking files attach: role points to uploaded file id
        $fields = [
            'custom-files' => [
                [
                    'id' => $file->id,
                ],
            ],
        ];

        // Act: update triggers FilesTrait afterSave to sync pivot and attach
        $this->repository->update($model->id, $fields, $schema);

        // Assert: pivot matches payload exactly (previous attachment for file-2 removed)
        $fresh = $model->fresh();
        $this->assertTrue($fresh->files->contains('id', $file->id));
        $this->assertFalse($fresh->files->contains('id', $file2->id));
        $this->assertCount(1, $fresh->files);
    }

    public function test_detach_files_when_payload_omits_role_after_previous_attachment()
    {
        // Arrange: create a library file and a model
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-2.pdf',
            'filename' => 'file-2.pdf',
            'size' => 100,
        ]);

        $model = $this->repository->create(['name' => 'With File']);

        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false,
            ],
        ];

        // First attach one file
        $this->repository->update($model->id, [
            'file-1' => [['id' => $file->id]],
        ], $schema);
        $this->assertTrue($model->fresh()->files->contains('id', $file->id));

        // Act: second update without role should detach all for that role (sync([]))
        $this->repository->update($model->id, [], $schema);

        // Assert: no files attached
        $this->assertSame(1, $model->fresh()->files()->count());
    }

    public function test_attach_translated_file_role_attaches_with_locale_pivot()
    {
        // Arrange: uploaded library file
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-3.pdf',
            'filename' => 'file-3.pdf',
            'size' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Translated File']);

        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => true,
            ],
        ];

        $fields = [
            'file-1' => [
                'en' => [['id' => $file->id]],
                // another locale intentionally empty
            ],
        ];

        // Act
        $this->repository->update($model->id, $fields, $schema);

        // Assert: attached with pivot locale 'en'
        $pivot = $model->fresh()->files->where('id', $file->id)->first()->pivot;
        $this->assertSame('file-1', $pivot->role);
        $this->assertSame('en', $pivot->locale);
    }

    public function test_detach_files_for_one_locale_does_not_remove_same_file_from_other_locale()
    {
        config(['translatable.locales' => ['en', 'tr']]);

        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/shared.pdf',
            'filename' => 'shared.pdf',
            'size' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Shared File']);

        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => true,
            ],
        ];

        $this->repository->update($model->id, [
            'file-1' => [
                'en' => [['id' => $file->id]],
                'tr' => [['id' => $file->id]],
            ],
        ], $schema);

        $this->repository->update($model->id, [
            'file-1' => [
                'en' => [],
                'tr' => [['id' => $file->id]],
            ],
        ], $schema);

        $fresh = $model->fresh()->load('files');
        $this->assertCount(1, $fresh->files);
        $this->assertSame('tr', $fresh->files->first()->pivot->locale);
    }

    public function test_ignored_files_field_skips_sync_and_attachment()
    {
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-4.pdf',
            'filename' => 'file-4.pdf',
            'size' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Ignored Files']);

        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false,
            ],
        ];

        $this->repository->addIgnoreFieldsBeforeSave('files');

        $this->repository->update($model->id, [
            'file-1' => [['id' => $file->id]],
        ], $schema);

        $this->assertSame(0, $model->fresh()->files()->count());
    }

    public function test_hydrate_images_trait_sets_medias_relation()
    {
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-5.pdf',
            'filename' => 'file-5.pdf',
            'size' => 100,
        ]);
        $model = $this->repository->create(['name' => 'With File']);
        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false,
            ],
        ];
        $this->repository->setColumns($this->repository->chunkInputs($schema));
        $fields = [
            'file-1' => [['id' => $file->id]],
        ];
        $model = $this->repository->hydrate($model, $fields);
        $this->assertTrue($model->relationLoaded('files'));
        $this->assertSame(1, $model->files->count());
        $pivot = $model->files->first()->pivot;
        $this->assertSame('file-1', $pivot->role);
        $this->assertNotEmpty($pivot->locale);

        $model = $model->fresh();
        $this->repository->addIgnoreFieldsBeforeSave('files');
        $hydrated = $this->repository->hydrate($model, $fields);
        $this->assertFalse($hydrated->relationLoaded('files'));
    }

    public function test_get_form_fields_files_files_trait_non_translated()
    {
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-5.pdf',
            'filename' => 'file-5.pdf',
            'size' => 100,
        ]);
        $model = $this->repository->create(['name' => 'With File']);
        $model->files()->attach($file->id, [
            'role' => 'file-1',
            'locale' => config('app.locale', 'en'),
        ]);
        $model = $model->fresh();
        $schema = [
            'file-1' => ['name' => 'file-1', 'type' => 'input-file', 'translated' => false],
        ];
        $fields = $this->repository->getFormFields($model, $schema);
        $this->assertArrayHasKey('file-1', $fields);
        $this->assertInstanceOf(Collection::class, $fields['file-1']);
        $this->assertSame('file-5.pdf', $fields['file-1'][0]['name']);
        $this->assertSame('100 B', $fields['file-1'][0]['size']);
    }

    public function test_get_form_fields_files_files_trait_translated()
    {
        config(['translatable.locales' => ['en', 'tr']]);
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-6.pdf',
            'filename' => 'file-6.pdf',
            'size' => 100,
        ]);
        $model = $this->repository->create(['name' => 'With File']);
        $model->files()->attach($file->id, [
            'role' => 'file-1',
            'locale' => 'en',
        ]);
        $model->files()->attach($file->id, [
            'role' => 'file-1',
            'locale' => 'tr',
        ]);
        $model = $model->load('files');
        $schema = [
            'file-1' => ['name' => 'file-1', 'type' => 'input-file', 'translated' => true],
        ];
        $fields = $this->repository->getFormFields($model, $schema);

        $this->assertArrayHasKey('file-1', $fields);
        $this->assertCount(2, $fields['file-1']);
        $this->assertArrayHasKey('en', $fields['file-1']);
        $this->assertArrayHasKey('tr', $fields['file-1']);
        $this->assertSame('file-6.pdf', $fields['file-1']['en'][0]['name']);
        $this->assertSame('100 B', $fields['file-1']['en'][0]['size']);
        $this->assertSame('file-6.pdf', $fields['file-1']['tr'][0]['name']);
        $this->assertSame('100 B', $fields['file-1']['tr'][0]['size']);
    }

    public function test_get_form_fields_files_files_trait_returns_empty_array_if_no_files()
    {
        config(['translatable.locales' => ['en', 'tr']]);
        $model = $this->repository->create(['name' => 'No Files']);
        $schema = [
            'file-1' => ['name' => 'file-1', 'type' => 'input-file', 'translated' => false],
            'file-2' => ['name' => 'file-2', 'type' => 'input-file', 'translated' => true],
        ];
        $fields = $this->repository->getFormFields($model, $schema);
        $this->assertArrayHasKey('file-1', $fields);
        $this->assertInstanceOf(Collection::class, $fields['file-1']);
        $this->assertCount(0, $fields['file-1']);
        $this->assertArrayHasKey('file-2', $fields);
        $this->assertInstanceOf(Collection::class, $fields['file-2']);
        $this->assertCount(2, $fields['file-2']);
        $this->assertArrayHasKey('en', $fields['file-2']);
        $this->assertArrayHasKey('tr', $fields['file-2']);
        $this->assertCount(0, $fields['file-2']['en']);
        $this->assertCount(0, $fields['file-2']['tr']);
    }

    public function test_hydrate_files_trait_keeps_persisted_files_when_file_role_missing_from_payload()
    {
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-persist.pdf',
            'filename' => 'file-persist.pdf',
            'size' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Has File']);
        $model->files()->attach($file->id, [
            'role' => 'file-1',
            'locale' => config('app.locale', 'en'),
        ]);

        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false,
            ],
        ];

        $this->repository->setColumns($this->repository->chunkInputs($schema));

        $hydrated = $this->repository->hydrate($model->fresh(), [
            'name' => 'preview-without-file-inputs',
        ]);

        $this->assertTrue($hydrated->relationLoaded('files'));
        $this->assertSame(1, $hydrated->files->count());
        $this->assertTrue($hydrated->files->contains('id', $file->id));
    }
}

class FilesTestModel extends TestModel
{
    use HasFiles;
}

class FilesTestRepository extends TestRepository
{
    use FilesTrait;

    public function __construct(FilesTestModel $model)
    {
        $this->model = $model;
    }
}
