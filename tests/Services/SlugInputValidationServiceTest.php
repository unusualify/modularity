<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Unusualify\Modularous\Entities\Traits\HasSlug;
use Unusualify\Modularous\Services\SlugInputValidationService;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\TestModulesCase;

class SlugInputValidationServiceTest extends TestModulesCase
{
    private SlugInputValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSlugTables();
        $this->service = app(SlugInputValidationService::class);
    }

    public function test_rejects_model_without_has_slug_trait(): void
    {
        $result = $this->service->validateModelSlug(TestModel::class, 'any-slug', 'en', true, null);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['message']);
    }

    public function test_propose_rejects_model_without_has_slug_trait(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->proposeUniqueSlugForModel(TestModel::class, 'hello-world', 'en', true, null);
    }

    public function test_validate_model_slug_accepts_unique_slug(): void
    {
        $result = $this->service->validateModelSlug(SlugValidationArticle::class, 'Fresh Article', 'en', true, null);

        $this->assertTrue($result['valid']);
        $this->assertSame('fresh-article', $result['normalized']);
    }

    public function test_validate_model_slug_rejects_duplicate_slug(): void
    {
        SlugValidationArticleSlug::query()->create([
            'slug_validation_article_id' => 1,
            'slug' => 'taken-slug',
            'locale' => 'en',
        ]);

        $result = $this->service->validateModelSlug(SlugValidationArticle::class, 'taken-slug', 'en', true, null);

        $this->assertFalse($result['valid']);
        $this->assertSame('taken-slug', $result['normalized']);
    }

    public function test_propose_unique_slug_adds_suffix_when_base_is_taken(): void
    {
        SlugValidationArticleSlug::query()->create([
            'slug_validation_article_id' => 1,
            'slug' => 'launch-update',
            'locale' => 'en',
        ]);

        $result = $this->service->proposeUniqueSlugForModel(
            SlugValidationArticle::class,
            'Launch Update',
            'en',
            true,
            null,
        );

        $this->assertTrue($result['suffixed']);
        $this->assertSame('launch-update-2', $result['slug']);
    }

    public function test_resolve_model_class_reads_module_route_model(): void
    {
        $modelClass = $this->service->resolveModelClass('TestModule', 'Item');

        $this->assertSame(\TestModules\TestModule\Entities\Item::class, $modelClass);
    }

    public function test_resolve_model_class_throws_for_missing_module(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->resolveModelClass('MissingModule', 'Item');
    }

    public function test_propose_unique_slug_throws_when_module_model_has_no_slug_trait(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->proposeUniqueSlug('TestModule', 'Item', 'Brand New Item', 'en', true, null);
    }

    private function createSlugTables(): void
    {
        if (! Schema::hasTable('slug_validation_articles')) {
            Schema::create('slug_validation_articles', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('slug_validation_article_slugs')) {
            Schema::create('slug_validation_article_slugs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('slug_validation_article_id');
                $table->string('slug');
                $table->string('locale');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }
}

class SlugValidationArticle extends EloquentModel
{
    use HasSlug, SoftDeletes;

    protected $table = 'slug_validation_articles';

    protected $slugModelClass = SlugValidationArticleSlug::class;

    protected $guarded = [];

    public $timestamps = false;
}

class SlugValidationArticleSlug extends EloquentModel
{
    use SoftDeletes;

    protected $table = 'slug_validation_article_slugs';

    protected $guarded = [];

    public $timestamps = false;
}
