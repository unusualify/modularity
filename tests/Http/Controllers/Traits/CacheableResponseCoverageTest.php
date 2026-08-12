<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Http\Controllers\Traits\CacheableResponse;
use Unusualify\Modularous\Tests\TestCase;

class CacheableResponseCoverageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('cacheable_response_children');
        Schema::dropIfExists('cacheable_response_parents');

        Schema::create('cacheable_response_parents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });

        Schema::create('cacheable_response_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('unknown_thing_id')->nullable();
            $table->string('name')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('cacheable_response_children');
        Schema::dropIfExists('cacheable_response_parents');
        parent::tearDown();
    }

    private function makeHarness(): object
    {
        return new class
        {
            use CacheableResponse;

            protected function shouldUseCache($type = null): bool
            {
                return false;
            }

            protected function rememberCache(callable $callback, string $type, array $data = [])
            {
                return $callback();
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };
    }

    /** @test */
    public function get_cacheable_form_item_bypasses_cache_when_disabled_or_missing_id(): void
    {
        $harness = $this->makeHarness();

        $this->assertSame(['x' => 1], $harness->call('getCacheableFormItem', null, fn () => ['x' => 1]));
        $this->assertSame(['y' => 2], $harness->call('getCacheableFormItem', 5, fn () => ['y' => 2]));
    }

    /** @test */
    public function extract_relation_ids_from_model_and_paginator(): void
    {
        $parent = CacheableResponseParent::query()->create(['name' => 'P']);
        $child = CacheableResponseChild::query()->create([
            'parent_id' => $parent->id,
            'unknown_thing_id' => 77,
            'name' => 'C',
        ]);

        $harness = $this->makeHarness();
        $fromModel = $harness->call('extractModelRelationIds', $child);

        $this->assertSame($parent->id, $fromModel[CacheableResponseParent::class]);
        $this->assertSame(77, $fromModel['UnknownThing']);

        $paginator = new LengthAwarePaginator([$child], 1, 10, 1);
        $fromPaginator = $harness->call('extractResponseRelationIds', $paginator);
        $this->assertSame([$parent->id], $fromPaginator[CacheableResponseParent::class]);
        $this->assertSame([77], $fromPaginator['UnknownThing']);

        $fromArray = $harness->call('extractResponseRelationIds', [$child]);
        $this->assertArrayHasKey(CacheableResponseParent::class, $fromArray);
    }
}

class CacheableResponseParent extends Model
{
    protected $table = 'cacheable_response_parents';

    protected $guarded = [];

    public $timestamps = false;
}

class CacheableResponseChild extends Model
{
    protected $table = 'cacheable_response_children';

    protected $guarded = [];

    public $timestamps = false;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CacheableResponseParent::class, 'parent_id');
    }
}
