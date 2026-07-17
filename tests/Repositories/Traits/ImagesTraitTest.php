<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Entities\Media;
use Unusualify\Modularous\Entities\Traits\HasImages;
use Unusualify\Modularous\Repositories\Traits\ImagesTrait;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\Repositories\TestRepository;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class ImagesTraitTest extends RepositoryTestCase
{
    use RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(ImagesTestRepository::class);
    }

    public function test_set_columns_images_trait_collects_image_inputs()
    {
        $repo = new class
        {
            use ImagesTrait;
        };

        $columns = $repo->setColumnsImagesTrait([], [
            ['name' => 'photo', 'type' => 'file'],
            ['name' => 'avatar', 'type' => 'image'],
            ['name' => 'gallery', 'type' => 'image'],
        ]);

        $this->assertSame(['avatar', 'gallery'], $columns['ImagesTrait']);
    }

    public function test_attach_uploaded_media_to_model_non_translated_role()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-1.jpg',
            'filename' => 'img-1.jpg',
            'alt_text' => 'img-1.jpg',
            'caption' => 'img-1.jpg',
            'width' => 100,
            'height' => 100,
        ]);
        $media2 = Media::create([
            'uuid' => 'uploads/folder/img-2.jpg',
            'filename' => 'img-2.jpg',
            'alt_text' => 'img-2.jpg',
            'caption' => 'img-2.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => false,
            ],
        ];

        $model = $this->repository->create([
            'name' => 'With Image',
            'image-1' => [
                [
                    'id' => $media->id,
                    'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]],
                ],
                [
                    'id' => $media2->id,
                    'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]],
                ],
            ],
        ], $schema);

        $fields = [
            'image-1' => [
                [
                    'id' => $media->id,
                    'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]],
                ],
            ],
        ];

        $this->repository->update($model->id, $fields, $schema);

        $fresh = $model->fresh();
        $this->assertTrue($fresh->medias->contains('id', $media->id));
        $this->assertFalse($fresh->medias->contains('id', $media2->id));
        $this->assertCount(1, $fresh->medias);
    }

    public function test_detach_media_when_payload_omits_role_after_previous_attachment()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-2.jpg',
            'filename' => 'img-2.jpg',
            'alt_text' => 'img-2.jpg',
            'caption' => 'img-2.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $model = $this->repository->create(['name' => 'With Image']);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => false,
            ],
        ];

        $this->repository->update($model->id, [
            'image-1' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
        ], $schema);
        $this->assertTrue($model->fresh()->medias->contains('id', $media->id));

        $this->repository->update($model->id, [], $schema);
        $this->assertSame(1, $model->fresh()->medias()->count());
    }

    public function test_attach_translated_media_role_attaches_with_locale_pivot()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-3.jpg',
            'filename' => 'img-3.jpg',
            'alt_text' => 'img-3.jpg',
            'caption' => 'img-3.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Translated Image']);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => true,
            ],
        ];

        $fields = [
            'image-1' => [
                'en' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
            ],
        ];

        $this->repository->update($model->id, $fields, $schema);

        $pivot = $model->fresh()->medias->where('id', $media->id)->first()->pivot;
        $this->assertSame('image-1', $pivot->role);
        $this->assertSame('en', $pivot->locale);
    }

    public function test_detach_media_for_one_locale_does_not_remove_same_media_from_other_locale()
    {
        config(['translatable.locales' => ['en', 'tr']]);

        $media = Media::create([
            'uuid' => 'uploads/folder/shared.jpg',
            'filename' => 'shared.jpg',
            'alt_text' => 'shared.jpg',
            'caption' => 'shared.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => true,
            ],
        ];

        $inputsMap = Arr::mapWithKeys($schema, fn ($i) => [$i['name'] => $i]);
        $repo = new ImagesTestRepositoryTranslated(new ImagesTestModel, $inputsMap);
        $model = $repo->create(['name' => 'Shared Media'], $schema);

        $repo->update($model->id, [
            'image-1' => [
                'en' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
                'tr' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
            ],
        ], $schema);

        $repo->update($model->id, [
            'image-1' => [
                'en' => [],
                'tr' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
            ],
        ], $schema);

        $fresh = $model->fresh()->load('medias');
        $this->assertCount(1, $fresh->medias);
        $this->assertSame('tr', $fresh->medias->first()->pivot->locale);
    }

    public function test_ignored_medias_field_skips_sync_and_attachment()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-4.jpg',
            'filename' => 'img-4.jpg',
            'alt_text' => 'img-4.jpg',
            'caption' => 'img-4.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Ignored Medias']);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => false,
            ],
        ];

        $this->repository->addIgnoreFieldsBeforeSave('medias');

        $this->repository->update($model->id, [
            'image-1' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
        ], $schema);

        $this->assertSame(0, $model->fresh()->medias()->count());
    }

    public function test_hydrate_images_trait_sets_medias_relation()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-5.jpg',
            'filename' => 'img-5.jpg',
            'alt_text' => 'img-5.jpg',
            'caption' => 'img-5.jpg',
            'width' => 120,
            'height' => 80,
        ]);

        $model = $this->repository->create(['name' => 'Hydrate Medias']);

        // Define inputs so ImagesTrait knows the image role
        $schema = [
            ['name' => 'image-1', 'type' => 'image', 'translated' => false],
        ];
        $this->repository->setColumns($this->repository->chunkInputs($schema));

        $fields = [
            'image-1' => [
                [
                    'id' => $media->id,
                    'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]],
                ],
            ],
        ];

        $hydrated = $this->repository->hydrate($model, $fields);

        $this->assertTrue($hydrated->relationLoaded('medias'));
        $this->assertSame(1, $hydrated->medias->count());
        $pivot = $hydrated->medias->first()->pivot;
        $this->assertSame('image-1', $pivot->role);
        $this->assertSame('default', $pivot->crop);
        $this->assertNotEmpty($pivot->locale);
        $this->assertIsString($pivot->metadatas);

        $model = $model->fresh();
        $this->repository->addIgnoreFieldsBeforeSave('medias');
        $hydrated = $this->repository->hydrate($model, $fields);
        $this->assertFalse($hydrated->relationLoaded('medias'));
    }

    public function test_get_form_fields_images_trait_non_translated()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-6.jpg',
            'filename' => 'img-6.jpg',
            'alt_text' => 'img-6.jpg',
            'caption' => 'img-6.jpg',
            'width' => 200,
            'height' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Show Fields']);
        $model->medias()->attach($media->id, [
            'role' => 'image-1',
            'crop' => 'default',
            'locale' => config('app.locale', 'en'),
            'metadatas' => json_encode(['default' => ['caption' => null, 'altText' => null, 'video' => null]]),
        ]);
        $model->load('medias');

        $schema = [
            'image-1' => ['name' => 'image-1', 'type' => 'image', 'translated' => false],
        ];

        $fields = $this->repository->getFormFields($model, $schema);
        $this->assertArrayHasKey('name', $fields);
        $this->assertInstanceOf(Collection::class, $fields['image-1']);
        $this->assertSame('img-6.jpg', $fields['image-1'][0]['name']);
        $this->assertSame(200, $fields['image-1'][0]['width']);
        $this->assertSame(100, $fields['image-1'][0]['height']);
    }

    public function test_get_form_fields_images_trait_translated_groups_by_locale()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-7.jpg',
            'filename' => 'img-7.jpg',
            'alt_text' => 'img-7.jpg',
            'caption' => 'img-7.jpg',
            'width' => 300,
            'height' => 150,
        ]);

        // Provide inputs() with translated=true through a custom repository subclass
        $schema = [
            'image-1' => ['name' => 'image-1', 'type' => 'image', 'translated' => true],
        ];
        $inputsMap = Arr::mapWithKeys($schema, fn ($i) => [$i['name'] => $i]);
        $repo = new ImagesTestRepositoryTranslated(new ImagesTestModel, $inputsMap);
        $model = $repo->create(['name' => 'Translated Show Fields'], $schema);
        $model->medias()->attach($media->id, [
            'role' => 'image-1',
            'crop' => 'default',
            'locale' => config('app.locale', 'en'),
            'metadatas' => json_encode(['default' => ['caption' => null, 'altText' => null, 'video' => null]]),
        ]);
        $model->load('medias');

        $fields = $repo->getFormFields($model, $schema);
        $this->assertArrayHasKey('image-1', $fields);
        $this->assertArrayHasKey(config('app.locale', 'en'), $fields['image-1']);
        $this->assertSame('img-7.jpg', $fields['image-1'][config('app.locale', 'en')][0]['name']);
    }

    public function test_get_form_fields_images_trait_returns_empty_array_if_no_medias()
    {
        config(['translatable.locales' => ['en', 'tr']]);
        $model = $this->repository->create(['name' => 'No Medias']);
        $schema = [
            'image-1' => ['name' => 'image-1', 'type' => 'image', 'translated' => false],
            'image-2' => ['name' => 'image-2', 'type' => 'image', 'translated' => true],
        ];
        $fields = $this->repository->getFormFields($model, $schema);

        $this->assertArrayHasKey('image-1', $fields);
        $this->assertInstanceOf(Collection::class, $fields['image-1']);
        $this->assertCount(0, $fields['image-1']);
        $this->assertArrayHasKey('image-2', $fields);
        $this->assertInstanceOf(Collection::class, $fields['image-2']);
        $this->assertCount(2, $fields['image-2']);
        $this->assertArrayHasKey('en', $fields['image-2']);
        $this->assertArrayHasKey('tr', $fields['image-2']);
        $this->assertCount(0, $fields['image-2']['en']);
        $this->assertCount(0, $fields['image-2']['tr']);
    }

    public function test_get_crops_returns_model_medias_params()
    {
        $this->assertSame(
            ['default' => ['ratio' => 'free', 'name' => 'Default']],
            $this->repository->getCrops('image-1')
        );
    }
}

class ImagesTestModel extends TestModel
{
    use HasImages;

    public $mediasParams = [
        'image-1' => [
            'default' => [
                'ratio' => 'free',
                'name' => 'Default',
            ],
        ],
    ];
}

class ImagesTestRepository extends TestRepository
{
    use ImagesTrait;

    public function __construct(ImagesTestModel $model)
    {
        $this->model = $model;
    }
}

class ImagesTestRepositoryTranslated extends ImagesTestRepository
{
    protected array $testInputs;

    public function __construct(ImagesTestModel $model, array $inputsMap)
    {
        parent::__construct($model);
        $this->testInputs = $inputsMap;
    }

    public function inputs($noGroupChunk = false)
    {
        return $this->testInputs;
    }
}
