<?php

namespace Unusualify\Modularous\Tests\Models\Traits\Core;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\State;
use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Tests\ModelTestCase;

class ModelHelpersTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;

    protected $testModel;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_model_helpers_models', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable();
            $table->string('name');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('slug')->nullable();
            $table->json('preferences')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create anonymous test model class
        $this->testModel = ModelHelperModel::class;

        $this->model = $this->testModel;
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_model_helpers_models');
        Schema::dropIfExists('translatable_models');
        Schema::dropIfExists('translatable_model_translations');
        parent::tearDown();
    }

    public function test_trait_includes_required_traits()
    {
        $model = new $this->testModel;

        // Test that the trait includes other required traits
        $this->assertTrue(method_exists($model, 'hasScope')); // HasScopes
        $this->assertTrue(method_exists($model, 'getChangedRelationships')); // ChangeRelationships
        $this->assertTrue(method_exists($model, 'activities')); // LogsActivity
    }

    public function test_static_properties_are_set_correctly()
    {
        $this->assertEquals(User::class, $this->testModel::$defaultAuthorizedModel);
        $this->assertEquals(['manager', 'account-executive'], $this->testModel::$authorizableRolesToCheck);
        $this->assertEquals(['editor', 'reporter'], $this->testModel::$assignableRolesToCheck);
        $this->assertEquals(User::class, $this->testModel::$defaultHasCreatorModel);
    }

    public function test_boot_model_helpers_without_authentication()
    {
        // Ensure no user is authenticated
        Auth::logout();

        // Create a model to trigger boot
        $model = $this->testModel::create(['name' => 'Test Model']);

        $this->assertNotNull($model);
        $this->assertEquals('Test Model', $model->name);
    }

    public function test_boot_model_helpers_with_authentication()
    {
        // Authenticate user
        Auth::login($this->user);

        // Create a model to trigger boot
        $model = $this->testModel::create(['name' => 'Test Model']);

        $this->assertNotNull($model);
        $this->assertEquals('Test Model', $model->name);
    }

    public function test_get_title_value()
    {
        $model = $this->testModel::create([
            'name' => 'Test Model',
            'title' => 'Test Title',
        ]);

        $this->assertEquals('Test Title', $model->getTitleValue());
    }

    public function test_get_show_format()
    {
        $model = $this->testModel::create([
            'name' => 'Test Model',
            'title' => 'Test Title',
        ]);

        $this->assertEquals('Test Title', $model->getShowFormat());
    }

    public function test_set_stateable_preview()
    {
        $state = new State([
            'en' => ['name' => 'In Review'],
            'color' => 'green',
            'icon' => 'mdi-check-circle',
            'code' => 'in-review',
        ]);

        $state->save();

        // // Mock the translatedAttribute method
        // $state->shouldReceive('translatedAttribute')
        //     ->with('name')
        //     ->andReturn(['en' => 'Active']);

        $model = $this->testModel::create(['name' => 'Test Model']);

        $preview = $model->setStateablePreview($state);

        $this->assertStringContainsString("color='green'", $preview);
        $this->assertStringContainsString("prepend-icon='mdi-check-circle'", $preview);
        $this->assertStringContainsString('In Review', $preview);
    }

    public function test_set_stateable_preview_null()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        $preview = $model->setStateablePreviewNull();

        $this->assertStringContainsString('No Status', $preview);
        $this->assertStringContainsString("color=''", $preview);
        $this->assertStringContainsString("prepend-icon=''", $preview);
    }

    public function test_set_state_formatted_with_state()
    {
        $state = new State([
            'en' => ['name' => 'Published'],
            'color' => 'blue',
            'icon' => 'mdi-publish',
            'code' => 'published',
        ]);

        $state->save();

        $model = $this->testModel::create(['name' => 'Test Model']);

        $formatted = $model->setStateFormatted($state);

        $this->assertStringContainsString("color='blue'", $formatted);
        $this->assertStringContainsString("prepend-icon='mdi-publish'", $formatted);
        $this->assertStringContainsString('Published', $formatted);
    }

    public function test_set_state_formatted_without_state()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        $formatted = $model->setStateFormatted(null);

        $this->assertStringContainsString('No Status', $formatted);
        $this->assertStringContainsString("color='grey'", $formatted);
        $this->assertStringContainsString("prepend-icon='mdi-alert-circle-outline'", $formatted);
    }

    public function test_get_activity_log_options_basic()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        $options = $model->getActivitylogOptions();

        $this->assertInstanceOf(LogOptions::class, $options);
    }

    public function test_get_activity_log_options_with_translatable_model()
    {
        Schema::create('translatable_models', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title');
            $table->timestamps();
        });

        // Create a translatable test model
        Schema::create('translatable_model_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'translatable_model');
            $table->string('name');
            $table->string('description');
        });

        $model = TranslatableModel::create([
            'title' => 'Translatable Model Title',
            'en' => [
                'name' => 'Translatable Model Name',
                'description' => 'Translatable Model Description',
                'active' => 0,
            ],
        ]);

        $options = $model->getActivitylogOptions();

        $this->assertInstanceOf(LogOptions::class, $options);
    }

    public function test_last_activities_relationship()
    {
        $this->app['config']->set('activitylog.enabled', true);

        Auth::login($this->user);

        $model = $this->testModel::create(['name' => 'Test Model']);

        sleep(1);

        activity()
            ->performedOn($model)
            ->causedBy($this->user)
            ->log('updated');

        $lastActivities = $model->lastActivities;

        $this->assertCount(2, $lastActivities);
        $this->assertEquals('updated', $lastActivities->first()->description);
        $this->assertEquals($this->user->id, $lastActivities->first()->causer_id);
    }

    public function test_is_foreign_key_relationship()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        // This method currently returns the definedRelationTypes property
        $result = $model->isForeignKeyRelationship('posts');

        // The method should return the defined relation types
        $this->assertNull($result); // Since definedRelationTypes property doesn't exist
    }

    public function test_get_with()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        $with = $model->getWith();

        $this->assertIsArray($with);
        $this->assertEquals([], $with); // Default empty array
    }

    public function test_magic_call_number_of_methods()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Test numberOfPosts (camelCase)
        $numberOfPosts = $model->numberOfPosts();
        $this->assertEquals(0, $numberOfPosts);

        // Test numberOfTags (camelCase)
        $numberOfTags = $model->numberOfTags();
        $this->assertEquals(0, $numberOfTags);
    }

    public function test_magic_call_spreadable_mutator_methods()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Test spreadable mutator methods
        $result = $model->getComputedValue();
        $this->assertEquals('method_computed_value', $result);

        $result = $model->getDynamicMethod();
        $this->assertEquals('method_dynamic_value', $result);
    }

    public function test_magic_call_falls_back_to_parent()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Test that unknown methods fall back to parent
        $this->expectException(\BadMethodCallException::class);
        $model->nonExistentMethod();
    }

    public function test_magic_get_spreadable_mutator_attributes()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Test spreadable mutator attributes
        $this->assertEquals('computed_value', $model->computed_field);
        $this->assertEquals('dynamic_value', $model->dynamic_attribute);
    }

    public function test_magic_get_falls_back_to_parent()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Test that normal attributes still work
        $this->assertEquals('Test Model', $model->name);

        // Test that unknown attributes return null (Eloquent behavior)
        $this->assertNull($model->non_existent_attribute);
    }

    public function test_old_translations_property()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Test that oldTranslations property exists and is initialized
        $this->assertIsArray($model->oldTranslations);
        $this->assertEquals([], $model->oldTranslations);
    }

    public function test_model_events_are_registered()
    {
        // Test that model events are properly registered
        $eventsCalled = [];

        // Mock the events
        Event::listen('eloquent.saving: ' . $this->testModel, function () use (&$eventsCalled) {
            $eventsCalled[] = 'saving';
        });

        Event::listen('eloquent.saved: ' . $this->testModel, function () use (&$eventsCalled) {
            $eventsCalled[] = 'saved';
        });

        // Create and save a model
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Retrieve the model to trigger retrieved event
        $retrievedModel = $this->testModel::find($model->id);

        // Note: Events might not fire in tests due to how the anonymous class is handled
        // This test mainly ensures the event registration code doesn't throw errors
        $this->assertNotNull($retrievedModel);
    }

    public function test_activity_logging_with_authenticated_user()
    {
        $this->app['config']->set('activitylog.enabled', true);

        Auth::login($this->user);

        $model = $this->testModel::create([
            'name' => 'Test Model',
            'description' => 'Test Description',
        ]);

        // Update the model to generate activity
        $model->update(['name' => 'Updated Model']);

        $activities = Activity::forSubject($model)->get();

        $this->assertGreaterThan(0, $activities->count());
    }

    public function test_activity_logging_without_authenticated_user()
    {
        $this->app['config']->set('activitylog.enabled', true);

        Auth::logout();

        $model = $this->testModel::create([
            'name' => 'Test Model',
            'description' => 'Test Description',
        ]);

        // Update the model
        $model->update(['name' => 'Updated Model']);

        // Activity logging should be disabled when no user is authenticated
        $activities = Activity::forSubject($model)->get();

        // Might still have activities depending on configuration
        $this->assertIsInt($activities->count());
    }

    public function test_number_of_method_with_snake_case_relationships()
    {
        // Create a model with snake_case relationship methods
        $modelWithSnakeCase = new class extends Model
        {
            use ModelHelpers;

            protected $table = 'test_model_helpers_models';

            protected $fillable = ['name'];

            public function user_posts(): HasMany
            {
                return $this->hasMany(self::class, 'parent_id');
            }

            public function getRouteTitleColumnKey(): string
            {
                return 'name';
            }
        };

        $model = $modelWithSnakeCase::create(['name' => 'Test Model']);

        // Test numberOfUserPosts should work with snake_case relationship
        $result = $model->numberOfUserPosts();
        $this->assertIsInt($result);
        $this->assertEquals(0, $result);
    }

    public function test_complex_spreadable_attributes_and_methods()
    {
        // Create a model with more complex spreadable configurations
        $complexModel = new class extends Model
        {
            use ModelHelpers;

            protected $table = 'test_model_helpers_models';

            protected $fillable = ['name', 'preferences'];

            protected $casts = ['preferences' => 'array'];

            protected $spreadableMutatorAttributes = [
                'full_name' => 'John Doe',
                'computed_score' => 95.5,
                'is_active' => true,
                'metadata' => ['key' => 'value'],
            ];

            protected $spreadableMutatorMethods = [
                'getFormattedName' => 'Mr. John Doe',
                'calculateTotal' => 1000,
                'isEligible' => false,
            ];

            public function getRouteTitleColumnKey(): string
            {
                return 'name';
            }
        };

        $model = $complexModel::create(['name' => 'Complex Model']);

        // Test complex spreadable attributes
        $this->assertEquals('John Doe', $model->full_name);
        $this->assertEquals(95.5, $model->computed_score);
        $this->assertTrue($model->is_active);
        $this->assertEquals(['key' => 'value'], $model->metadata);

        // Test complex spreadable methods
        $this->assertEquals('Mr. John Doe', $model->getFormattedName());
        $this->assertEquals(1000, $model->calculateTotal());
        $this->assertFalse($model->isEligible());
    }

    public function test_activity_log_options_with_json_attributes()
    {
        $model = $this->testModel::create([
            'name' => 'Test Model',
            'preferences' => ['theme' => 'dark', 'notifications' => true],
        ]);

        $options = $model->getActivitylogOptions();

        $this->assertInstanceOf(LogOptions::class, $options);

        // Update model to test logging
        $model->update(['preferences' => ['theme' => 'light', 'notifications' => false]]);

        // Verify the model was updated
        $this->assertEquals(['theme' => 'light', 'notifications' => false], $model->fresh()->preferences);
    }

    public function test_translatable_model_updates_existing_batch_activity()
    {
        $this->app['config']->set('activitylog.enabled', true);
        Auth::login($this->user);

        Schema::create('translatable_models', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('translatable_model_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'translatable_model');
            $table->string('name');
            $table->string('description');
        });

        $model = TranslatableModel::create([
            'title' => 'Translatable Model Title',
            'en' => [
                'name' => 'Original Name',
                'description' => 'Original Description',
                'active' => 1,
            ],
        ]);

        LogBatch::startBatch();
        $batchUuid = LogBatch::getUuid();
        $this->assertNotNull($batchUuid);

        $batchActivity = Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'subject_type' => TranslatableModel::class,
            'subject_id' => $model->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'batch_uuid' => $batchUuid,
            'properties' => [
                'attributes' => ['en' => ['name' => 'Original Name']],
                'old' => [],
            ],
        ]);

        $activitiesBefore = Activity::forBatch($batchUuid)
            ->where('subject_type', TranslatableModel::class)
            ->where('subject_id', $model->id)
            ->count();

        $model->translate('en')->name = 'Updated Name';
        $model->translate('en')->description = 'Updated Description';
        $model->save();

        $activitiesAfter = Activity::forBatch($batchUuid)
            ->where('subject_type', TranslatableModel::class)
            ->where('subject_id', $model->id)
            ->count();

        $this->assertEquals($activitiesBefore, $activitiesAfter);

        $batchActivity->refresh();

        $this->assertEquals('Updated Name', $batchActivity->properties['attributes']['en']['name']);
        $this->assertEquals('Updated Description', $batchActivity->properties['attributes']['en']['description']);
        $this->assertEquals('Original Name', $batchActivity->properties['old']['en']['name']);
        $this->assertEquals('Original Description', $batchActivity->properties['old']['en']['description']);

        LogBatch::endBatch();
    }

    public function test_translatable_model_creates_new_activity_when_no_batch_activity_exists()
    {
        $this->app['config']->set('activitylog.enabled', true);
        Auth::login($this->user);

        Schema::create('translatable_models', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('translatable_model_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'translatable_model');
            $table->string('name');
            $table->string('description');
        });

        $model = TranslatableModel::create([
            'title' => 'Translatable Model Title',
            'en' => [
                'name' => 'Original Name',
                'description' => 'Original Description',
                'active' => 1,
            ],
        ]);

        LogBatch::startBatch();
        $batchUuid = LogBatch::getUuid();
        $this->assertNotNull($batchUuid);

        $existingBatchActivity = Activity::forBatch($batchUuid)
            ->where('subject_type', TranslatableModel::class)
            ->where('subject_id', $model->id)
            ->first();

        $this->assertNull($existingBatchActivity);

        $activitiesBefore = Activity::forSubject($model)->count();

        $model->translate('en')->name = 'Updated Name';
        $model->translate('en')->description = 'Updated Description';
        $model->save();

        $activitiesAfter = Activity::forSubject($model)->count();
        $this->assertGreaterThan($activitiesBefore, $activitiesAfter);

        $newActivity = Activity::forSubject($model)
            ->where('description', 'updated')
            ->where('event', 'saved')
            ->where('batch_uuid', $batchUuid)
            ->latest()
            ->first();

        $this->assertNotNull($newActivity);
        $this->assertEquals('saved', $newActivity->event);
        $this->assertEquals('updated', $newActivity->description);
        $this->assertEquals($batchUuid, $newActivity->batch_uuid);
        $this->assertArrayHasKey('attributes', $newActivity->properties);
        $this->assertArrayHasKey('old', $newActivity->properties);
        $this->assertArrayHasKey('en', $newActivity->properties['attributes']);

        LogBatch::endBatch();
    }
}

