<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mockery;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\FormPageUtility;
use Unusualify\Modularous\Tests\TestCase;

class FormPageUtilityCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeItem(array $attrs = []): object
    {
        $item = new class extends Model
        {
            protected $guarded = [];

            public $timestamps = false;

            public function getActiveLanguages(): array
            {
                return ['en'];
            }
        };

        foreach ($attrs as $key => $value) {
            $item->{$key} = $value;
        }

        return $item;
    }

    private function makeController(array $overrides = []): object
    {
        $self = $this;

        return new class($overrides, $self)
        {
            use FormPageUtility;

            public array $overrides;

            public $testCase;

            public bool $isSingleton = false;

            public $repository;

            public array $formWith = [];

            public array $formWithCount = [];

            public string $titleColumnKey = 'name';

            public array $formSchema = ['name' => []];

            public string $routeName = 'Item';

            public string $routePrefix = 'admin';

            public $nestedParentId = null;

            public bool $isNested = false;

            public Request $request;

            public array $formAppends = ['status as status_label'];

            public function __construct(array $overrides, $testCase)
            {
                $this->overrides = $overrides;
                $this->testCase = $testCase;
                $this->isSingleton = (bool) ($overrides['isSingleton'] ?? false);
                $this->request = $overrides['request'] ?? Request::create('/');
                $this->repository = $overrides['repository'] ?? $this->defaultRepository();
            }

            private function defaultRepository()
            {
                $testCase = $this->testCase;

                return new class($testCase)
                {
                    public function __construct(private $testCase) {}

                    public function getModel()
                    {
                        $testCase = $this->testCase;

                        return new class($testCase)
                        {
                            public function __construct(private $testCase) {}

                            public function single()
                            {
                                return $this->testCase->makePublicItem([
                                    'id' => 99,
                                    'name' => 'Singleton',
                                    'status' => 'live',
                                ]);
                            }
                        };
                    }

                    public function getById($id, $with = [], $withCount = [], $lazy = [], $scopes = [], $useDefaultScopes = true)
                    {
                        return $this->testCase->makePublicItem([
                            'id' => $id,
                            'name' => 'Existing',
                            'status' => 'draft',
                            'created_at' => 'c',
                            'updated_at' => 'u',
                        ]);
                    }

                    public function newInstance()
                    {
                        return $this->testCase->makePublicItem([
                            'id' => null,
                            'name' => '',
                            'status' => null,
                        ]);
                    }

                    public function getFormFields($item, $schema = [], $noSerialization = false): array
                    {
                        return ['name' => $item->name ?? ''];
                    }
                };
            }

            protected function filterScope($scopes = [])
            {
                return $scopes;
            }

            protected function nestedParentScopes(): array
            {
                return ['parent_id' => 1];
            }

            public function addFormAppends(): void {}

            public function getFormAppends(): array
            {
                return $this->formAppends;
            }

            protected function getCacheableFormItem($id, callable $formItemCallback): array
            {
                return $formItemCallback();
            }

            protected function getModuleRoute($id, $action, $singleton = false): string
            {
                return "/admin/item/{$id}/{$action}";
            }

            protected function routeHasTrait($trait): bool
            {
                return (bool) ($this->overrides['translations'] ?? false);
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };
    }

    public function makePublicItem(array $attrs = []): object
    {
        return $this->makeItem($attrs);
    }

    /** @test */
    public function repository_item_paths_and_form_item_building(): void
    {
        $controller = $this->makeController(['isSingleton' => true]);
        $id = null;
        $singleton = $controller->getRepositoryItem($id);
        $this->assertSame(99, $id);
        $this->assertSame('Singleton', $singleton->name);

        $controller = $this->makeController();
        $existingId = 5;
        $existing = $controller->getRepositoryItem($existingId);
        $this->assertSame(5, $existing->id);

        $newId = null;
        $new = $controller->getRepositoryItem($newId);
        $this->assertNull($new->id);

        $this->assertSame($existing, $controller->formItem($existing));

        $formItem = $controller->getFormItem(5);
        $this->assertSame(5, $formItem['id']);
        $this->assertSame('Existing', $formItem['name']);
        $this->assertSame('draft', $formItem['status_label']);
    }

    /** @test */
    public function form_url_modal_data_and_modal_form_data_hook(): void
    {
        $controller = $this->makeController();
        $this->assertSame('/admin/item/3/update', $controller->getFormUrl(3));
        // getFormUrl(null) calls moduleRoute and dd() on failure — skipped (tightly coupled).

        $this->assertSame([], $controller->modalFormData(Request::create('/')));

        $controller = $this->makeController(['translations' => true]);
        $testCase = $this;
        $controller->repository = new class($testCase)
        {
            public function __construct(private $testCase) {}

            public function getById($id, $with = [], $withCount = [])
            {
                return $this->testCase->makePublicItem(['id' => $id]);
            }

            public function getFormFields($item): array
            {
                return [
                    'translations' => ['title' => ['en' => 'Hello']],
                    'name' => 'N',
                ];
            }
        };

        // Override getActiveLanguages for translations branch
        $item = $this->makePublicItem(['id' => 1]);
        $controller->repository = new class($item)
        {
            public function __construct(private $item) {}

            public function getById($id, $with = [], $withCount = [])
            {
                $this->item->id = $id;

                return new class($this->item)
                {
                    public function __construct(private $inner) {}

                    public function __get($key)
                    {
                        return $this->inner->{$key};
                    }

                    public function getActiveLanguages(): array
                    {
                        return ['en', 'tr'];
                    }
                };
            }

            public function getFormFields($item): array
            {
                return [
                    'translations' => ['title' => ['en' => 'Hello']],
                    'name' => 'N',
                ];
            }
        };

        $modal = $controller->call('getModalFormData', 1);
        $this->assertSame(['en', 'tr'], $modal['languages']);
        $this->assertNotEmpty($modal['fields']);
    }
}
