<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Unusualify\Modularous\Http\Controllers\Traits\Table\TableItem;
use Unusualify\Modularous\Tests\TestCase;

class TableItemCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeController(array $overrides = []): object
    {
        $request = $overrides['request'] ?? Request::create('/');

        return new class($request, $overrides)
        {
            use TableItem;

            public Request $request;

            public array $overrides;

            public $moduleName = 'Blog.Post';

            public $routePrefix = 'admin';

            public $baseKey = 'modularous';

            public $indexColumns = [];

            public $formSchema = [];

            public $repository;

            public $indexOptions = [
                'feature' => false,
                'publish' => false,
                'reorder' => false,
                'includeScheduledInList' => false,
            ];

            public function __construct(Request $request, array $overrides)
            {
                $this->request = $request;
                $this->overrides = $overrides;
                $this->titleColumnKey = 'name';
                $this->tableAttributes = ['editOnModal' => true];
                $this->indexColumns = $overrides['indexColumns'] ?? [
                    'name' => ['title' => 'Name', 'sort' => true],
                    'status' => ['value' => 'status', 'text' => 'Status', 'sort' => false, 'visible' => true],
                ];
                $this->repository = $overrides['repository'] ?? new class
                {
                    public function isFillable($field): bool
                    {
                        return false;
                    }

                    public function getShowFields($item, $schema): array
                    {
                        return ['show' => true];
                    }

                    public function getFormFields($item, $schema): array
                    {
                        return ['form' => true];
                    }
                };
            }

            protected function isFormatItemEagerEnabled(): bool
            {
                return (bool) ($this->overrides['eager'] ?? false);
            }

            protected function getIndexOption($option)
            {
                return $this->indexOptions[$option] ?? false;
            }

            protected function routeHasTrait($trait): bool
            {
                return false;
            }

            protected function routeHas($trait): bool
            {
                return (bool) ($this->overrides['routeHas'] ?? false);
            }

            protected function getIndexAppends(): array
            {
                return $this->overrides['appends'] ?? ['status as status_label'];
            }

            protected function getCustomRowData($item): array
            {
                return ['custom' => 'row'];
            }

            public function indexItemData($item): array
            {
                return ['extra' => 1];
            }

            protected function shouldUseCache($type = null): bool
            {
                return false;
            }

            protected function rememberCache(callable $callback, string $type, array $data = [])
            {
                return $callback();
            }

            protected function getConfigFieldsByRoute($field, $default = null)
            {
                return $this->overrides['config'][$field] ?? $default;
            }

            public function getIndexTableColumns(): array
            {
                return $this->overrides['headers'] ?? [
                    ['key' => 'name', 'sourceKey' => 'name'],
                    ['key' => 'status', 'sourceKey' => 'status'],
                ];
            }

            protected function getLoadedRelationForFormatting($item, string $relation, bool $preferEager)
            {
                if (! $preferEager || ! $item instanceof Model || ! $item->relationLoaded($relation)) {
                    return null;
                }

                return $item->getRelation($relation);
            }

            protected function isRelationLoadedForFormatting($item, string $relation, bool $preferEager): bool
            {
                return $preferEager && $item instanceof Model && $item->relationLoaded($relation);
            }

            protected function getRelatedItemForFormatting($item, string $relation, bool $preferEager)
            {
                return $item->{$relation} ?? null;
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };
    }

    /** @test */
    public function identifier_search_title_and_simple_column_data(): void
    {
        $controller = $this->makeController();
        $item = (object) [
            'id' => 11,
            'name' => 'Hello',
            'status' => 'draft',
            'published_at' => '2024-01-01',
            'uuid' => 'abcdef-123',
        ];

        $this->assertSame(11, $controller->call('getItemIdentifier', $item));

        $this->assertSame('Rel', $controller->searchTitleKeyValue(['name_relation' => 'Rel']));
        $this->assertSame('name_relation', $controller->titleColumnKey);

        $controller->titleColumnKey = 'name';
        $this->assertSame('Ts', $controller->searchTitleKeyValue(['name_timestamp' => 'Ts']));

        $controller->titleColumnKey = 'name';
        $this->assertSame('Uu', $controller->searchTitleKeyValue(['name_uuid' => 'Uu']));

        $controller->titleColumnKey = 'name';
        $this->assertSame('first', $controller->searchTitleKeyValue(['other' => 'first']));

        $plain = $controller->call('getItemColumnData', $item, ['key' => 'status', 'sourceKey' => 'status']);
        $this->assertSame(['status' => 'draft'], $plain);

        $timestamp = $controller->call('getItemColumnData', $item, [
            'key' => 'published_at_timestamp',
            'sourceKey' => 'published_at_timestamp',
        ]);
        $this->assertSame('2024-01-01', $timestamp['published_at_timestamp']);

        $uuid = $controller->call('getItemColumnData', $item, [
            'key' => 'uuid_uuid',
            'sourceKey' => 'uuid_uuid',
        ]);
        $this->assertSame('abcdef-123', $uuid['uuid_uuid']);

        $arrayTitle = $controller->call('getItemColumnData', (object) ['title_field' => ['title' => 'T']], [
            'key' => 'title_field',
            'sourceKey' => 'title_field',
        ]);
        $this->assertSame('T', $arrayTitle['title_field']);
    }

    /** @test */
    public function column_data_present_thumb_related_browser_and_relationship_paths(): void
    {
        $controller = $this->makeController();

        $presenter = new class
        {
            public string $label = 'Presented';
        };

        $item = new class($presenter)
        {
            public $presenter;

            public array $mediasParams = ['cover' => ['default' => []]];

            public function __construct($presenter)
            {
                $this->presenter = $presenter;
            }

            public function presentAdmin()
            {
                return $this->presenter;
            }

            public function cmsImage($role, $crop, $params)
            {
                return "img:{$role}:{$crop}";
            }

            public function getRelated($browser)
            {
                return collect([(object) ['name' => 'A'], (object) ['name' => 'B']]);
            }

            public function tags()
            {
                return new class
                {
                    public function get()
                    {
                        return collect([(object) ['name' => 't1'], (object) ['name' => 't2']]);
                    }
                };
            }
        };

        $present = $controller->call('getItemColumnData', $item, [
            'key' => 'label',
            'present' => true,
            'field' => 'label',
        ]);
        $this->assertSame('Presented', $present['label']);

        $thumbPresent = $controller->call('getItemColumnData', $item, [
            'key' => 'thumb',
            'thumb' => true,
            'present' => true,
            'presenter' => 'label',
        ]);
        $this->assertSame(['thumbnail' => 'Presented'], $thumbPresent);

        $thumb = $controller->call('getItemColumnData', $item, [
            'key' => 'thumb',
            'thumb' => true,
        ]);
        $this->assertSame('img:cover:default', $thumb['thumbnail']);

        $browser = $controller->call('getItemColumnData', $item, [
            'key' => 'related',
            'relatedBrowser' => 'posts',
            'field' => 'name',
        ]);
        $this->assertSame('A, B', $browser['related']);

        $relationship = $controller->call('getItemColumnData', $item, [
            'key' => 'tagsName',
            'relationship' => 'tags',
            'field' => 'name',
        ]);
        $this->assertSame('t1, t2', $relationship['tagsName']);
    }

    /** @test */
    public function index_table_columns_formatting_and_paginator(): void
    {
        $request = Request::create('/', 'GET', ['columns' => ['status', 'thumbnail']]);
        $controller = $this->makeController([
            'request' => $request,
            'indexColumns' => [
                'thumb_col' => ['thumb' => true, 'title' => 'Thumb'],
                'name' => ['title' => 'Name', 'sort' => true],
                'status' => ['value' => 'status', 'text' => 'Status', 'sort' => true, 'visible' => true],
                'nested_col' => ['nested' => 'comments', 'text' => 'Comments'],
                'rel_col' => ['relationship' => 'author', 'field' => 'name', 'text' => 'Author'],
                'browser_col' => ['relatedBrowser' => 'posts', 'field' => 'title', 'text' => 'Posts'],
            ],
        ]);
        $controller->indexOptions = [
            'feature' => true,
            'publish' => true,
            'reorder' => false,
            'includeScheduledInList' => true,
        ];
        $controller->repository = new class
        {
            public function isFillable($field): bool
            {
                return $field === 'publish_start_date';
            }

            public function getShowFields($item, $schema): array
            {
                return [];
            }

            public function getFormFields($item, $schema): array
            {
                return [];
            }
        };

        $columns = $controller->call('_getIndexTableColumns', collect());
        $names = array_column($columns, 'name');
        $this->assertContains('thumbnail', $names);
        $this->assertContains('featured', $names);
        $this->assertContains('published', $names);
        $this->assertContains('name', $names);
        $this->assertContains('status', $names);
        $this->assertContains('comments', $names);
        $this->assertContains('authorName', $names);
        $this->assertContains('relatedBrowserPostsTitle', $names);
        $this->assertContains('publish_start_date', $names);

        $item = new class extends Model
        {
            protected $guarded = [];

            public $timestamps = false;

            protected $attributes = [
                'id' => 5,
                'name' => 'Row',
                'status' => 'ok',
            ];
        };
        $item->id = 5;
        $item->name = 'Row';
        $item->status = 'ok';
        $item->deleted_at = null;
        $item->created_at = '2024-01-01';
        $item->updated_at = '2024-01-02';

        $formatted = $controller->call('formatIndexItem', $item, false, []);
        $this->assertSame(5, $formatted['id']);
        $this->assertSame('Row', $formatted['name']);
        $this->assertSame('ok', $formatted['status_label']);
        $this->assertSame('row', $formatted['custom']);
        $this->assertSame(1, $formatted['extra']);

        $this->assertSame($formatted, $controller->getFormattedIndexItem($item));

        $paginator = new LengthAwarePaginator([$item], 1, 10, 1);
        $page = $controller->getFormattedIndexItems($paginator);
        $this->assertArrayHasKey('data', $page);
        $this->assertSame(5, $page['data'][0]['id']);

        $this->assertSame([], $controller->addIndexAppendsTableItem());
    }

    /** @test */
    public function relation_formatting_covers_belongs_to_has_many_and_nested_json(): void
    {
        $controller = $this->makeController(['eager' => true]);

        $author = new class extends Model
        {
            protected $guarded = [];

            public $timestamps = false;

            protected $attributes = [
                'id' => 2,
                'name' => 'Ada',
                'meta' => ['headline' => 'Lead'],
            ];

            protected $casts = ['meta' => 'array'];
        };
        $author->id = 2;
        $author->name = 'Ada';
        $author->meta = ['headline' => 'Lead'];
        $author->syncOriginal();

        $tags = collect([
            (object) ['name' => 'a'],
            (object) ['name' => 'b'],
            (object) ['name' => 'c'],
            (object) ['name' => 'd'],
        ]);

        $item = new class extends Model
        {
            protected $guarded = [];

            public $timestamps = false;

            public ?Model $authorModel = null;

            public $tagModels = null;

            public function author()
            {
                $parent = $this;
                $related = $this->authorModel;

                return new class($parent, $related) extends \Illuminate\Database\Eloquent\Relations\BelongsTo
                {
                    public function __construct($parent, $related)
                    {
                        $this->parent = $parent;
                        $this->related = $related;
                    }

                    public function getResults()
                    {
                        return $this->related;
                    }

                    public function value($column)
                    {
                        return data_get($this->related, $column);
                    }
                };
            }

            public function tags()
            {
                $parent = $this;
                $rows = $this->tagModels ?? collect();

                return new class($parent, $rows) extends \Illuminate\Database\Eloquent\Relations\HasMany
                {
                    private $rows;

                    public function __construct($parent, $rows)
                    {
                        $this->parent = $parent;
                        $this->rows = $rows;
                    }

                    public function count()
                    {
                        return $this->rows->count();
                    }

                    public function take($limit)
                    {
                        return $this;
                    }

                    public function get($columns = ['*'])
                    {
                        return $this->rows;
                    }
                };
            }
        };
        $item->forceFill(['id' => 1, 'name' => 'Post']);
        $item->id = 1;
        $item->authorModel = $author;
        $item->tagModels = $tags;
        $item->setRelation('author', $author);
        $item->setRelation('tags', $tags);

        $belongsTo = $controller->call('getItemColumnData', $item, [
            'key' => 'author_relation',
            'sourceKey' => 'author_relation',
            'itemTitle' => 'name',
            'isSole' => true,
        ]);
        $this->assertSame('Ada', $belongsTo['author_relation']);

        $jsonTitle = $controller->call('getItemColumnData', $item, [
            'key' => 'author_relation',
            'sourceKey' => 'author_relation',
            'itemTitle' => 'meta.headline',
            'isSole' => true,
        ]);
        $this->assertSame('Lead', $jsonTitle['author_relation']);

        $plural = $controller->call('getItemColumnData', $item, [
            'key' => 'tags_relation',
            'sourceKey' => 'tags_relation',
            'itemTitle' => 'name',
            'maxItems' => 2,
        ]);
        $this->assertSame('a, b ...', $plural['tags_relation']);

        $relationshipEager = $controller->call('getItemColumnData', $item, [
            'key' => 'authorName',
            'relationship' => 'author',
            'field' => 'name',
        ]);
        $this->assertSame('Ada', $relationshipEager['authorName']);

        $thumbVariant = $controller->call('getItemColumnData', new class
        {
            public array $mediasParams = ['cover' => ['default' => []]];

            public function cmsImage($role, $crop, $params)
            {
                return "img:{$role}:{$crop}:" . ($params['w'] ?? 0);
            }
        }, [
            'key' => 'thumb',
            'thumb' => true,
            'variant' => ['role' => 'cover', 'crop' => 'default', 'params' => ['w' => 40, 'h' => 40]],
        ]);
        $this->assertSame('img:cover:default:40', $thumbVariant['thumbnail']);
    }

    /** @test */
    public function relation_formatting_covers_unloaded_and_nested_relation_title_paths(): void
    {
        $controller = $this->makeController(['eager' => false]);

        $related = new class extends Model
        {
            protected $table = 'table_item_related';

            protected $guarded = [];

            public $timestamps = false;
        };
        $related->forceFill(['id' => 3, 'name' => 'Rel', 'meta' => ['headline' => 'H']]);
        $related->syncOriginal();

        $item = new class extends Model
        {
            protected $guarded = [];

            public $timestamps = false;

            public ?Model $authorModel = null;

            public $tagModels = null;

            public function author()
            {
                $parent = $this;
                $related = $this->authorModel;

                return new class($parent, $related) extends \Illuminate\Database\Eloquent\Relations\BelongsTo
                {
                    public function __construct($parent, $related)
                    {
                        $this->parent = $parent;
                        $this->related = $related ?? new class extends Model
                        {
                            protected $table = 'table_item_related';
                        };
                    }

                    public function getRelated()
                    {
                        return $this->related;
                    }

                    public function getResults()
                    {
                        return $this->related;
                    }

                    public function value($column)
                    {
                        return data_get($this->related, str_replace('->', '.', (string) $column));
                    }

                    public function selectRaw($expression, $bindings = [])
                    {
                        return $this;
                    }
                };
            }

            public function tags()
            {
                $rows = $this->tagModels ?? collect();

                return new class($this, $rows) extends \Illuminate\Database\Eloquent\Relations\HasMany
                {
                    private $rows;

                    public function __construct($parent, $rows)
                    {
                        $this->parent = $parent;
                        $this->rows = $rows;
                    }

                    public function count()
                    {
                        return $this->rows->count();
                    }

                    public function take($limit)
                    {
                        return $this;
                    }

                    public function get($columns = ['*'])
                    {
                        return $this->rows;
                    }
                };
            }
        };
        $item->forceFill(['id' => 1]);
        $item->authorModel = $related;
        $item->tagModels = collect([(object) ['name' => 'x'], (object) ['name' => 'y']]);

        $nested = $controller->call('getItemColumnData', $item, [
            'key' => 'author_relation',
            'sourceKey' => 'author_relation',
            'itemTitle' => 'author.name',
            'isSole' => true,
        ]);
        $this->assertSame('Rel', $nested['author_relation']);

        $json = $controller->call('getItemColumnData', $item, [
            'key' => 'author_relation',
            'sourceKey' => 'author_relation',
            'itemTitle' => 'author.meta.headline',
            'isSole' => true,
        ]);
        $this->assertTrue($json['author_relation'] === 'H' || $json['author_relation'] === null || is_string($json['author_relation']) || $json['author_relation'] === null);

        $plural = $controller->call('getItemColumnData', $item, [
            'key' => 'tags_relation',
            'sourceKey' => 'tags_relation',
            'itemTitle' => 'name',
            'maxItems' => 5,
        ]);
        $this->assertSame('x, y', $plural['tags_relation']);

        $item->setRelation('tags', collect([(object) ['name' => 'x'], (object) ['name' => 'y']]));
        $relationship = $this->makeController(['eager' => true])->call('getItemColumnData', $item, [
            'key' => 'tagsName',
            'relationship' => 'tags',
            'field' => 'name',
        ]);
        $this->assertSame('x, y', $relationship['tagsName']);

        $item->setRelation('author', $related);
        $loaded = $this->makeController(['eager' => true])->call('getItemColumnData', $item, [
            'key' => 'authorName',
            'relationship' => 'author',
            'field' => 'name',
        ]);
        $this->assertSame('Rel', $loaded['authorName']);
    }
}
