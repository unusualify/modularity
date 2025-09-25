<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Modules\SystemNotification\Events\StateableUpdated;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\State;
use Unusualify\Modularity\Entities\Stateable;
use Unusualify\Modularity\Entities\Traits\HasStateable;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasStateableTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $testModel;

    protected $stateModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table for stateable models
        Schema::create('test_stateable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        // Create test model class
        $this->testModel = TestStateableModel::class;
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_stateable_models');
        parent::tearDown();
    }

    public function test_trait_initializes_correctly()
    {
        $model = new $this->testModel;

        // Test that appended attributes are set
        $appends = $model->getAppends();
        $this->assertContains('state_formatted', $appends);
        $this->assertContains('states', $appends);

        // Test that fillable attributes are merged
        $fillable = $model->getFillable();
        $this->assertContains('initial_stateable', $fillable);
        $this->assertContains('stateable_id', $fillable);
    }

    public function test_get_states_returns_collection()
    {
        // Create some states based on default states
        State::create([
            'code' => 'draft',
            'icon' => '$edit',
            'color' => 'warning',
            'en' => ['name' => 'Draft', 'active' => true],
        ]);

        State::create([
            'code' => 'published',
            'icon' => '$publish',
            'color' => 'success',
            'en' => ['name' => 'Published', 'active' => true],
        ]);

        $states = $this->testModel::getStates();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $states);
        $this->assertCount(2, $states);
        $this->assertEquals('draft', $states->first()->code);
        $this->assertEquals('published', $states->last()->code);
    }

    public function test_states_attribute_accessor()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        $states = $model->states;

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $states);
        $this->assertCount(2, $states);
    }

    public function test_state_relationship()
    {
        // Create a state
        $state = State::create([
            'code' => 'draft',
            'icon' => '$edit',
            'color' => 'warning',
            'en' => ['name' => 'Draft', 'active' => true],
        ]);

        // Create a model
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Create a stateable record
        Stateable::create([
            'stateable_id' => $model->id,
            'stateable_type' => get_class($model),
            'state_id' => $state->id,
        ]);

        // Test the state relationship
        $modelState = $model->state;

        $this->assertInstanceOf(State::class, $modelState);
        $this->assertEquals($state->id, $modelState->id);
        $this->assertEquals('draft', $modelState->code);
    }

    public function test_stateable_relationship()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        $this->assertInstanceOf(Stateable::class, $model->stateable);
        $this->assertEquals($model->id, $model->stateable->stateable_id);
        $this->assertEquals(get_class($model), $model->stateable->stateable_type);
        $this->assertEquals(1, $model->stateable->state_id);
    }

    public function test_hydrate_state()
    {
        $state = State::create([
            'code' => 'draft',
            'icon' => '$info',
            'color' => 'info',
            'en' => ['name' => 'Draft', 'active' => true],
        ]);

        $model = $this->testModel::create(['name' => 'Test Model']);

        $hydratedState = $model->hydrateState($state);

        $this->assertInstanceOf(State::class, $hydratedState);
        $this->assertEquals('$edit', $hydratedState->icon); // Should be updated from config
        $this->assertEquals('warning', $hydratedState->color); // Should be updated from config
    }

    public function test_get_state_attribute()
    {
        $state = State::create([
            'code' => 'draft',
            'icon' => '$info',
            'color' => 'info',
            'en' => ['name' => 'Draft', 'active' => true],
        ]);

        $model = $this->testModel::create(['name' => 'Test Model']);

        Stateable::create([
            'stateable_id' => $model->id,
            'stateable_type' => get_class($model),
            'state_id' => $state->id,
        ]);

        $modelState = $model->getStateAttribute();

        $this->assertInstanceOf(State::class, $modelState);
        $this->assertEquals('$edit', $modelState->icon); // Should be hydrated
        $this->assertEquals('warning', $modelState->color); // Should be hydrated
    }

    public function test_get_state_attribute_returns_null_when_no_state()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        $state = $model->getStateAttribute();

        $this->assertInstanceOf(State::class, $state);
        $this->assertEquals('draft', $state->code);
    }

    public function test_stateable_code_attribute()
    {
        $model = $this->testModel::create(['name' => 'Test Model', 'initial_stateable' => 'published']);

        $this->assertEquals('published', $model->stateable_code);
    }

    public function test_stateable_code_attribute_returns_null_when_no_state()
    {
        $model = $this->testModel::create(['name' => 'Test Model']);

        $this->assertEquals('draft', $model->stateable_code);
    }

    public function test_state_formatted_attribute_with_state()
    {
        $model = $this->testModel::create(['name' => 'Test Model', 'initial_stateable' => 'published']);

        $formatted = $model->state_formatted;

        $this->assertStringContainsString("color='success'", $formatted);
        $this->assertStringContainsString("prepend-icon='\$publish'", $formatted);
        $this->assertStringContainsString('Published', $formatted);
    }

    public function test_format_stateable_state_with_string()
    {
        $formatted = $this->testModel::formatStateableState('draft');

        $this->assertIsArray($formatted);
        $this->assertEquals('draft', $formatted['code']);
        $this->assertEquals('Draft', $formatted['en']['name']);
        $this->assertTrue($formatted['en']['active']);
        $this->assertEquals('$info', $formatted['icon']);
        $this->assertEquals('info', $formatted['color']);
    }

    public function test_format_stateable_state_with_array()
    {
        $stateData = [
            'code' => 'published',
            'name' => 'Published Article',
            'icon' => '$publish',
            'color' => 'success',
        ];

        $formatted = $this->testModel::formatStateableState($stateData);

        $this->assertIsArray($formatted);
        $this->assertEquals('published', $formatted['code']);
        $this->assertEquals('Published Article', $formatted['en']['name']);
        $this->assertEquals('$publish', $formatted['icon']);
        $this->assertEquals('success', $formatted['color']);
    }

    public function test_format_stateable_state_throws_exception_for_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('State cannot be empty string');

        $this->testModel::formatStateableState('');
    }

    public function test_format_stateable_state_throws_exception_for_array_without_code()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('State must have a code attribute');

        $this->testModel::formatStateableState(['name' => 'Test']);
    }

    public function test_get_default_state()
    {
        $defaultState = $this->testModel::getDefaultState();

        $this->assertIsArray($defaultState);
        $this->assertEquals('$edit', $defaultState['icon']);
        $this->assertEquals('warning', $defaultState['color']);
    }

    public function test_get_initial_state()
    {
        $initialState = $this->testModel::getInitialState();

        $this->assertIsArray($initialState);
        $this->assertEquals('draft', $initialState['code']);
        $this->assertEquals('Draft', $initialState['en']['name']);
    }

    public function test_get_default_states()
    {
        $defaultStates = $this->testModel::getDefaultStates();

        $this->assertIsArray($defaultStates);
        $this->assertCount(2, $defaultStates);

        $this->assertEquals('draft', $defaultStates[0]['code']);
        $this->assertEquals('published', $defaultStates[1]['code']);
    }

    public function test_get_raw_state_configuration()
    {
        $config = $this->testModel::getRawStateConfiguration('draft');

        $this->assertIsArray($config);
        $this->assertEquals('draft', $config['code']);
        $this->assertEquals('$edit', $config['icon']);
        $this->assertEquals('warning', $config['color']);
    }

    public function test_get_state_configuration()
    {
        $config = $this->testModel::getStateConfiguration('published');

        $this->assertIsArray($config);
        $this->assertEquals('$publish', $config['icon']);
        $this->assertEquals('success', $config['color']);
    }

    public function test_create_non_existent_states_on_model_creation()
    {
        // Ensure no states exist
        State::truncate();

        $model = $this->testModel::create(['name' => 'Test Model']);

        // Check that states were created
        $states = State::all();
        $this->assertCount(2, $states);

        $draftState = State::where('code', 'draft')->first();
        $publishedState = State::where('code', 'published')->first();

        $this->assertNotNull($draftState);
        $this->assertNotNull($publishedState);

        // Check that the model has the initial state
        $this->assertNotNull($model->stateable);
        $this->assertEquals($draftState->id, $model->stateable->state_id);
    }

    public function test_custom_initial_stateable()
    {
        State::truncate();

        $model = $this->testModel::create([
            'name' => 'Test Model',
            'initial_stateable' => 'published',
        ]);

        $publishedState = State::where('code', 'published')->first();

        $this->assertNotNull($model->stateable);
        $this->assertEquals($publishedState->id, $model->stateable->state_id);
    }

    public function test_stateable_updating_check()
    {
        $draftState = State::create([
            'code' => 'draft',
            'icon' => '$edit',
            'color' => 'warning',
            'en' => ['name' => 'Draft', 'active' => true],
        ]);

        $publishedState = State::create([
            'code' => 'published',
            'icon' => '$publish',
            'color' => 'success',
            'en' => ['name' => 'Published', 'active' => true],
        ]);

        $model = $this->testModel::create(['name' => 'Test Model']);

        // Create initial stateable
        Stateable::create([
            'stateable_id' => $model->id,
            'stateable_type' => get_class($model),
            'state_id' => $draftState->id,
        ]);

        // Update the stateable_id to trigger state change
        $model->stateable_id = $publishedState->id;
        $model->save();

        // Refresh model to get updated state
        $model->refresh();

        $this->assertEquals($publishedState->id, $model->stateable->state_id);
    }

    public function test_stateable_updated_event_is_dispatched()
    {
        Event::fake([StateableUpdated::class]);

        $draftState = State::create([
            'code' => 'draft',
            'icon' => '$edit',
            'color' => 'warning',
            'en' => ['name' => 'Draft', 'active' => true],
        ]);

        $publishedState = State::create([
            'code' => 'published',
            'icon' => '$publish',
            'color' => 'success',
            'en' => ['name' => 'Published', 'active' => true],
        ]);

        $model = $this->testModel::create(['name' => 'Test Model']);

        // Create initial stateable
        Stateable::create([
            'stateable_id' => $model->id,
            'stateable_type' => get_class($model),
            'state_id' => $draftState->id,
        ]);

        // Update the state
        $model->stateable_id = $publishedState->id;
        $model->save();

        Event::assertDispatched(StateableUpdated::class, function ($event) use ($model, $publishedState, $draftState) {
            return $event->model->id === $model->id &&
                   $event->newState->id === $publishedState->id &&
                   $event->oldState->id === $draftState->id;
        });
    }

    public function test_sync_state_data()
    {
        // Ensure no states exist
        State::truncate();

        $newStates = $this->testModel::syncStateData();

        $this->assertCount(2, $newStates);

        $states = State::all();
        $this->assertCount(2, $states);

        $draftState = State::where('code', 'draft')->first();
        $publishedState = State::where('code', 'published')->first();

        $this->assertNotNull($draftState);
        $this->assertNotNull($publishedState);
    }

    public function test_sync_state_data_only_creates_missing_states()
    {
        // Create only draft state
        State::create([
            'code' => 'draft',
            'icon' => '$edit',
            'color' => 'warning',
            'en' => ['name' => 'Draft', 'active' => true],
        ]);

        $newStates = $this->testModel::syncStateData();

        $this->assertCount(1, $newStates); // Only published should be created
        $this->assertEquals('published', $newStates[0]->code);

        $allStates = State::all();
        $this->assertCount(2, $allStates);
    }

    public function test_clear_stateable_fillable()
    {
        State::create([
            'code' => 'draft',
            'icon' => '$edit',
            'color' => 'warning',
            'en' => ['name' => 'Draft', 'active' => true],
        ]);

        $model = $this->testModel::create([
            'name' => 'Test Model',
            'initial_stateable' => 'draft',
            'stateable_id' => 1,
        ]);

        // These attributes should be cleared after saving
        $this->assertNull($model->getAttributeValue('initial_stateable'));
        $this->assertNull($model->getAttributeValue('stateable_id'));
    }

    public function test_get_stateable_translation_languages()
    {
        $languages = $this->testModel::getStateableTranslationLanguages();

        $this->assertIsArray($languages);
        $this->assertContains(app()->getLocale(), $languages);
    }

    public function test_get_state_model()
    {
        $stateModel = $this->testModel::getStateModel();

        $this->assertEquals('Modules\SystemUtility\Entities\State', $stateModel);
    }

    public function test_get_default_state_with_invalid_default_state_property()
    {
        // Create a model with invalid $default_state property
        $invalidModel = new class extends Model
        {
            use HasStateable;

            protected $table = 'test_stateable_models';

            protected $fillable = ['name', 'title', 'initial_stateable', 'stateable_id'];

            // Invalid: not an array
            protected static $default_state = 'invalid_string';

            protected static $default_states = [
                [
                    'code' => 'draft',
                    'name' => 'Draft',
                    'icon' => '$edit',
                    'color' => 'warning',
                ],
            ];
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default state must be an array');

        $invalidModel::getDefaultState();
    }

    public function test_get_default_state_with_integer_default_state_property()
    {
        // Create a model with integer $default_state property
        $intModel = new class extends Model
        {
            use HasStateable;

            protected $table = 'test_stateable_models';

            protected $fillable = ['name', 'title', 'initial_stateable', 'stateable_id'];

            // Invalid: integer value
            protected static $default_state = 123;

            protected static $default_states = [
                [
                    'code' => 'draft',
                    'name' => 'Draft',
                    'icon' => '$edit',
                    'color' => 'warning',
                ],
            ];
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default state must be an array');

        $intModel::getDefaultState();
    }

    public function test_get_default_state_with_object_default_state_property()
    {
        // Create a model with object $default_state property
        $objModel = new class extends Model
        {
            use HasStateable;

            protected $table = 'test_stateable_models';

            protected $fillable = ['name', 'title', 'initial_stateable', 'stateable_id'];

            // Invalid: object value
            protected static $default_state;

            protected static $default_states = [
                [
                    'code' => 'draft',
                    'name' => 'Draft',
                    'icon' => '$edit',
                    'color' => 'warning',
                ],
            ];

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);
                self::$default_state = new \stdClass;
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default state must be an array');

        $objModel::getDefaultState();
    }

    public function test_get_default_state_with_valid_default_state_property()
    {
        // Create a model with valid $default_state property
        $validModel = new class extends Model
        {
            use HasStateable;

            protected $table = 'test_stateable_models';

            protected $fillable = ['name', 'title', 'initial_stateable', 'stateable_id'];

            // Valid: array value
            protected static $default_state = [
                'icon' => '$custom',
                'color' => 'custom',
                'extra_field' => 'should_be_filtered_out',
            ];

            protected static $default_states = [
                [
                    'code' => 'draft',
                    'name' => 'Draft',
                    'icon' => '$edit',
                    'color' => 'warning',
                ],
            ];
        };

        $defaultState = $validModel::getDefaultState();

        $this->assertIsArray($defaultState);
        $this->assertEquals('$custom', $defaultState['icon']);
        $this->assertEquals('custom', $defaultState['color']);
        $this->assertArrayNotHasKey('extra_field', $defaultState); // Should be filtered out by Arr::only
    }

    public function test_cached_states()
    {
        // First call should cache the states
        $states1 = $this->testModel::getStates();

        // Second call should use cached states
        $states2 = $this->testModel::getStates();

        $this->assertEquals($states1, $states2);
        $this->assertCount(2, $states1);
        $this->assertCount(2, $states2);
    }
}

class TestStateableModel extends Model
{
    use HasStateable;

    protected $table = 'test_stateable_models';

    protected $fillable = ['name', 'title', 'initial_stateable', 'stateable_id'];

    protected static $default_states = [
        [
            'code' => 'draft',
            'name' => 'Draft',
            'icon' => '$edit',
            'color' => 'warning',
        ],
        [
            'code' => 'published',
            'name' => 'Published',
            'icon' => '$publish',
            'color' => 'success',
        ],
    ];
}
