<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Mockery\MockInterface;
use Unusualify\Modularous\Entities\Media;
use Unusualify\Modularous\Facades\CurrencyExchange;
use Unusualify\Modularous\Facades\ModularousFinder;
use Unusualify\Modularous\Facades\ModularousLog;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class AbstractRepositoryTest extends RepositoryTestCase
{
    use RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();
    }

    public function test_repository_can_be_created()
    {
        $this->assertInstanceOf(TestRepository::class, $this->repository);
    }

    public function test_repository_can_get_model()
    {
        $this->assertInstanceOf(TestModel::class, $this->repository->getModel());
    }

    public function test_repository_call_model_methods()
    {
        $this->assertEquals($this->repository->getModel()->getTable(), $this->repository->getTable());
        $this->assertEquals($this->repository->getModel()->getFillable(), $this->repository->getFillable());
        $this->assertEquals($this->repository->getModel()->getDates(), $this->repository->getDates());
        $this->assertEquals($this->repository->getModel()->getAppends(), $this->repository->getAppends());
        $this->assertEquals($this->repository->getModel()->getAttributes(), $this->repository->getAttributes());
    }

    public function test_repository_get_like_operator()
    {
        $this->assertEquals($this->repository->getLikeOperator(), 'LIKE');
    }

    public function test_repository_manage_names()
    {
        $this->assertEquals($this->repository->getStudlyName('test_model'), 'TestModel');
        $this->assertEquals($this->repository->getLowerName('TestModel'), 'testmodel');
        $this->assertEquals($this->repository->getPlural('TestModel'), 'TestModels');
        $this->assertEquals($this->repository->getHeadline('test_model'), 'Test Model');
        $this->assertEquals($this->repository->getDBTableName('TestModel'), 'test_models');
        $this->assertEquals($this->repository->getSingular('TestModels'), 'TestModel');
        $this->assertEquals($this->repository->getCamelCase('test_model'), 'testModel');
        $this->assertEquals($this->repository->getKebabCase('TestModel'), 'test-model');
        $this->assertEquals($this->repository->getSnakeCase('TestModel'), 'test_model');
        $this->assertEquals($this->repository->getPascalCase('test_model'), 'TestModel');
    }

    public function test_repository_inspect_traits()
    {
        $this->assertEquals($this->repository->hasBehavior('translation'), false);
        $this->assertEquals($this->repository->isTranslatable('name'), false);

        $this->assertEquals($this->repository->isSoftDeletable(), true);
        $this->assertEquals($this->laravelRepository->isSoftDeletable(), false);

        $this->assertEquals($this->repository->hasModelTrait('Unusualify\Modularous\Entities\Traits\IsTranslatable'), true);
        $this->assertEquals($this->laravelRepository->hasModelTrait('Unusualify\Modularous\Entities\Traits\IsTranslatable'), false);

        $this->assertEquals($this->repository->hasModelTrait('Unusualify\Modularous\Entities\Traits\Core\ModelHelpers'), true);
        $this->assertEquals($this->laravelRepository->hasModelTrait('Unusualify\Modularous\Entities\Traits\Core\ModelHelpers'), true);
    }

    public function test_repository_relationship_get_columns()
    {
        $this->assertCount(0, $this->repository->getColumns('Unusualify\Modularous\Repositories\Logic\InspectTraits'));
        $this->assertCount(0, $this->repository->getColumns('Unusualify\Modularous\Repositories\Logic\RelationshipHelpers'));
        $this->assertCount(0, $this->repository->getColumns('Unusualify\Modularous\Repositories\Logic\MethodTransformers'));
        $this->assertCount(0, $this->repository->getColumns('Unusualify\Modularous\Repositories\Logic\QueryBuilder'));
        $this->assertCount(0, $this->repository->getColumns('Unusualify\Modularous\Repositories\Logic\CountBuilders'));
        $this->assertCount(0, $this->repository->getColumns('Unusualify\Modularous\Repositories\Logic\Dates'));
        $this->assertCount(0, $this->repository->getColumns('Unusualify\Modularous\Repositories\Logic\Relationships'));
        $this->assertCount(0, $this->repository->getColumns('Unusualify\Modularous\Repositories\Logic\DispatchEvents'));
    }

    public function test_repository_cleanup_fields()
    {
        $fields = [
            'name' => 'Test',
            'owner_id' => 1,
        ];

        $cleanedUpFields = $this->repository->cleanupFields(null, $fields);
        $object = new TestModel($cleanedUpFields);

        $this->assertEquals($object->name, $fields['name']);
        $this->assertEquals($object->owner_id, $fields['owner_id']);
        $this->assertEquals($object->is_active, false);
        $this->assertEquals($object->description, null);

        $fields = [
            'name' => 'Test',
            'owner_id' => 1,
            'is_active' => true,
            'description' => 'Test',
        ];

        $cleanedUpFields = $this->repository->cleanupFields(null, $fields);
        $object = new TestModel($cleanedUpFields);

        $this->assertEquals($object->name, $fields['name']);
        $this->assertEquals($object->owner_id, $fields['owner_id']);
        $this->assertEquals($object->is_active, true);
        $this->assertEquals($object->description, $fields['description']);
    }

    public function test_repository_trait_has_input()
    {
        // mock getColumns
        // $this->repository = $this->createMock(TestRepository::class);
        // $this->repository->method('getColumns')->willReturn(['is_active']);
        // $this->instance(
        //     TestRepository::class,
        //     Mockery::mock(TestRepository::class, function (\Mockery\MockInterface $mock) {
        //         $mock->shouldReceive('getColumns')->andReturn(['is_active']);
        //     })
        // );
        $this->repository = $this->partialMock(TestRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getColumns')->andReturn(['is_active']);
        });

        $this->assertEquals(true, $this->repository->traitHasInput('Unusualify\Modularous\Repositories\Logic\Relationships', 'is_active'));
        $this->assertEquals(false, $this->repository->traitHasInput('Unusualify\Modularous\Repositories\Logic\Relationships', 'description'));
    }

    public function test_repository_any_trait_has_input()
    {
        $this->repository = $this->partialMock(TestRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getColumns')->andReturn(['is_active']);
        });
        $this->assertEquals(true, $this->repository->anyTraitHasInput(['Unusualify\Modularous\Repositories\Logic\MethodTransformers', 'Unusualify\Modularous\Repositories\Logic\InspectTraits'], 'is_active'));
        $this->assertEquals(false, $this->repository->anyTraitHasInput(['Unusualify\Modularous\Repositories\Logic\MethodTransformers', 'Unusualify\Modularous\Repositories\Logic\InspectTraits'], 'description'));
    }

    public function test_filter_excludes_except_ids(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['exceptIds' => [1, 3]];

        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        $this->assertSame([2, 4, 5], $ids);
    }

    public function test_filter_where_in(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['id' => [1, 3, 5]];

        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        $this->assertSame([1, 3, 5], $ids);
    }

    public function test_filter_like_on_percent_column(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['%name' => 'Alice'];

        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        // Matches 'Alice' and 'Alice B'
        $this->assertSame([1, 2], $ids);
    }

    public function test_filter_not_like_on_percent_column(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['%name' => '!Alice'];

        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        // Excludes names LIKE '%Alice%'
        $this->assertSame([3, 4, 5], $ids);
    }

    public function test_filter_negation_equality(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['name' => '!Bob'];

        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        // All except exact 'Bob' (id=3)
        $this->assertSame([1, 2, 4, 5], $ids);
    }

    public function test_filter_simple_equals(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['is_active' => true];

        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        // Active: 1, 3, 4
        $this->assertSame([1, 3, 4], $ids);
    }

    public function test_filter_ignores_fields_listed_in_searches(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();

        // 'name' is listed in 'searches', so it should be removed from $scopes and not applied
        $scopes = [
            'searches' => ['name'],
            'name' => 'Alice',
        ];

        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        // No name filter applied, returns all
        $this->assertSame([1, 2, 3, 4, 5], $ids);
    }

    public function test_filter_add_relation_filter_scope(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['addRelationOwner' => [1]];

        $result = $this->repository->filter($query, $scopes)->pluck('id')->all();
        $this->assertSame([1, 2, 5], $result);
    }

    public function test_add_like_filter_scope(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['name' => 'Alice'];

        $this->repository->addLikeFilterScope($query, $scopes, 'name');

        // ensure scope entry is unset
        $this->assertArrayNotHasKey('name', $scopes);

        $ids = $query->pluck('id')->all();

        $this->assertSame([1, 2], $ids);
    }

    public function test_search_in_applies_or_like_over_fields(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['search' => 'hello'];

        $this->repository->searchIn($query, $scopes, 'search', ['name', 'description']);

        $ids = $query->pluck('id')->all();

        // description contains 'hello' on ids 1 and 3
        $this->assertSame([1, 3], $ids);
    }

    public function test_search_in_relationships_owner_name(): void
    {
        $this->seedFilterFixtures();

        $query = $this->repository->newQuery();
        $scopes = ['search' => 'Owner B'];

        $this->repository->searchInRelationships($query, $scopes, 'search', ['owner.name']);

        $ids = $query->pluck('id')->all();

        // Owner B owns models with ids 3 and 4
        $this->assertSame([3, 4], $ids);
    }

    public function test_add_relation_filter_scope_with_owner_id(): void
    {
        $this->seedFilterFixtures();

        $ownerBId = Owner::where('name', 'Owner B')->value('id');

        $query = $this->repository->newQuery();
        $scopes = ['id' => [$ownerBId]];

        // filter by related owner.id through whereHas('owner')
        $this->repository->addRelationFilterScope($query, $scopes, 'id', 'owner');

        $this->assertArrayNotHasKey('id', $scopes);

        $ids = $query->pluck('id')->all();

        $this->assertSame([3, 4], $ids);
    }

    public function test_cms_search(): void
    {
        $this->seedFilterFixtures();

        $results = $this->repository->cmsSearch('Ali', ['name']);

        // cmsSearch orders by latest(); fixtures created in the same second tie on created_at
        $this->assertEqualsCanonicalizing(['Alice', 'Alice B'], $results->pluck('name')->all());
    }

    public function test_repository_create()
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $schema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
            [
                'name' => 'owner_id',
                'type' => 'select',
            ],
        ];

        $object = $this->repository->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'created_at' => 'ss',
            'updated_at' => '',
        ], $schema);

        $this->assertEquals(false, $this->repository->dispatchEvent($object, 'null'));

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($owner->id, $object->owner_id);
        $this->assertEquals(null, $object->description);
        $this->assertEquals(false, $object->is_active);

        $laravelSchema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
        ];
        $laravelObject = $this->laravelRepository->create(['name' => 'Test'], $laravelSchema);

        $this->assertEquals('Test', $laravelObject->name);
        $this->assertEquals(null, $laravelObject->description);
    }

    public function test_repository_after_save_morph_many_relationships(): void
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $schema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
            [
                'name' => 'owner_id',
                'type' => 'select',
            ],
            [
                'type' => 'price',
                'name' => 'prices',
            ],
        ];

        CurrencyExchange::shouldReceive('convertTo')->with('100', 'USD')->andReturn(116);
        CurrencyExchange::shouldReceive('convertTo')->with('100', 'TRY')->andReturn(4900);
        $object = $this->repository->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'posts' => [
                [
                    'en' => [
                        'title' => 'Post 1',
                        'content' => 'Content of Post 1',
                    ],
                ],
                [
                    'en' => [
                        'title' => 'Post 2',
                        'content' => 'Content of Post 2',
                    ],
                ],
            ],
            'prices' => [
                [
                    'currency_id' => 1,
                    'price_value' => 100,
                    'vat_rate_id' => 1,
                    'price_type_id' => 1,
                ],
            ],
        ], $schema);
        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($owner->id, $object->owner_id);

        $this->assertEquals(2, $object->posts()->count());
        $this->assertEquals('Post 1', $object->posts()->first()->title);
        $this->assertEquals('Content of Post 1', $object->posts()->first()->content);
        $this->assertEquals('Post 2', $object->posts[1]->title);
        $this->assertEquals('Content of Post 2', $object->posts[1]->content);

        $this->repository->update($object->id, [
            'posts' => [
                [
                    'id' => $object->posts[0]->id,
                    'en' => [
                        'title' => 'Post 1 Updated',
                        'content' => 'Content of Post 1 Updated',
                    ],
                ],
            ],
        ]);

        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals(1, $object->posts->count());
        $this->assertEquals('Post 1 Updated', $object->posts[0]->title);
        $this->assertEquals('Content of Post 1 Updated', $object->posts[0]->content);

        // getFormFieldsRelationships for morphMany relationships
        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('inputs')->andReturn([
            'posts' => [
                'type' => 'repeater',
                'name' => 'posts',
                'draggable' => true,
                'connectedRelationship' => 'owner',
                'schema' => [
                    [
                        'name' => 'id',
                        'type' => 'hidden',
                    ],
                    [
                        'name' => 'title',
                        'type' => 'text',
                    ],
                ],
            ],
        ]);
        $formFields = $mock->getFormFields($object, []);

        $this->assertArrayHasKey('posts', $formFields);
        $this->assertCount(1, $formFields['posts']);
        $this->assertEquals($object->posts[0]->id, $formFields['posts'][0]['id']);
        $this->assertEquals($object->posts[0]->title, $formFields['posts'][0]['title']);
        $this->assertEquals($object->posts[0]->position, $formFields['posts'][0]['position']);
        $this->assertEquals($owner->id, $formFields['owner']->id);
    }

    public function test_repository_after_save_has_many_relationships(): void
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $schema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
            [
                'name' => 'owner_id',
                'type' => 'select',
            ],
        ];

        $object = $this->repository->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'notes' => [
                [
                    'content' => 'Note 1',
                ],
                [
                    'content' => 'Note 2',
                ],
            ],
        ], $schema);
        $object->refresh();

        $this->assertCount(0, $object->notes);

        $noteRepository = App::make(NoteRepository::class);

        ModularousFinder::shouldReceive('getRouteRepository')
            ->with('note', true)
            ->andReturn($noteRepository);

        $object = $this->repository->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'notes' => [
                [
                    'content' => 'Note 1',
                ],
                [
                    'content' => 'Note 2',
                ],
            ],
        ], $schema);
        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($owner->id, $object->owner_id);

        $this->assertEquals(2, $object->notes()->count());
        $this->assertEquals('Note 1', $object->notes()->first()->content);
        $this->assertEquals('Note 2', $object->notes[1]->content);

        $this->repository->update($object->id, [
            'notes' => [
                [
                    'id' => $object->notes[0]->id,
                    'content' => 'Note 1 Updated',
                ],
            ],
        ]);

        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals(1, $object->notes->count());
        $this->assertEquals('Note 1 Updated', $object->notes[0]->content);

        ModularousLog::shouldReceive('critical')
            ->with('Found numeric data in hasMany relationship on afterSaveRelationships', [
                'relationName' => 'notes',
                'data' => $object->notes[0]->id,
                'idsDeleted' => [$object->notes[0]->id],
                'repository' => TestRepository::class,
                'relationRepository' => NoteRepository::class,
            ])->once();

        $this->repository->update($object->id, [
            'notes' => [
                $object->notes[0]->id,
            ],
        ]);

        // snapshot test
        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('isSnapshotRelation')->withArgs([Note::class])->andReturn(true);
        $mock->shouldReceive('getSnapshotSourceForeignKey')->withArgs([Note::class])->andReturn('copy_note_id');

        $note = Note::create(['test_model_id' => $object->id, 'content' => 'Note 1']);

        $fields = $mock->prepareFieldsBeforeSaveRelationships($object, [
            'name' => 'Test',
            'owner_id' => $owner->id,
            'notes' => [
                $note->id,
            ],
        ]);

        $this->assertArrayHasKey('notes', $fields);
        $this->assertEquals([[
            'copy_note_id' => $note->id,
        ]], $fields['notes']);

        // getFormFieldsRelationships for hasMany relationships
        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $formFields = $mock->getFormFields($object, [
            'notes' => [
                'type' => 'select',
                'name' => 'notes',
            ],
        ]);

        $this->assertArrayHasKey('notes', $formFields);
        $this->assertCount(1, $formFields['notes']);
        $this->assertEquals($object->notes[0]->id, $formFields['notes'][0]->id);
    }

    public function test_repository_after_save_belongs_to_many_relationships(): void
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $schema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
        ];
        $testRole = TestRole::create(['name' => 'Test Role']);
        $object = $this->repository->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'testRoles' => [
                $testRole->id,
            ],
        ], $schema);

        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals(1, $object->testRoles()->count());
        $this->assertEquals($testRole->id, $object->testRoles()->first()->id);

        $testRole2 = TestRole::create(['name' => 'Second Test Role']);

        $testRoles = $object->testRoles;

        $testRoles = $testRoles->push([
            'test_model_id' => $object->id,
            'test_role_id' => $testRole2->id,
        ]);

        $this->repository->update($object->id, [
            'testRoles' => $testRoles,
        ]);

        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals(2, $object->testRoles()->count());
        $this->assertEquals($testRole->id, $object->testRoles()->first()->id);
        $this->assertEquals($testRole2->id, $object->testRoles[1]->id);

        // getFormFieldsRelationships for belongsToMany relationships
        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('inputs')->andReturn([
            'testRoles' => [
                'type' => 'select',
                'name' => 'testRoles',
            ],
        ]);
        $formFields = $mock->getFormFields($object, []);
        $this->assertArrayHasKey('testRoles', $formFields);
        $this->assertCount(2, $formFields['testRoles']);
        $this->assertEquals($testRole->id, $formFields['testRoles'][0]);
        $this->assertEquals($testRole2->id, $formFields['testRoles'][1]);

        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('inputs')->andReturn([
            'testRoles' => [
                'type' => 'repeater',
                'name' => 'testRoles',
                'orderable' => true,
                'schema' => [
                    [
                        'name' => 'test_role_id',
                        'type' => 'select',
                        'model' => TestRole::class,
                    ],
                ],
            ],
        ]);
        $formFields = $mock->getFormFields($object, []);
        $this->assertArrayHasKey('testRoles', $formFields);
        $this->assertCount(2, $formFields['testRoles']);
        $this->assertEquals($testRole->id, $formFields['testRoles'][0]['test_role_id']);
        $this->assertEquals($testRole2->id, $formFields['testRoles'][1]['test_role_id']);

        // error syncing belongsToMany relationships
        $errorData = [
            [
                'deneme' => 'dee',
            ],
        ];
        ModularousLog::shouldReceive('critical')
            ->with('Error syncing belongsToMany relationship on afterSaveRelationships', [
                'repository' => TestRepository::class,
                'relationName' => 'testRoles',
                'error' => 'Undefined array key "test_role_id"',
                'data' => $errorData,
            ])
            ->once();

        $this->repository->update($object->id, [
            'testRoles' => $errorData,
        ]);

        $this->repository->update($object->id, [
            'testRoles' => null,
        ]);
        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals(0, $object->testRoles()->count());
    }

    public function test_repository_after_save_morph_to_relationships(): void
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $schema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
            [
                'type' => 'morphTo',
                'schema' => [
                    [
                        'name' => 'first_morph_id',
                        'type' => 'select',
                        'model' => FirstMorph::class,
                    ],
                    [
                        'name' => 'second_morph_id',
                        'type' => 'select',
                        'model' => SecondMorph::class,
                    ],
                ],
            ],
        ];

        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('getRawChunkedInputs')->withArgs([false, false])->once()->andReturn($schema);

        $firstMorph = FirstMorph::create(['name' => 'First Morph']);
        $secondMorph = SecondMorph::create(['name' => 'Second Morph']);

        $object = $mock->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'first_morph_id' => $firstMorph->id,
        ], $schema);
        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($object->testModelable()->exists(), true);
        $this->assertEquals($object->testModelable->id, $firstMorph->id);

        $this->repository->update($object->id, [
            'second_morph_id' => $secondMorph->id,
        ]);
        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($object->testModelable()->exists(), true);
        $this->assertEquals($object->testModelable->id, $secondMorph->id);

        // getFormFieldsRelationships for morphTo relationships
        $mock->shouldReceive('getRawChunkedInputs')->withArgs([false, false])->once()->andReturn([
            [
                'type' => 'morphTo',
                'schema' => [
                    [
                        'name' => 'first_morph_id',
                        'type' => 'select',
                        'model' => FirstMorph::class,
                    ],
                    [
                        'name' => 'second_morph_id',
                        'type' => 'select',
                        'model' => SecondMorph::class,
                    ],
                    [
                        'name' => 'test_role_id',
                        'type' => 'select',
                        'model' => TestRole::class,
                    ],
                ],
            ],
        ]);
        $object->test_role_id = 1;
        $formFields = $mock->getFormFields($object, $schema);
        $this->assertArrayHasKey('first_morph_id', $formFields);
        $this->assertArrayHasKey('second_morph_id', $formFields);
        $this->assertArrayHasKey('test_role_id', $formFields);
        $this->assertEquals($firstMorph->id, $formFields['first_morph_id']);
        $this->assertEquals(null, $formFields['second_morph_id']);
        $this->assertEquals(1, $formFields['test_role_id']);
        $object->offsetUnset('test_role_id');

        // with repository class
        $mock->shouldReceive('getRawChunkedInputs')->withArgs([false, false])->once()->andReturn([
            [
                'type' => 'morphTo',
                'schema' => [
                    [
                        'name' => 'second_morph_id',
                        'type' => 'select',
                        'repository' => 'Unusualify\Modularous\Tests\Repositories\NoteRepository',
                    ],
                ],
            ],
        ]);
        $this->assertEquals([
            'testModelable' => [
                [
                    'name' => 'second_morph_id',
                    'model' => 'Unusualify\Modularous\Tests\Repositories\Note',
                ],
            ],
        ], $mock->getMorphToRelations());

        // with connector
        $mock->shouldReceive('getRawChunkedInputs')->withArgs([false, false])->once()->andReturn([
            [
                'type' => 'morphTo',
                'schema' => [
                    [
                        'name' => 'second_morph_id',
                        'type' => 'select',
                        'connector' => 'connectorDefinition',
                    ],
                ],
            ],
        ]);
        $mock->shouldReceive('findConnectorRepository')->withArgs(['connectorDefinition'])->once()
            ->andReturn(App::make(NoteRepository::class, [new Note]));
        $this->assertEquals([
            'testModelable' => [
                [
                    'name' => 'second_morph_id',
                    'model' => 'Unusualify\Modularous\Tests\Repositories\Note',
                ],
            ],
        ], $mock->getMorphToRelations());

        // with new connector
        $mock->shouldReceive('getRawChunkedInputs')->withArgs([false, false])->once()->andReturn([
            [
                'type' => 'morphTo',
                'schema' => [
                    [
                        'name' => 'second_morph_id',
                        'type' => 'select',
                        'newConnector' => 'connectorDefinition',
                    ],
                ],
            ],
        ]);
        $mock->shouldReceive('findNewConnectorRepository')->withArgs(['connectorDefinition'])->once()
            ->andReturn(App::make(NoteRepository::class, [new Note]));
        $this->assertEquals([
            'testModelable' => [
                [
                    'name' => 'second_morph_id',
                    'model' => 'Unusualify\Modularous\Tests\Repositories\Note',
                ],
            ],
        ], $mock->getMorphToRelations());
    }

    public function test_repository_morph_to_exception_model_not_found(): void
    {
        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Model not found on morphTo input: NotExistingModel on second_morph_id');
        $mock->shouldReceive('getRawChunkedInputs')->withArgs([false, false])->once()->andReturn([
            [
                'type' => 'morphTo',
                'schema' => [
                    [
                        'name' => 'second_morph_id',
                        'type' => 'select',
                        'model' => 'NotExistingModel',
                    ],
                ],
            ],
        ]);
        $mock->getMorphToRelations();
    }

    public function test_repository_morph_to_exception_repository_not_found(): void
    {
        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Repository not found on morphTo input: second_morph_id');
        $mock->shouldReceive('getRawChunkedInputs')->withArgs([false, false])->once()->andReturn([
            [
                'type' => 'morphTo',
                'schema' => [
                    [
                        'name' => 'second_morph_id',
                        'type' => 'select',
                        'repository' => 'NotExistingRepository',
                    ],
                ],
            ],
        ]);
        $mock->getMorphToRelations();
    }

    public function test_repository_morph_to_exception_no_repository_or_connector(): void
    {
        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Repository or connector not found on morphTo input: second_morph_id');
        $mock->shouldReceive('getRawChunkedInputs')->withArgs([false, false])->once()->andReturn([
            [
                'type' => 'morphTo',
                'schema' => [
                    [
                        'name' => 'second_morph_id',
                        'type' => 'select',
                    ],
                ],
            ],
        ]);
        $mock->getMorphToRelations();
    }

    public function test_repository_after_save_morph_to_many_relationships(): void
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $schema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
        ];
        $image = Media::create([
            'uuid' => 'uploads/folder/img-5.jpg',
            'filename' => 'img-5.jpg',
            'alt_text' => 'img-5.jpg',
            'caption' => 'img-5.jpg',
            'width' => 120,
            'height' => 80,
        ]);

        $object = $this->repository->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'medias' => [[
                'media_id' => $image->id,
                'crop' => 'default',
                'locale' => 'en',
                'role' => 'logo',
                'metadatas' => json_encode(['default' => ['caption' => null, 'altText' => null, 'video' => null]]),
            ]],
        ], $schema);

        $object->refresh();

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($object->medias()->exists(), true);
        $this->assertEquals(1, $object->medias()->count());
        $this->assertEquals($object->medias()->first()->id, $image->id);

        // getFormFieldsRelationships for morphToMany relationships
        $mock = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('inputs')->andReturn([
            'medias' => [
                'type' => 'image',
                'name' => 'medias',
            ],
        ]);
        $formFields = $mock->getFormFields($object, $schema);

        $this->assertArrayHasKey('medias', $formFields);
        $this->assertEquals([$image->id], $formFields['medias']);
    }

    public function test_repository_get_form_fields_relationships(): void
    {
        $owner = Owner::create(['name' => 'Test Owner']);

        $noteRepository = App::make(NoteRepository::class);

        ModularousFinder::shouldReceive('getRouteRepository')
            ->with('note', true)
            ->andReturn($noteRepository);

        $object = $this->repository->create(['name' => 'Test', 'owner_id' => $owner->id, 'notes' => [
            [
                'content' => 'Test Note',
            ],
        ]]);

        ModularousFinder::shouldReceive('getRouteRepository')
            ->withArgs(['Note', true])
            ->once()
            ->andReturn(App::make(NoteRepository::class, [new Note]));

        $formFields = $this->repository->getFormFields($object, [
            'notes' => [
                'type' => 'select',
                'ext' => 'relationship',
                'name' => 'notes',
                'relationship' => 'notes',
                'isSerialized' => true,
                'schema' => [
                    [
                        'name' => 'id',
                        'type' => 'hidden',
                    ],
                    [
                        'name' => 'test_model_id',
                        'type' => 'hidden',
                    ],
                    [
                        'name' => 'content',
                        'type' => 'text',
                    ],
                ],
            ],
        ]);
        $this->assertArrayHasKey('notes', $formFields);
        $this->assertCount(1, $formFields['notes']);
        $this->assertEquals($object->notes[0]->id, $formFields['notes'][0]['id']);
        $this->assertEquals($object->notes[0]->test_model_id, $formFields['notes'][0]['test_model_id']);
        $this->assertEquals($object->notes[0]->content, $formFields['notes'][0]['content']);
    }

    public function test_repository_first_or_create()
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $schema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
        ];
        $object = $this->repository->firstOrCreate(['name' => 'Test', 'owner_id' => $owner->id], [], $schema);

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($owner->id, $object->owner_id);

        $object = $this->repository->firstOrCreate(['name' => 'Test', 'owner_id' => $owner->id], [], $schema);

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($owner->id, $object->owner_id);
        $this->assertEquals(1, $object->id);
    }

    public function test_repository_create_for_preview()
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $object = $this->repository->createForPreview(['name' => 'Test', 'owner_id' => $owner->id]);

        $this->assertEquals('Test', $object->name);
        $this->assertEquals($owner->id, $object->owner_id);
        $this->assertEquals(null, $object->description);
        $this->assertEquals(false, $object->is_active);
    }

    public function test_create_for_preview_hydrates_without_persisting(): void
    {
        $owner = Owner::create(['name' => 'Own']);

        $preview = $this->repository->createForPreview([
            'name' => 'Preview Name',
            'owner_id' => $owner->id,
            'is_active' => true,
            'description' => 'Desc',
        ]);

        $this->assertSame('Preview Name', $preview->name);
        $this->assertSame($owner->id, $preview->owner_id);
        $this->assertSame(true, $preview->is_active);
        $this->assertSame('Desc', $preview->description);

        $this->assertSame(0, TestModel::count());
    }

    public function test_update_or_create_creates_then_updates(): void
    {
        $owner = Owner::create(['name' => 'Own']);

        // create branch
        $created = $this->repository->updateOrCreate(
            ['name' => 'UOC'],
            ['name' => 'UOC', 'owner_id' => $owner->id, 'is_active' => true]
        );

        $this->assertSame('UOC', TestModel::first()->name);

        // update branch
        $this->repository->updateOrCreate(
            ['name' => 'UOC'],
            ['name' => 'UOC2', 'owner_id' => $owner->id, 'is_active' => false]
        );

        $this->assertSame('UOC2', TestModel::first()->name);
    }

    public function test_update_updates_existing_record(): void
    {
        $owner = Owner::create(['name' => 'Own']);
        $m = TestModel::create(['name' => 'Old', 'owner_id' => $owner->id, 'is_active' => false]);

        $this->repository->update($m->id, ['name' => 'New']);

        $this->assertSame('New', $m->fresh()->name);
    }

    public function test_update_basic_with_null_id_applies_scopes(): void
    {
        $this->seedFilterFixtures();

        $updated = $this->repository->updateBasic(null, ['description' => 'zz'], ['is_active' => false]);

        $this->assertTrue($updated);
        $this->assertSame(['zz', 'zz'], TestModel::where('is_active', false)->pluck('description')->all());
    }

    public function test_update_basic_with_array_of_ids(): void
    {
        $this->seedFilterFixtures();

        $ids = TestModel::where('is_active', true)->pluck('id')->all();
        $updated = $this->repository->updateBasic($ids, ['description' => 'yay']);

        $this->assertTrue($updated);
        $this->assertSame(['yay', 'yay', 'yay'], TestModel::where('is_active', true)->pluck('description')->all());
    }

    public function test_update_basic_with_single_id(): void
    {
        $this->seedFilterFixtures();

        $id = TestModel::where('name', 'Bob')->value('id');
        $updated = $this->repository->updateBasic($id, ['description' => 'only']);

        $this->assertTrue($updated);
        $this->assertSame('only', TestModel::find($id)->description);
    }

    public function test_delete_soft_deletes(): void
    {
        $this->seedFilterFixtures();
        $id = TestModel::where('name', 'Bob')->value('id');

        $this->assertTrue($this->repository->delete($id));
        $this->assertSoftDeleted('test_models', ['id' => $id]);
    }

    public function test_bulk_delete_soft_deletes_multiple(): void
    {
        $this->seedFilterFixtures();
        $ids = TestModel::whereIn('name', ['Alice', 'Bob'])->pluck('id')->all();

        $this->assertTrue($this->repository->bulkDelete($ids));
        foreach ($ids as $id) {
            $this->assertSoftDeleted('test_models', ['id' => $id]);
        }
    }

    public function test_force_delete_permanently_deletes(): void
    {
        $this->seedFilterFixtures();
        $id = TestModel::where('name', 'Bob')->value('id');
        TestModel::find($id)->delete();

        $this->assertTrue($this->repository->forceDelete($id));
        $this->assertNull(TestModel::withTrashed()->find($id));
    }

    public function test_bulk_force_delete_permanently_deletes_multiple(): void
    {
        $this->seedFilterFixtures();
        $ids = TestModel::whereIn('name', ['Alice', 'Bob'])->pluck('id')->all();
        TestModel::whereIn('id', $ids)->delete();

        $this->assertTrue($this->repository->bulkForceDelete($ids));
        $this->assertSame(0, TestModel::withTrashed()->whereIn('id', $ids)->count());
    }

    public function test_restore_soft_deleted(): void
    {
        $this->seedFilterFixtures();
        $id = TestModel::where('name', 'Bob')->value('id');
        TestModel::find($id)->delete();

        $this->assertTrue($this->repository->restore($id));
        $this->assertFalse((bool) TestModel::find($id)->trashed());
    }

    public function test_bulk_restore_soft_deleted_multiple(): void
    {
        $this->seedFilterFixtures();
        $ids = TestModel::whereIn('name', ['Alice', 'Bob'])->pluck('id')->all();
        TestModel::whereIn('id', $ids)->delete();

        $this->assertTrue($this->repository->bulkRestore($ids));
        $this->assertSame(0, TestModel::onlyTrashed()->whereIn('id', $ids)->count());
    }

    public function test_set_new_order_calls_model_method(): void
    {
        $this->seedFilterFixtures();

        $this->assertEquals(1, $this->repository->setNewOrder([3, 1, 5, 2, 4])); // reached without errors

        // dd($this->repository->setNewOrder([3, 1, 5, 2, 4]));
        $objects = $this->repository->get(scopes: ['ordered' => true]);
        $this->assertEquals(3, $objects->first()->id);
        $this->assertEquals(4, $objects->last()->id);
    }

    public function test_duplicate_creates_new_record_from_existing(): void
    {
        $owner = Owner::create(['name' => 'Owner D']);
        $original = TestModel::create(['name' => 'Orig', 'owner_id' => $owner->id, 'is_active' => true, 'description' => 'x']);

        $schema = [
            ['name' => 'name', 'type' => 'text'],
            ['name' => 'owner_id', 'type' => 'select'],
            ['name' => 'is_active', 'type' => 'checkbox'],
            ['name' => 'description', 'type' => 'text'],
        ];

        $duplicated = $this->repository->duplicate($original->id, 'name', $schema);

        $this->assertNotFalse($duplicated);
        $this->assertNotEquals($original->id, $duplicated->id);
        $this->assertSame('Orig', $duplicated->name);
        $this->assertSame($owner->id, $duplicated->owner_id);
        $this->assertSame(true, (bool) $duplicated->is_active);
        $this->assertSame('x', $duplicated->description);
    }

    public function test_ignore_fields_before_save_flags_and_checks(): void
    {
        $this->repository->addIgnoreFieldsBeforeSave('description');
        $this->repository->addIgnoreFieldsBeforeSave(['is_active']);

        $this->assertTrue($this->repository->shouldIgnoreFieldBeforeSave('description'));
        $this->assertTrue($this->repository->shouldIgnoreFieldBeforeSave('is_active'));
        $this->assertFalse($this->repository->shouldIgnoreFieldBeforeSave('name'));
    }

    public function test_update_multi_select_syncs_pivot(): void
    {
        $owner = Owner::create(['name' => 'Own']);
        $m = TestModel::create(['name' => 'HasRoles', 'owner_id' => $owner->id, 'is_active' => true]);
        $r1 = TestRole::create(['name' => 'R1']);
        $r2 = TestRole::create(['name' => 'R2']);

        $fields = ['testRoles' => [$r1->id, $r2->id]];
        $this->repository->updateMultiSelect($m, $fields, 'testRoles');

        $this->assertSame([$r1->id, $r2->id], $m->testRoles()->pluck('test_roles.id')->sort()->values()->all());
    }

    public function test_update_one_to_many_creates_and_cleans(): void
    {
        $owner = Owner::create(['name' => 'Own']);
        $m = TestModel::create(['name' => 'HasNotes', 'owner_id' => $owner->id, 'is_active' => true]);

        // create 10,20
        $fields = ['note_ids' => [10, 20]];
        $this->repository->updateOneToMany($m, $fields, 'notes', 'note_ids', 'external_id');

        $this->assertSame([10, 20], $m->notes()->pluck('external_id')->sort()->values()->all());

        // update to 20,30 (remove 10, add 30)
        $fields = ['note_ids' => [20, 30]];
        $this->repository->updateOneToMany($m, $fields, 'notes', 'note_ids', 'external_id');

        $this->assertSame([20, 30], $m->notes()->pluck('external_id')->sort()->values()->all());
    }

    public function test_get_count_by_status_slug(): void
    {
        // Ensure published column exists for scopes
        Schema::table('test_models', function (Blueprint $table) {
            if (! Schema::hasColumn('test_models', 'published')) {
                $table->boolean('published')->default(false);
            }
        });

        $ownerA = Owner::create(['name' => 'Owner A']);
        $ownerB = Owner::create(['name' => 'Owner B']);

        // Create records with varying published status
        (new TestModel)->forceFill([
            'name' => 'P1',
            'owner_id' => $ownerA->id,
            'is_active' => true,
            'description' => null,
            'published' => true,
        ])->save();

        (new TestModel)->forceFill([
            'name' => 'P2',
            'owner_id' => $ownerB->id,
            'is_active' => true,
            'description' => null,
            'published' => true,
        ])->save();

        (new TestModel)->forceFill([
            'name' => 'D1',
            'owner_id' => $ownerA->id,
            'is_active' => false,
            'description' => null,
            'published' => false,
        ])->save();

        (new TestModel)->forceFill([
            'name' => 'D2',
            'owner_id' => $ownerB->id,
            'is_active' => false,
            'description' => null,
            'published' => false,
        ])->save();

        $trashed = (new TestModel)->forceFill([
            'name' => 'P3-TRASH',
            'owner_id' => $ownerA->id,
            'is_active' => true,
            'description' => null,
            'published' => true,
        ]);
        $trashed->save();
        $trashed->delete();

        // Repository counts without scope
        $this->assertSame(4, $this->repository->getCountByStatusSlug('all'));
        $this->assertSame(2, $this->repository->getCountByStatusSlug('published'));
        $this->assertSame(2, $this->repository->getCountByStatusSlug('draft'));
        $this->assertSame(1, $this->repository->getCountByStatusSlug('trash'));

        // Repository counts scoped to Owner B
        $this->assertSame(2, $this->repository->getCountByStatusSlug('all', ['owner_id' => $ownerB->id]));
        $this->assertSame(1, $this->repository->getCountByStatusSlug('published', ['owner_id' => $ownerB->id]));
        $this->assertSame(1, $this->repository->getCountByStatusSlug('draft', ['owner_id' => $ownerB->id]));
        $this->assertSame(0, $this->repository->getCountByStatusSlug('trash', ['owner_id' => $ownerB->id]));
        $this->assertSame(0, $this->repository->getCountByStatusSlug('_deneme', ['owner_id' => $ownerB->id]));
    }

    public function test_get_show_fields(): void
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        $schema = [
            [
                'name' => 'name',
                'type' => 'text',
            ],
            [
                'name' => 'owner_id',
                'type' => 'select',
            ],
        ];

        $object = $this->repository->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'created_at' => 'ss',
            'updated_at' => '',
        ], $schema);

        $fields = $this->repository->getShowFields($object, $schema);

        $this->assertEquals('Test', $fields['name']);
        $this->assertEquals($owner->id, $fields['owner_id']);
        $this->assertEquals(null, $fields['description']);
        $this->assertEquals(1, $fields['position']);
        $this->assertEquals(false, $fields['is_active']);
    }

    public function test_defined_relations(): void
    {
        $this->assertContains('owner', $this->repository->getDefinedRelations(['BelongsTo']));
        $this->assertContains('owner', $this->repository->getDefinedRelations('BelongsTo'));
        $this->assertContains('testRoles', $this->repository->getDefinedRelations(['BelongsToMany']));
        $this->assertContains('testRoles', $this->repository->getDefinedRelations('BelongsToMany'));
        $this->assertContains('notes', $this->repository->getDefinedRelations(['HasMany']));
        $this->assertContains('notes', $this->repository->getDefinedRelations('HasMany'));
        $this->assertContains('posts', $this->repository->getDefinedRelations(['MorphMany']));
        $this->assertContains('posts', $this->repository->getDefinedRelations('MorphMany'));

        // multiple relations
        $this->assertContains('owner', $this->repository->getDefinedRelations(['BelongsTo', 'BelongsToMany', 'HasMany', 'MorphMany']));
        $this->assertContains('testRoles', $this->repository->getDefinedRelations(['BelongsTo', 'BelongsToMany', 'HasMany', 'MorphMany']));
        $this->assertContains('notes', $this->repository->getDefinedRelations(['BelongsTo', 'BelongsToMany', 'HasMany', 'MorphMany']));
        $this->assertContains('posts', $this->repository->getDefinedRelations(['BelongsTo', 'BelongsToMany', 'HasMany', 'MorphMany']));

        $this->assertContains('owner', $this->repository->getDefinedRelations(['BelongsTo', 'BelongsToMany']));
        $this->assertContains('testRoles', $this->repository->getDefinedRelations(['BelongsTo', 'BelongsToMany']));
        $this->assertContains('notes', $this->repository->getDefinedRelations(['HasMany', 'MorphMany']));
        $this->assertContains('posts', $this->repository->getDefinedRelations(['HasMany', 'MorphMany']));
    }

    public function test_foreign_key_methods(): void
    {
        $model = $this->repository->getModel();

        $this->assertEquals('owner_id', $this->repository->getRelationForeignKey($model->owner()));
        $this->assertEquals('test_role_id', $this->repository->getRelationForeignKey($model->testRoles()));
        $this->assertEquals('test_model_id', $this->repository->getRelationForeignKey($model->notes()));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid relation type');
        $this->repository->getRelationForeignKey($model->posts());
    }

    public function test_get_count_for(): void
    {
        // Create a partial mock (spy) of the repository
        $repository = Mockery::mock(TestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Mock the filter method to return the query directly (passthrough)
        $repository->shouldReceive('filter')
            ->andReturnUsing(function ($query, $scopes = []) {
                return $query;
            });

        // Test getCountFor with scopes that exist on the model
        $this->assertSame(0, $repository->getCountFor('createdAtBetween', [now()->subDay(), now()->addDay()]));

        // Test that exception is thrown for non-existent scope
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Method scopeNonExistent does not exist');
        $repository->getCountFor('nonExistent');
    }
}
