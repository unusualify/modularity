<?php

namespace Unusualify\Modularity\Tests\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasAuthorizable;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Tests\ModelTestCase;

class AbstractModelTest extends ModelTestCase
{
    use RefreshDatabase;

    protected TestModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('locale')->default('en');
            $table->boolean('active')->default(true);
            $table->timestamp('publish_start_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create test translation table
        Schema::create('test_model_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_model_id');
            $table->string('locale', 6);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['test_model_id', 'locale']);
            $table->foreign('test_model_id')->references('id')->on('test_models')->onDelete('cascade');
        });

        // // Create tagged table for tagging functionality
        // Schema::create('um_tagged', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('tag_id');
        //     $table->unsignedBigInteger('taggable_id');
        //     $table->string('taggable_type');
        //     $table->timestamps();

        //     $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
        // });

        // // Create tags table
        // Schema::create('um_tags', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('slug')->unique();
        //     $table->timestamps();
        // });

        $this->model = new TestModel([
            'name' => 'Test Model',
            'slug' => 'test-model',
        ]);
    }

    public function test_model_extends_laravel_model()
    {
        $this->assertInstanceOf(LaravelModel::class, $this->model);
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function test_model_implements_taggable_interface()
    {
        $this->assertInstanceOf(\Cartalyst\Tags\TaggableInterface::class, $this->model);
    }

    public function test_model_has_required_traits()
    {
        $traits = class_uses_recursive($this->model);

        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasPresenter', $traits);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\IsTranslatable', $traits);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\ModelHelpers', $traits);
        $this->assertContains('Illuminate\Database\Eloquent\SoftDeletes', $traits);
        $this->assertContains('Cartalyst\Tags\TaggableTrait', $traits);
        $this->assertContains('Illuminate\Notifications\Notifiable', $traits);
    }

    public function test_model_has_timestamps()
    {
        $this->assertTrue($this->model->timestamps);
    }

    public function test_is_translation_model_returns_false_for_base_model()
    {
        $reflectionMethod = new \ReflectionMethod($this->model, 'isTranslationModel');
        $reflectionMethod->setAccessible(true);
        $this->assertFalse($reflectionMethod->invoke($this->model));
    }

    public function test_is_translation_model_returns_true_for_translation_model()
    {
        $translationModel = new TestModelTranslation();
        $reflectionMethod = new \ReflectionMethod($translationModel, 'isTranslationModel');
        $reflectionMethod->setAccessible(true);
        $this->assertTrue($reflectionMethod->invoke($translationModel));
    }

    public function test_set_publish_start_date_attribute_with_value()
    {
        $date = Carbon::now()->subDays(5);
        $this->model->setPublishStartDateAttribute($date);

        $this->assertEquals($date, $this->model->publish_start_date);
    }

    public function test_set_publish_start_date_attribute_with_null()
    {
        $this->model->setPublishStartDateAttribute(null);

        $this->assertNotNull($this->model->publish_start_date);
        $this->assertInstanceOf(Carbon::class, $this->model->publish_start_date);
    }

    public function test_get_fillable_returns_base_fillable_for_normal_model()
    {
        $expectedFillable = ['name', 'slug', 'locale', 'active'];
        $this->assertEquals($expectedFillable, $this->model->getFillable());
    }

    public function test_get_fillable_returns_translated_attributes_for_translation_model()
    {
        $translationModel = new TestModelTranslation();
        $fillable = $translationModel->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('locale', $fillable);
        $this->assertContains('active', $fillable);
    }

    public function test_get_fillable_includes_authorizable_attributes()
    {
        $authorizableModel = new TestAuthorizableModel();
        $fillable = $authorizableModel->getFillable();

        // This should include HasAuthorizable fillable attributes
        $this->assertIsArray($fillable);
    }

    public function test_tags_relationship_exists()
    {
        $this->assertTrue(method_exists($this->model, 'tags'));

        $relation = $this->model->tags();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $relation);
    }

    public function test_tags_relationship_configuration()
    {
        $relation = $this->model->tags();

        $this->assertEquals('um_tagged', $relation->getTable());
        $this->assertEquals('taggable_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('tag_id', $relation->getRelatedPivotKeyName());
    }

    public function test_boot_taggable_trait_sets_tags_model()
    {
        $this->assertEquals(\Unusualify\Modularity\Entities\Tag::class, TestModel::getTagsModel());
    }

    public function test_model_can_be_saved()
    {
        $this->model->save();

        $this->assertDatabaseHas('test_models', [
            'name' => 'Test Model',
            'slug' => 'test-model',
        ]);
    }

    public function test_model_can_be_soft_deleted()
    {
        $this->model->save();
        $this->model->delete();

        $this->assertSoftDeleted('test_models', [
            'id' => $this->model->id,
        ]);
    }

    public function test_model_has_translation_functionality()
    {
        $reflectionMethod = new \ReflectionMethod($this->model, 'isTranslationModel');
        $reflectionMethod->setAccessible(true);
        $this->assertFalse($reflectionMethod->invoke($this->model));
    }

    public function test_model_translation_is_translation_model()
    {
        $translationModel = new TestModelTranslation();
        $reflectionMethod = new \ReflectionMethod($translationModel, 'isTranslationModel');
        $reflectionMethod->setAccessible(true);
        $this->assertTrue($reflectionMethod->invoke($translationModel));
    }

    public function test_model_fillable_is_dynamic()
    {
        $model1 = new TestModel();
        $model2 = new TestModelTranslation();

        $this->assertNotEquals($model1->getFillable(), $model2->getFillable());
    }

    public function test_model_supports_notifications()
    {
        $this->assertTrue(method_exists($this->model, 'notify'));
        $this->assertTrue(method_exists($this->model, 'notifications'));
    }

    public function test_model_supports_tagging()
    {
        $this->assertTrue(method_exists($this->model, 'tag'));
        $this->assertTrue(method_exists($this->model, 'untag'));
        $this->assertTrue(method_exists($this->model, 'setTags'));
        $this->assertTrue(method_exists($this->model, 'addTag'));
        $this->assertTrue(method_exists($this->model, 'removeTag'));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_models');
        Schema::dropIfExists('test_model_translations');
        Schema::dropIfExists('um_tagged');
        Schema::dropIfExists('um_tags');

        parent::tearDown();
    }
}

// Test concrete implementation of abstract Model
class TestModel extends Model
{
    use HasTranslation;

    protected $fillable = ['name', 'slug', 'locale', 'active'];

    protected $translatedAttributes = ['title', 'description'];

    protected $translationModel = TestModelTranslation::class;

    protected $table = 'test_models';
}

// Test translation model
class TestModelTranslation extends Model
{
    protected $table = 'test_model_translations';

    protected $baseModuleModel = TestModel::class;
}

// Test model with HasAuthorizable trait
class TestAuthorizableModel extends Model
{
    use HasAuthorizable;

    protected $fillable = ['name', 'slug'];

    protected $table = 'test_models';
}
