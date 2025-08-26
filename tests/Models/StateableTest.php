<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Unusualify\Modularity\Entities\State;
use Unusualify\Modularity\Entities\Stateable;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class StateableTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_stateable()
    {
        $stateable = new Stateable;
        $this->assertEquals(modularityConfig('tables.stateables', 'um_stateables'), $stateable->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'state_id',
            'stateable_id',
            'stateable_type',
        ];

        $stateable = new Stateable;
        $this->assertEquals($expectedFillable, $stateable->getFillable());
    }

    public function test_no_timestamps()
    {
        $stateable = new Stateable;
        $this->assertFalse($stateable->timestamps);
    }

    public function test_create_stateable()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'test-state',
            'icon' => 'mdi-test',
            'color' => 'info',
        ]);

        $user = User::factory()->create();

        $stateable = Stateable::create([
            'state_id' => $state->id,
            'stateable_id' => $user->id,
            'stateable_type' => get_class($user),
        ]);

        $this->assertEquals($state->id, $stateable->state_id);
        $this->assertEquals($user->id, $stateable->stateable_id);
        $this->assertEquals(get_class($user), $stateable->stateable_type);
    }

    public function test_update_stateable()
    {
        $state1 = State::create([
            'published' => 1,
            'code' => 'initial-state',
            'icon' => 'mdi-initial',
            'color' => 'warning',
        ]);

        $state2 = State::create([
            'published' => 1,
            'code' => 'updated-state',
            'icon' => 'mdi-updated',
            'color' => 'success',
        ]);

        $user = User::factory()->create();

        $stateable = Stateable::create([
            'state_id' => $state1->id,
            'stateable_id' => $user->id,
            'stateable_type' => get_class($user),
        ]);

        $stateable->update([
            'state_id' => $state2->id,
        ]);

        $this->assertEquals($state2->id, $stateable->state_id);
    }

    public function test_delete_stateable()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'delete-test',
            'icon' => 'mdi-delete',
            'color' => 'error',
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $stateable1 = Stateable::create([
            'state_id' => $state->id,
            'stateable_id' => $user1->id,
            'stateable_type' => get_class($user1),
        ]);

        $stateable2 = Stateable::create([
            'state_id' => $state->id,
            'stateable_id' => $user2->id,
            'stateable_type' => get_class($user2),
        ]);

        $this->assertCount(2, Stateable::all());

        $stateable2->delete();

        $this->assertFalse(Stateable::all()->contains('state_id', $stateable2->id));
        $this->assertTrue(Stateable::all()->contains('state_id', $stateable1->id));
        $this->assertCount(2, Stateable::all());
    }

    public function test_state_relationship()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'relationship-test',
            'icon' => 'mdi-relationship',
            'color' => 'primary',
        ]);

        $user = User::factory()->create();

        $stateable = Stateable::create([
            'state_id' => $state->id,
            'stateable_id' => $user->id,
            'stateable_type' => get_class($user),
        ]);

        $relation = $stateable->state();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(State::class, $stateable->state);
        $this->assertEquals($state->id, $stateable->state->id);
        $this->assertEquals('relationship-test', $stateable->state->code);
    }

    public function test_extends_base_model()
    {
        $stateable = new Stateable;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $stateable);
    }

    public function test_pivot_table_functionality()
    {
        // Test that Stateable acts as a pivot table between State and polymorphic models
        $state1 = State::create([
            'published' => 1,
            'code' => 'draft',
            'icon' => 'mdi-file-document-outline',
            'color' => 'warning',
        ]);

        $state2 = State::create([
            'published' => 1,
            'code' => 'published',
            'icon' => 'mdi-check-circle-outline',
            'color' => 'success',
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create multiple stateable relationships
        $stateable1 = Stateable::create([
            'state_id' => $state1->id,
            'stateable_id' => $user1->id,
            'stateable_type' => get_class($user1),
        ]);

        $stateable2 = Stateable::create([
            'state_id' => $state2->id,
            'stateable_id' => $user2->id,
            'stateable_type' => get_class($user2),
        ]);

        // Test querying by state
        $draftStateables = Stateable::where('state_id', $state1->id)->get();
        $publishedStateables = Stateable::where('state_id', $state2->id)->get();

        $this->assertCount(1, $draftStateables);
        $this->assertCount(1, $publishedStateables);
        $this->assertEquals($user1->id, $draftStateables->first()->stateable_id);
        $this->assertEquals($user2->id, $publishedStateables->first()->stateable_id);
    }

    public function test_multiple_models_with_same_state()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'common-state',
            'icon' => 'mdi-common',
            'color' => 'info',
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Both users can have the same state
        $stateable1 = Stateable::create([
            'state_id' => $state->id,
            'stateable_id' => $user1->id,
            'stateable_type' => get_class($user1),
        ]);

        $stateable2 = Stateable::create([
            'state_id' => $state->id,
            'stateable_id' => $user2->id,
            'stateable_type' => get_class($user2),
        ]);

        $commonStateStateables = Stateable::where('state_id', $state->id)->get();

        $this->assertCount(2, $commonStateStateables);
        $this->assertEquals($state->id, $stateable1->state->id);
        $this->assertEquals($state->id, $stateable2->state->id);
        $this->assertEquals('common-state', $stateable1->state->code);
        $this->assertEquals('common-state', $stateable2->state->code);
    }

    public function test_polymorphic_relationships()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'polymorphic-test',
            'icon' => 'mdi-shape',
            'color' => 'purple',
        ]);

        $user = User::factory()->create();

        $stateable = Stateable::create([
            'state_id' => $state->id,
            'stateable_id' => $user->id,
            'stateable_type' => get_class($user),
        ]);

        // Test polymorphic type storage
        $this->assertEquals(get_class($user), $stateable->stateable_type);
        $this->assertEquals($user->id, $stateable->stateable_id);

        // Test querying by polymorphic type
        $userStateables = Stateable::where('stateable_type', get_class($user))->get();
        $this->assertTrue($userStateables->contains('stateable_id', $user->id));
    }

    public function test_state_transition_simulation()
    {
        $draftState = State::create([
            'published' => 1,
            'code' => 'draft',
            'icon' => 'mdi-file-document-outline',
            'color' => 'warning',
        ]);

        $reviewState = State::create([
            'published' => 1,
            'code' => 'in-review',
            'icon' => 'mdi-information-outline',
            'color' => 'info',
        ]);

        $publishedState = State::create([
            'published' => 1,
            'code' => 'published',
            'icon' => 'mdi-check-circle-outline',
            'color' => 'success',
        ]);

        $user = User::factory()->create();

        // Simulate state transition: Draft -> Review -> Published
        $stateable = Stateable::create([
            'state_id' => $draftState->id,
            'stateable_id' => $user->id,
            'stateable_type' => get_class($user),
        ]);

        // Transition to review
        $stateable->update(['state_id' => $reviewState->id]);
        $this->assertEquals($reviewState->id, $stateable->state_id);
        $this->assertEquals('in-review', $stateable->state->code);

        // Transition to published
        $stateable->update(['state_id' => $publishedState->id]);
        $this->assertEquals($publishedState->id, $stateable->state_id);
        $this->assertEquals('published', $stateable->state()->first()->code);
    }

    public function test_stateable_with_state_configuration()
    {
        app()->config->set('translatable.locales', ['en', 'tr']);
        // Test creating stateable with state that has full configuration like PressRelease
        $state = State::create([
            'published' => 1,
            'code' => 'in-progress',
            'icon' => 'mdi-progress-check',
            'color' => 'blue-darken-2',
            'en' => [
                'name' => 'In Progress',
                'active' => 1,
            ],
            'tr' => [
                'name' => 'İşlem Devam Ediyor',
                'active' => 1,
            ],
        ]);

        $user = User::factory()->create();

        $stateable = Stateable::create([
            'state_id' => $state->id,
            'stateable_id' => $user->id,
            'stateable_type' => get_class($user),
        ]);

        // Verify the state relationship includes all configuration
        $this->assertEquals('in-progress', $stateable->state->code);
        $this->assertEquals('mdi-progress-check', $stateable->state->icon);
        $this->assertEquals('blue-darken-2', $stateable->state->color);
        $this->assertEquals('In Progress', $stateable->state->translate('en')->name ?? null);
        $this->assertEquals('İşlem Devam Ediyor', $stateable->state->translate('tr')->name ?? null);
    }
}