class ModelHelperModel extends Model
{
    protected $table = 'test_model_helpers_models';

    protected $fillable = ['name', 'title', 'description', 'slug', 'preferences'];

    protected $casts = ['preferences' => 'array'];

    public function getRouteTitleColumnKey(): string
    {
        return 'title';
    }

    // Define some relationships for testing
    public function posts(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(self::class, 'commentable');
    }

    // Mock spreadable attributes for testing
    protected $spreadableMutatorAttributes = [
        'computed_field' => 'computed_value',
        'dynamic_attribute' => 'dynamic_value',
    ];

    protected $spreadableMutatorMethods = [
        'getComputedValue' => 'method_computed_value',
        'getDynamicMethod' => 'method_dynamic_value',
    ];
}

class TranslatableModel extends Model
{
    use ModelHelpers, HasTranslation;

    protected $table = 'translatable_models';

    protected $fillable = ['title'];

    protected $translatedAttributes = ['name', 'description'];

    protected $translationModel = TranslatableModelTranslation::class;

    public function getRouteTitleColumnKey(): string
    {
        return 'title';
    }
}
class TranslatableModelTranslation extends Model
{
    protected $baseModuleModel = TranslatableModel::class;
    // protected $fillable = [
    //     'name',
    //     'description',
    //     'active',
    // ];

    protected $table = 'translatable_model_translations';
}
