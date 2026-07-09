<?php

namespace Unusualify\Modularous\Tests\Repositories\Logic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Repositories\Logic\MethodTransformers;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class MethodTransformersTest extends RepositoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('method_transformer_models', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('method_transformer_models');
        parent::tearDown();
    }
    public function test_cleanup_fields_normalizes_checkboxes_and_nullables(): void
    {
        $repo = new MethodTransformersStub(new CheckboxNullableModel);

        $fields = $repo->cleanupFields(new CheckboxNullableModel, [
            'is_active' => 1,
        ]);

        $this->assertTrue($fields['is_active']);
        $this->assertNull($repo->cleanupFields(new CheckboxNullableModel, [])['notes'] ?? null);
    }

    public function test_prepare_fields_before_create_runs_cleanup(): void
    {
        $repo = new MethodTransformersStub(new CheckboxNullableModel);

        $fields = $repo->prepareFieldsBeforeCreate(['is_active' => 0]);

        $this->assertFalse($fields['is_active']);
    }

    public function test_should_bypass_after_save_hook_when_pass_flag_set(): void
    {
        $repo = new MethodTransformersStub(new CheckboxNullableModel);
        $repo->passAfterSaveSlugsTrait = true;

        $ref = new \ReflectionMethod($repo, 'shouldBypassAfterSaveHook');

        $this->assertTrue($ref->invoke($repo, 'afterSaveSlugsTrait'));
        $this->assertFalse($ref->invoke($repo, 'beforeSave'));
    }

    public function test_get_count_by_status_slug_returns_all_count(): void
    {
        CheckboxNullableModel::query()->insert([
            ['is_active' => true, 'notes' => 'a'],
            ['is_active' => false, 'notes' => 'b'],
        ]);

        $repo = new MethodTransformersStub(new CheckboxNullableModel);

        $this->assertSame(2, $repo->getCountByStatusSlug('all'));
    }

    public function test_trait_has_input_checks_trait_columns(): void
    {
        $repo = new MethodTransformersStub(new CheckboxNullableModel);
        $repo->traitColumns = ['SlugsTrait' => ['slug']];

        $this->assertTrue($repo->traitHasInput(\Unusualify\Modularous\Repositories\Traits\SlugsTrait::class, 'slug'));
        $this->assertFalse($repo->traitHasInput(\Unusualify\Modularous\Repositories\Traits\SlugsTrait::class, 'title'));
    }
}

class CheckboxNullableModel extends Model
{
    protected $table = 'method_transformer_models';

    protected $fillable = ['is_active', 'notes'];

    public $checkboxes = ['is_active'];

    public $nullable = ['notes'];
}

class MethodTransformersStub
{
    use MethodTransformers;

    public array $traitColumns = [];

    public function __construct(public Model $model) {}

    public function getModel(): Model
    {
        return $this->model;
    }

    public function shouldIgnoreFieldBeforeSave(string $field): bool
    {
        return false;
    }

    public function traitsMethods(string $method): array
    {
        return [];
    }

    public function getCountForAll(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getCountForPublished(): int
    {
        return 0;
    }

    public function getCountForDraft(): int
    {
        return 0;
    }

    public function getCountForTrash(): int
    {
        return 0;
    }

    protected function cacheableCount(string $slug, callable $callback, array $scope = []): int
    {
        return $callback();
    }
}
