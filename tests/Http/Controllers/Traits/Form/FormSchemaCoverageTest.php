<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route as RouteFacade;
use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\Form\FormSchema;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\ManageNames;

class FormSchemaCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeController(array $routeInputs = [], $repository = null, $module = true): object
    {
        $request = Request::create('/admin/items/create', 'GET');
        $this->app->instance('request', $request);

        Config::set('modularous.default_input', [
            'type' => 'text',
            'color' => '',
            'col' => ['cols' => 12],
        ]);
        Config::set('modularous.input_types', [
            'email' => ['type' => 'text', 'inputType' => 'email'],
        ]);

        return new class($request, $routeInputs, $repository, $module)
        {
            use FormSchema;
            use ManageNames;

            public $request;

            public $module;

            public $repository;

            public $moduleName = 'Package';

            public $routeName = 'Item';

            public $config;

            public array $routeInputs;

            public function __construct($request, array $routeInputs, $repository, $module)
            {
                $this->request = $request;
                $this->routeInputs = $routeInputs;
                $this->repository = $repository;
                $this->module = $module;
                $this->config = (object) ['routes' => []];
                $this->__beforeConstructFormSchema(app(), $request);
            }

            public function getConfigFieldsByRoute($key, $default = null)
            {
                return $key === 'inputs' ? $this->routeInputs : $default;
            }

            public function setFormSchemaForTest(array $schema): void
            {
                $this->formSchema = $schema;
            }

            public function getFormSchemaForTest()
            {
                return $this->formSchema;
            }

            public function getRouteName()
            {
                return $this->routeName;
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };
    }

    /** @test */
    public function it_builds_empty_and_simple_text_schemas(): void
    {
        $controller = $this->makeController();

        $this->assertSame([], $controller->createFormSchema([]));

        $schema = $controller->createFormSchema([
            ['type' => 'text', 'name' => 'title', 'label' => 'Title'],
            ['type' => 'email', 'name' => 'email'],
        ]);

        $this->assertArrayHasKey('title', $schema);
        $this->assertArrayHasKey('email', $schema);
        $this->assertSame('text', $schema['title']['type']);
        $this->assertSame('email', $schema['email']['inputType'] ?? null);
    }

    /** @test */
    public function it_covers_title_divider_wrap_and_group_branches(): void
    {
        $controller = $this->makeController();

        $schema = $controller->createFormSchema([
            ['type' => 'title', 'label' => 'Section'],
            ['type' => 'divider'],
            [
                'type' => 'wrap',
                'noLabel' => true,
                'rules' => 'required',
                'schema' => [
                    ['type' => 'text', 'name' => 'inner', 'default' => 'x'],
                ],
            ],
            [
                'type' => 'group',
                'name' => 'meta',
                'label' => 'Meta',
                'schema' => [
                    ['type' => 'text', 'name' => 'slug', 'default' => 's'],
                    [
                        'type' => 'wrap',
                        'name' => 'nested_wrap',
                        'schema' => [
                            ['type' => 'text', 'name' => 'nested', 'default' => 'n'],
                        ],
                    ],
                ],
            ],
        ]);

        $titleKeys = array_values(array_filter(array_keys($schema), fn ($k) => str_starts_with((string) $k, 'title_')));
        $dividerKeys = array_values(array_filter(array_keys($schema), fn ($k) => str_starts_with((string) $k, 'divider_')));
        $this->assertNotEmpty($titleKeys);
        $this->assertNotEmpty($dividerKeys);

        $wrap = collect($schema)->first(fn ($input) => ($input['type'] ?? null) === 'wrap');
        $this->assertNotNull($wrap);
        $this->assertStringContainsString('required', (string) ($wrap['class'] ?? ''));
        $this->assertArrayHasKey('inner', $wrap['schema']);

        $this->assertArrayHasKey('meta', $schema);
        $this->assertSame('s', $schema['meta']['default']['slug'] ?? null);
        $this->assertArrayHasKey('parentName', $schema['meta']['schema']['slug']);
    }

    /** @test */
    public function it_runs_setup_callbacks_and_repository_schema_hooks(): void
    {
        $repository = new class
        {
            public function appendFormSchema(): array
            {
                return [
                    ['type' => 'text', 'name' => 'appended'],
                ];
            }

            public function prependFormSchema(array $inputs): array
            {
                return [
                    ['type' => 'text', 'name' => 'prepended'],
                ];
            }

            public function getModel()
            {
                return new class
                {
                    public function isTranslationAttribute($name): bool
                    {
                        return $name === 'title';
                    }
                };
            }

            public function isTranslationAttribute($name): bool
            {
                return $name === 'title';
            }
        };

        $controller = $this->makeController([
            ['type' => 'text', 'name' => 'title'],
        ], $repository);

        $class = $controller::class;
        $class::updateFormSchema(function (array $inputs) {
            $inputs[] = ['type' => 'text', 'name' => 'from_callback'];

            return $inputs;
        });

        $controller->setupFormSchema();
        $schema = $controller->getFormSchemaForTest();

        $this->assertArrayHasKey('title', $schema);
        $this->assertArrayHasKey('appended', $schema);
        $this->assertArrayHasKey('prepended', $schema);
        $this->assertArrayHasKey('from_callback', $schema);
        $this->assertTrue($schema['title']['translated'] ?? false);

        $class::updateFormSchema(fn ($inputs) => $inputs);
    }

    /** @test */
    public function it_hydrates_ext_patterns_and_filters_by_roles(): void
    {
        $controller = $this->makeController();

        $user = Mockery::mock();
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($roles) => in_array('admin', (array) $roles, true));
        $controller->allowableUser = $user;

        $withDate = $controller->createFormSchema([
            ['type' => 'text', 'name' => 'published_at', 'ext' => 'date'],
            ['type' => 'text', 'name' => 'starts_at', 'ext' => 'time'],
            ['type' => 'text', 'name' => 'status', 'ext' => 'lock:url:url|preview:fields|clearModel:status|resetItems:status|removeValue:status|toggleInput:status:1:-1|set:label:name:items.*.name|update:label:name'],
        ]);

        $this->assertSame(date('Y-m-d'), $withDate['published_at']['default']);
        $this->assertSame(date('H:i'), $withDate['starts_at']['default']);
        $this->assertStringContainsString('formatLock', (string) ($withDate['status']['event'] ?? ''));
        $this->assertStringContainsString('formatPreview', (string) ($withDate['status']['event'] ?? ''));
        $this->assertStringContainsString('formatClearModel', (string) ($withDate['status']['event'] ?? ''));
        $this->assertStringContainsString('formatToggleInput', (string) ($withDate['status']['event'] ?? ''));

        $filtered = $controller->filterSchemaByRoles([
            'title' => ['type' => 'text', 'name' => 'title'],
            'secret' => ['type' => 'text', 'name' => 'secret', 'allowedRoles' => ['super']],
            'visible' => ['type' => 'text', 'name' => 'visible', 'allowedRoles' => ['admin']],
            'readonly' => ['type' => 'text', 'name' => 'readonly', 'allowedRoles' => ['super'], 'viewOnlyComponent' => 'v-text'],
            'group' => [
                'type' => 'group',
                'name' => 'group',
                'schema' => [
                    'inner' => ['type' => 'text', 'name' => 'inner'],
                ],
            ],
        ]);

        $this->assertArrayHasKey('title', $filtered);
        $this->assertArrayHasKey('visible', $filtered);
        $this->assertArrayHasKey('readonly', $filtered);
        $this->assertArrayNotHasKey('secret', $filtered);
        $this->assertArrayHasKey('group', $filtered);
        $this->assertArrayNotHasKey('viewOnlyComponent', $filtered['visible'] ?? []);
    }

    /** @test */
    public function it_collects_form_withs_and_appends_from_schema(): void
    {
        $controller = $this->makeController();
        $controller->setFormSchemaForTest([
            'a' => ['name' => 'title', 'with' => 'creator,company', 'appends' => 'status'],
            'b' => ['name' => 'body', 'with' => ['tags'], 'appends' => ['author']],
        ]);

        $this->assertSame(['creator', 'company', 'tags'], $controller->addFormWithsFormSchema());
        $appends = $controller->addFormAppendsFormSchema();
        $this->assertContains('status', $appends);
        $this->assertContains('author', $appends);
        $this->assertContains('title', $appends);
        $this->assertContains('body', $appends);
    }

    /** @test */
    public function it_covers_relationship_missing_name_and_connector_spread_short_circuit(): void
    {
        $controller = $this->makeController();

        $this->assertSame([], $controller->createFormSchema([
            ['type' => 'relationship'],
        ]));

        $module = Mockery::mock(\Nwidart\Modules\Module::class);
        Modularous::shouldReceive('find')->with('SystemSetting')->andReturn($module);

        $input = [
            'type' => 'spread',
            'name' => 'settings',
            'connector' => 'SystemSetting:General',
        ];

        $controller->hydrateInputConnector($input);
        $this->assertSame('SystemSetting', $input['_moduleName']);
        $this->assertSame('General', $input['_routeName']);
        $this->assertArrayNotHasKey('endpoint', $input);
    }

    /** @test */
    public function it_covers_textarea_number_and_checkbox_passthrough(): void
    {
        $controller = $this->makeController([], null, null);

        $schema = $controller->createFormSchema([
            ['type' => 'textarea', 'name' => 'bio', 'label' => 'Bio'],
            ['type' => 'number', 'name' => 'qty', 'default' => 3],
            ['type' => 'checkbox', 'name' => 'active', 'default' => true],
            ['type' => 'hidden', 'name' => 'token', 'default' => 'abc'],
        ]);

        $this->assertArrayHasKey('bio', $schema);
        $this->assertSame(3, $schema['qty']['default'] ?? null);
        $this->assertArrayHasKey('active', $schema);
        $this->assertSame('abc', $schema['token']['default'] ?? null);
    }

    /** @test */
    public function it_covers_additional_input_types_and_title_defaults(): void
    {
        RouteFacade::post('/filepond/process', static fn () => 'ok')->name('filepond.process');
        RouteFacade::delete('/filepond/revert', static fn () => 'ok')->name('filepond.revert');
        RouteFacade::get('/filepond/preview/{uuid}', static fn () => 'ok')->name('filepond.preview');
        RouteFacade::getRoutes()->refreshNameLookups();

        $controller = $this->makeController([], null, null);

        $schema = $controller->createFormSchema([
            ['type' => 'select', 'name' => 'status', 'items' => [['id' => 1, 'name' => 'A']], 'default' => 1],
            ['type' => 'radio', 'name' => 'choice', 'items' => [['id' => 'x', 'name' => 'X']]],
            ['type' => 'switch', 'name' => 'enabled', 'default' => false],
            ['type' => 'date', 'name' => 'starts_on'],
            ['type' => 'file', 'name' => 'doc'],
            ['type' => 'image', 'name' => 'cover'],
            ['type' => 'filepond', 'name' => 'uploads', 'acceptedExtensions' => ['pdf']],
            ['type' => 'slug', 'name' => 'slug', 'titleFrom' => 'title'],
            ['type' => 'json', 'name' => 'payload'],
            ['type' => 'checklist-group', 'name' => 'perms', 'items' => []],
            [
                'type' => 'title',
                'name' => 'heading',
                'label' => 'Heading',
            ],
            [
                'type' => 'wrap',
                'name' => 'wrap-plain',
                'label' => 'Wrapped',
                'schema' => [
                    ['type' => 'text', 'name' => 'inner2', 'default' => 'y'],
                ],
            ],
        ]);

        $this->assertArrayHasKey('status', $schema);
        $this->assertArrayHasKey('choice', $schema);
        $this->assertArrayHasKey('enabled', $schema);
        $this->assertArrayHasKey('starts_on', $schema);
        $this->assertArrayHasKey('doc', $schema);
        $this->assertArrayHasKey('cover', $schema);
        $this->assertArrayHasKey('uploads', $schema);
        $this->assertArrayHasKey('slug', $schema);
        $this->assertArrayHasKey('payload', $schema);
        $titleKeys = array_values(array_filter(array_keys($schema), fn ($k) => str_starts_with((string) $k, 'title_')));
        $this->assertNotEmpty($titleKeys);
        $this->assertSame('bold', $schema[$titleKeys[0]]['weight'] ?? null);
        $this->assertArrayHasKey('wrap-plain', $schema);
    }

    /** @test */
    public function it_covers_ext_price_soft_currency_and_connector_with_endpoint(): void
    {
        $controller = $this->makeController();

        $withPrice = $controller->createFormSchema([
            ['type' => 'text', 'name' => 'amount', 'ext' => 'price'],
            ['type' => 'text', 'name' => 'soft', 'ext' => 'softCurrency'],
        ]);
        $this->assertArrayHasKey('amount', $withPrice);
        $this->assertArrayHasKey('soft', $withPrice);

        $module = Mockery::mock(\Nwidart\Modules\Module::class);
        $module->shouldReceive('getRouteActionUrl')->andReturn('/items');
        Modularous::shouldReceive('find')->with('Blog')->andReturn($module);

        $input = [
            'type' => 'select',
            'name' => 'post_id',
            'connector' => 'Blog:Post',
        ];
        $controller->hydrateInputConnector($input);
        $this->assertSame('Blog', $input['_moduleName']);
        $this->assertSame('Post', $input['_routeName']);
    }

    /** @test */
    public function it_covers_relationship_polymorphic_morph_to_and_extension_leftovers(): void
    {
        $parent = new FormSchemaCoverageMorphParent;
        $child = new FormSchemaCoverageMorphChild;
        $this->app->instance(FormSchemaCoverageMorphParent::class, $parent);
        $this->app->instance(FormSchemaCoverageMorphChild::class, $child);
        $this->app->instance(FormSchemaCoveragePolyModel::class, new FormSchemaCoveragePolyModel);
        $this->app->instance(FormSchemaCoveragePolyRepository::class, new FormSchemaCoveragePolyRepository);

        $repository = new class
        {
            public function getModel()
            {
                return new class
                {
                    public function getRelationType($name)
                    {
                        return $name === 'comments' ? 'HasMany' : null;
                    }

                    public function isTranslationAttribute($name): bool
                    {
                        return false;
                    }
                };
            }

            public function isTranslationAttribute($name): bool
            {
                return false;
            }
        };

        $module = Mockery::mock(\Unusualify\Modularous\Module::class);
        $module->shouldReceive('getRawRouteConfig')->andReturn([
            ['type' => 'text', 'name' => 'body'],
            ['type' => 'text', 'name' => 'item_id', 'rules' => 'required'],
        ]);
        $module->shouldReceive('getName')->andReturn('Package');
        $module->shouldReceive('getStudlyName')->andReturn('Package');

        $controller = $this->makeController([], $repository, $module);

        $relationship = $controller->createFormSchema([
            [
                'type' => 'relationship',
                'name' => 'comments',
                'schema' => [
                    ['type' => 'text', 'name' => 'body', 'default' => ''],
                    ['type' => 'text', 'name' => 'item_id', 'rules' => 'required'],
                ],
            ],
        ]);
        $this->assertArrayHasKey('comments', $relationship);
        $this->assertSame('relationship', $relationship['comments']['ext'] ?? null);
        $this->assertSame('hidden', $relationship['comments']['schema']['item_id']['type'] ?? null);

        $polymorphic = $controller->createFormSchema([
            [
                'type' => 'polymorphic',
                'name' => 'poly',
                'model' => FormSchemaCoveragePolyModel::class,
                'createable' => true,
                'rules' => 'required',
                'morphs' => [
                    [
                        'repository' => FormSchemaCoveragePolyRepository::class,
                        'name' => 'poly.item',
                        'rules' => 'required',
                    ],
                ],
            ],
        ]);
        $polyTypes = collect($polymorphic)->pluck('type')->filter()->values()->all();
        $this->assertContains('select', $polyTypes);
        $this->assertContains('combobox', $polyTypes);

        $morphTo = $controller->createFormSchema([
            [
                'type' => 'morphTo',
                'name' => 'attachable',
                'schema' => [
                    [
                        'type' => 'select',
                        'name' => 'parent_id',
                        'model' => FormSchemaCoverageMorphParent::class,
                        'items' => [],
                    ],
                    [
                        'type' => 'select',
                        'name' => 'child_id',
                        'model' => FormSchemaCoverageMorphChild::class,
                        'items' => [],
                    ],
                ],
            ],
        ]);
        $this->assertArrayHasKey('parent_id', $morphTo);
        $this->assertArrayHasKey('child_id', $morphTo);
        $this->assertSame('morphTo', $morphTo['parent_id']['ext'] ?? null);

        $moduleBlog = Mockery::mock(\Unusualify\Modularous\Module::class);
        $moduleBlog->shouldReceive('getRouteActionUrl')->andReturn('/filter-show');
        $moduleBlog->shouldReceive('getRouteClass')->with('Post', 'repository')->andReturn(FormSchemaCoveragePolyRepository::class);
        $moduleBlog->shouldReceive('getName')->andReturn('Blog');
        Modularous::shouldReceive('find')->with('Blog')->andReturn($moduleBlog);

        $withFilter = $controller->createFormSchema([
            [
                'type' => 'select',
                'name' => 'category_id',
                '_moduleName' => 'Blog',
                '_routeName' => 'Post',
                'ext' => 'filter:items:inputs:id|prependSchema:wrap:key:schema:true|permalinkPrefix:slug',
            ],
        ]);
        $this->assertArrayHasKey('category_id', $withFilter);
        $this->assertSame('/filter-show', $withFilter['category_id']['filterEndpoint'] ?? null);
        $this->assertStringContainsString('formatFilter', (string) ($withFilter['category_id']['event'] ?? ''));
        $this->assertStringContainsString('formatPrependSchema', (string) ($withFilter['category_id']['event'] ?? ''));
        $this->assertStringContainsString('formatPermalinkPrefix', (string) ($withFilter['category_id']['event'] ?? ''));
        $this->assertStringContainsString('item', (string) ($withFilter['category_id']['event'] ?? ''));

        $permalinkInputs = [
            [
                'type' => 'select',
                'name' => 'region_id',
                'repository' => FormSchemaCoveragePolyRepository::class,
                'ext' => 'permalinkPrefix:slug',
            ],
            [
                'type' => 'text',
                'name' => 'title',
                'ext' => 'permalink:slug',
            ],
        ];
        $withPermalink = $controller->createFormSchema($permalinkInputs);
        $this->assertArrayHasKey('title', $withPermalink);
        $this->assertArrayHasKey('slug', $withPermalink);
        $this->assertSame('permalink', $withPermalink['slug']['ref'] ?? null);

        $repoConnector = [
            'type' => 'select',
            'name' => 'linked_id',
            'connector' => 'Blog:Post|repository',
        ];
        $controller->hydrateInputConnector($repoConnector);
        $this->assertArrayHasKey('repository', $repoConnector);
        $this->assertArrayNotHasKey('connector', $repoConnector);

        $wrapRequired = $controller->createFormSchema([
            [
                'type' => 'wrap',
                'label' => 'Wrapped',
                'rules' => 'required|min:1',
                'schema' => [
                    ['type' => 'text', 'name' => 'inner_req', 'default' => 'z'],
                ],
            ],
            [
                'type' => 'group',
                'name' => 'meta_group',
                'label' => 'Meta',
                'schema' => [
                    [
                        'type' => 'wrap',
                        'name' => 'nested_wrap',
                        'schema' => [
                            ['type' => 'text', 'name' => 'nested_default', 'default' => 'nd'],
                        ],
                    ],
                    ['type' => 'text', 'name' => 'plain', 'default' => 'p'],
                ],
            ],
        ]);
        $wrap = collect($wrapRequired)->first(fn ($input) => ($input['type'] ?? null) === 'wrap');
        $this->assertNotNull($wrap);
        $this->assertStringContainsString('required', (string) ($wrap['class'] ?? ''));
        $this->assertArrayHasKey('meta_group', $wrapRequired);
        $this->assertSame('nd', $wrapRequired['meta_group']['default']['nested_default'] ?? null);
    }
}

class FormSchemaCoverageMorphParent
{
    public function getTableColumns(): array
    {
        return ['id', 'name', 'parent_id'];
    }
}

class FormSchemaCoverageMorphChild
{
    public function getTableColumns(): array
    {
        return ['id', 'name', 'parent_id', 'child_id'];
    }
}

class FormSchemaCoveragePolyModel
{
    public function getTableColumns(): array
    {
        $short = get_class_short_name(self::class);

        return [
            'id',
            'name',
            makeMorphForeignKey($short),
            makeMorphForeignType($short),
        ];
    }
}

class FormSchemaCoveragePolyRepository
{
    public function getModel(): FormSchemaCoveragePolyModel
    {
        return new FormSchemaCoveragePolyModel;
    }

    public function list(): array
    {
        return [
            ['id' => 1, 'name' => 'Alpha'],
            ['id' => 2, 'name' => 'Beta'],
        ];
    }
}
