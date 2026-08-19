<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Unusualify\Modularous\Http\Controllers\Traits\Table\TableEager;
use Unusualify\Modularous\Tests\TestCase;

class TableEagerMergeTest extends TestCase
{
    public function test_merge_index_withs_merges_dotted_plain_into_existing_assoc_root(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [
                'creator' => [
                    'roles',
                ],
            ],
            [
                'creator.company',
            ]
        );

        $this->assertSame(
            [
                'creator' => [
                    'roles',
                    'company',
                ],
            ],
            $result
        );
    }

    public function test_merge_index_withs_preserves_deeper_dotted_tail_under_assoc_root(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [
                'creator' => [
                    'roles',
                ],
            ],
            [
                'creator.company.logo',
            ]
        );

        $this->assertSame(
            [
                'creator' => [
                    'roles',
                    'company.logo',
                ],
            ],
            $result
        );
    }

    public function test_merge_index_withs_collapses_plain_root_with_dotted_plain_paths(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [
                'creator',
            ],
            [
                'creator.company',
                'creator.roles',
            ]
        );

        $this->assertSame(
            [
                'creator' => [
                    'roles',
                    'company',
                ],
            ],
            $result
        );
    }

    public function test_merge_index_withs_promotes_single_dotted_path_when_no_plain_siblings(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [],
            [
                'creator.company',
            ]
        );

        $this->assertSame(
            [
                'creator' => [
                    'company',
                ],
            ],
            $result
        );
    }

    public function test_merge_index_withs_keeps_single_dotted_path_when_plain_sibling_exists(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [],
            [
                'roles',
                'company.logo',
            ]
        );

        $this->assertSame(
            [
                'roles',
                'company.logo',
            ],
            $result
        );
    }

    public function test_is_format_item_eager_enabled_reads_route_module_and_config(): void
    {
        $stub = new class
        {
            use TableEager;

            public $module = null;

            public $routeValue = null;

            public function getConfigFieldsByRoute($key, $default = null)
            {
                return $key === 'use_format_item_eager' ? $this->routeValue : $default;
            }

            public function enabled(): bool
            {
                return $this->isFormatItemEagerEnabled();
            }
        };

        $stub->routeValue = true;
        $this->assertTrue($stub->enabled());

        $stub->routeValue = null;
        $stub->module = new class
        {
            public function getRawConfig(): array
            {
                return ['use_format_item_eager' => true];
            }
        };
        $this->assertTrue($stub->enabled());

        $stub->module = null;
        config(['modularous.use_format_item_eager' => false]);
        $this->assertFalse($stub->enabled());
    }

    public function test_resolve_header_withs_and_formatting_helpers(): void
    {
        $related = new TableEagerRelatedModel;
        $model = new TableEagerRootModel;

        $stub = new class
        {
            use TableEager;

            public function resolve($with, Model $model): array
            {
                return $this->resolveHeaderWiths($with, $model);
            }

            public function derive(array $header, Model $model): array
            {
                return $this->deriveHeaderWithsFromDotNotation($header, $model);
            }

            public function normalize($with): array
            {
                return $this->normalizeWithValue($with);
            }

            public function loaded($item, string $relation, bool $preferEager)
            {
                return $this->getLoadedRelationForFormatting($item, $relation, $preferEager);
            }

            public function isLoaded($item, string $relation, bool $preferEager): bool
            {
                return $this->isRelationLoadedForFormatting($item, $relation, $preferEager);
            }

            public function related($item, string $relation, bool $preferEager)
            {
                return $this->getRelatedItemForFormatting($item, $relation, $preferEager);
            }
        };

        $this->assertSame(['creator'], $stub->normalize('creator'));
        $this->assertSame([], $stub->normalize(123));

        $resolved = $stub->resolve([
            'creator' => ['functions' => ['withTrashed']],
            0 => 'missing.relation',
        ], $model);
        $this->assertArrayHasKey('creator', $resolved);
        $this->assertIsCallable($resolved['creator']);

        $derived = $stub->derive([
            'key' => 'creator.name',
            'searchKey' => 'creator.id',
        ], $model);
        $this->assertSame(['creator'], $derived);

        $model->setRelation('creator', $related);
        $this->assertSame($related, $stub->loaded($model, 'creator', true));
        $this->assertTrue($stub->isLoaded($model, 'creator', true));
        $this->assertNull($stub->loaded($model, 'creator', false));
        $this->assertSame($related, $stub->related($model, 'creator', true));
    }
}

class TableEagerRelatedModel extends Model
{
    protected $table = 'table_eager_related';
}

class TableEagerRootModel extends Model
{
    protected $table = 'table_eager_roots';

    public function definedRelations(): array
    {
        return ['creator'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(TableEagerRelatedModel::class, 'creator_id');
    }
}
