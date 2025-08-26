<?php

namespace Unusualify\Modularity\Tests\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Modules\SystemNotification\Events\AssignmentCreated;
use Modules\SystemNotification\Events\AssignmentUpdated;
use Unusualify\Modularity\Entities\Assignment;
use Unusualify\Modularity\Entities\Enums\AssignmentStatus;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class AssignmentTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_assignment()
    {
        $assignment = new Assignment;
        $this->assertEquals(modularityConfig('tables.assignments', 'um_assignments'), $assignment->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'assignable_id',
            'assignable_type',
            'assignee_id',
            'assignee_type',
            'assigner_id',
            'assigner_type',
            'status',
            'title',
            'description',
            'due_at',
            'accepted_at',
            'completed_at',
        ];

        $assignment = new Assignment;
        $this->assertEquals($expectedFillable, $assignment->getFillable());
    }

    public function test_casts()
    {
        $expectedCasts = [
            'status' => AssignmentStatus::class,
            'due_at' => 'datetime',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];

        $assignment = new Assignment;
        $casts = $assignment->getCasts();

        foreach ($expectedCasts as $key => $value) {
            $this->assertArrayHasKey($key, $casts);
            $this->assertEquals($value, $casts[$key]);
        }
    }

    public function test_appended_attributes()
    {
        $expectedAppends = [
            'assignee_name',
            'assignee_avatar',
            'assigner_name',
            'status_label',
            'status_color',
            'status_icon',
            'status_interval_description',
            'status_vuetify_icon',
            'attachments',
        ];

        $assignment = new Assignment;
        $appends = $assignment->getAppends();

        foreach ($expectedAppends as $append) {
            $this->assertContains($append, $appends);
        }
    }

    public function test_create_assignment()
    {
        Event::fake([
            AssignmentCreated::class,
        ]);

        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create(); // The entity being assigned

        // Create assignment with explicit assigner data (bypass the booted method for testing)
        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::PENDING,
            'description' => 'This is a test assignment description',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertEquals($assignable->id, $assignment->assignable_id);
        $this->assertEquals(get_class($assignable), $assignment->assignable_type);
        $this->assertEquals($assignee->id, $assignment->assignee_id);
        $this->assertEquals(get_class($assignee), $assignment->assignee_type);
        $this->assertEquals($assigner->id, $assignment->assigner_id);
        $this->assertEquals(get_class($assigner), $assignment->assigner_type);
        $this->assertEquals(AssignmentStatus::PENDING, $assignment->status);
        $this->assertEquals('This is a test assignment description', $assignment->description);
        $this->assertNotNull($assignment->due_at);

        Event::assertDispatched(AssignmentCreated::class);
    }

    public function test_update_assignment()
    {
        Event::fake([
            AssignmentCreated::class,
            AssignmentUpdated::class,
        ]);

        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::PENDING,
            'description' => 'Original description',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $assignment->update([
            'status' => AssignmentStatus::COMPLETED,
            'description' => 'Updated description',
            'completed_at' => Carbon::now(),
        ]);

        $this->assertEquals(AssignmentStatus::COMPLETED, $assignment->status);
        $this->assertEquals('Updated description', $assignment->description);
        $this->assertNotNull($assignment->completed_at);

        Event::assertDispatched(AssignmentCreated::class);
        Event::assertDispatched(AssignmentUpdated::class);
    }

    public function test_delete_assignment()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $assignment1 = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment 1 description',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $assignment2 = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment 2 description',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertCount(2, Assignment::all());

        $assignment2->delete();

        $this->assertFalse(Assignment::all()->contains('id', $assignment2->id));
        $this->assertTrue(Assignment::all()->contains('id', $assignment1->id));
        $this->assertCount(1, Assignment::all());
    }

    public function test_assignable_relationship()
    {
        $assignable = User::factory()->create();
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Relationship Test Assignment',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $relation = $assignment->assignable();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $assignment->assignable);
        $this->assertEquals($assignable->id, $assignment->assignable->id);
    }

    public function test_assignee_relationship()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignee Test Assignment',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $relation = $assignment->assignee();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $assignment->assignee);
        $this->assertEquals($assignee->id, $assignment->assignee->id);
    }

    public function test_assigner_relationship()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assigner Test Assignment',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $relation = $assignment->assigner();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $assignment->assigner);
        $this->assertEquals($assigner->id, $assignment->assigner->id);
    }

    public function test_assignment_status_enum()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::PENDING,
            'description' => 'Status Test Assignment',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertInstanceOf(AssignmentStatus::class, $assignment->status);

        $assignment->update(['status' => AssignmentStatus::COMPLETED]);
        $this->assertEquals(AssignmentStatus::COMPLETED, $assignment->status);

        $assignment->update(['status' => AssignmentStatus::REJECTED]);
        $this->assertEquals(AssignmentStatus::REJECTED, $assignment->status);
    }

    public function test_has_fileponds_trait()
    {
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Traits\HasFileponds::class,
            class_uses_recursive(new Assignment)
        ));
    }

    public function test_assignment_scopes_trait()
    {
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Scopes\AssignmentScopes::class,
            class_uses_recursive(new Assignment)
        ));
    }

    public function test_has_timestamps()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Timestamp Test Assignment',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertTrue($assignment->timestamps);
        $this->assertNotNull($assignment->created_at);
        $this->assertNotNull($assignment->updated_at);
    }

    public function test_datetime_casts_work_correctly()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();
        $dueDate = Carbon::now()->addDays(5);
        $acceptedDate = Carbon::now()->addHours(2);
        $completedDate = Carbon::now()->addDays(3);

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'DateTime Test Assignment',
            'due_at' => $dueDate,
            'accepted_at' => $acceptedDate,
            'completed_at' => $completedDate,
        ]);

        $this->assertInstanceOf(Carbon::class, $assignment->due_at);
        $this->assertInstanceOf(Carbon::class, $assignment->accepted_at);
        $this->assertInstanceOf(Carbon::class, $assignment->completed_at);
    }

    public function test_create_assignment_with_minimum_fields()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Minimal Assignment',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertNotNull($assignment->id);
        $this->assertEquals('Minimal Assignment', $assignment->description);
        $this->assertEquals($assignable->id, $assignment->assignable_id);
        $this->assertEquals(get_class($assignable), $assignment->assignable_type);
        $this->assertNull($assignment->accepted_at);
        $this->assertNull($assignment->completed_at);
    }

    public function test_assignment_workflow_like_controller()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        // Create an assignment with explicit assigner data (like controller would after auth)
        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Test assignment workflow',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        // Verify the assignment was created with correct data
        $this->assertEquals($assignable->id, $assignment->assignable_id);
        $this->assertEquals(get_class($assignable), $assignment->assignable_type);
        $this->assertEquals($assignee->id, $assignment->assignee_id);
        $this->assertEquals(get_class($assignee), $assignment->assignee_type);
        $this->assertEquals($assigner->id, $assignment->assigner_id);
        $this->assertEquals(get_class($assigner), $assignment->assigner_type);

        // Test status update (like in controller)
        $assignment->update([
            'status' => AssignmentStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        $this->assertEquals(AssignmentStatus::COMPLETED, $assignment->status);
        $this->assertNotNull($assignment->completed_at);

        // Test cancellation (like in controller)
        $assignment->updateQuietly([
            'status' => AssignmentStatus::CANCELLED,
        ]);

        $this->assertEquals(AssignmentStatus::CANCELLED, $assignment->status);
    }

    public function test_assignable_trait_relationships()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        // Add the Assignable trait functionality to the assignable model
        // Create assignments for the assignable entity
        $assignment1 = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'First assignment',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $assignment2 = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Second assignment',
            'due_at' => Carbon::now()->addDays(14),
        ]);

        // Test that we can query assignments by assignable
        $assignments = Assignment::where('assignable_id', $assignable->id)
            ->where('assignable_type', get_class($assignable))
            ->get();

        $this->assertCount(2, $assignments);
        $this->assertTrue($assignments->contains('id', $assignment1->id));
        $this->assertTrue($assignments->contains('id', $assignment2->id));
    }
}
