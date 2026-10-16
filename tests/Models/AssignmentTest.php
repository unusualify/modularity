<?php

namespace Unusualify\Modularous\Tests\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Modules\SystemNotification\Events\AssignmentCreated;
use Modules\SystemNotification\Events\AssignmentUpdated;
use Unusualify\Modularous\Entities\Assignment;
use Unusualify\Modularous\Entities\Enums\AssignmentStatus;
use Unusualify\Modularous\Entities\Scopes\AssignmentScopes;
use Unusualify\Modularous\Entities\Traits\HasFileponds;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Filepond;
use Unusualify\Modularous\Tests\ModelTestCase;

class AssignmentTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_assignment()
    {
        $assignment = new Assignment;
        $this->assertEquals(modularousConfig('tables.assignments', 'um_assignments'), $assignment->getTable());
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

    public function test_name_attributes_are_null_when_assignee_or_assigner_is_missing()
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
            'description' => 'Missing Users Test Assignment',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $assignee->forceDelete();
        $assigner->forceDelete();

        $assignment = Assignment::find($assignment->id);

        $this->assertNull($assignment->assignee);
        $this->assertNull($assignment->assigner);
        $this->assertNull($assignment->assignee_name);
        $this->assertNull($assignment->assignee_avatar);
        $this->assertNull($assignment->assigner_name);
        $this->assertIsArray($assignment->toArray());
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

        $this->assertEquals(__('Rejected'), $assignment->status_label);
        $this->assertEquals('text-error', $assignment->status_color);
        $this->assertEquals('mdi-close-circle-outline', $assignment->status_icon);
        $this->assertEquals('error', $assignment->status_icon_color);
    }

    public function test_has_fileponds_trait()
    {
        $this->assertTrue(in_array(
            HasFileponds::class,
            class_uses_recursive(new Assignment)
        ));
    }

    public function test_assignment_scopes_trait()
    {
        $this->assertTrue(in_array(
            AssignmentScopes::class,
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

    public function test_status_accessors_return_expected_values()
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
            'description' => 'Accessor check',
            'due_at' => Carbon::now()->addDay(),
        ]);

        $this->assertEquals(__('Pending'), $assignment->status_label);
        $this->assertEquals('text-warning', $assignment->status_color);
        $this->assertEquals('mdi-clock-outline', $assignment->status_icon);
        $this->assertEquals('info', $assignment->status_icon_color);
        $this->assertStringContainsString('v-icon', $assignment->status_vuetify_icon);
        $this->assertStringContainsString('mdi-clock-outline', $assignment->status_vuetify_icon);
        $this->assertStringContainsString('info', $assignment->status_vuetify_icon);
    }

    public function test_status_interval_description_uses_correct_times()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-01 12:00:00'));

        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        // Pending -> uses due_at
        $pending = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::PENDING,
            'description' => 'Pending',
            'due_at' => Carbon::now()->addDay(), // 2025-01-02
        ]);

        Storage::fake('local');

        $request = Request::create('/filepond', 'POST', [], [], [
            'attachments' => UploadedFile::fake()->image('anonymous.jpg', 64, 64),
        ]);
        $response = Filepond::createTemporaryFilepond($request);
        $this->assertEquals(200, $response->getStatusCode());
        $fileUniqueId = $response->getContent();

        Filepond::saveFile($pending, [['uuid' => $fileUniqueId]], 'attachments');

        $pending = $pending->fresh();
        $this->assertStringContainsString(__('Until'), $pending->status_interval_description);
        $this->assertStringContainsString('font-weight-bold text-blue-darken-1', $pending->status_interval_description);
        $this->assertEquals(1, $pending->attachments->count());

        // Completed -> uses completed_at
        $completed = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::COMPLETED,
            'description' => 'Completed',
            'due_at' => Carbon::now()->addDay(),
            'completed_at' => Carbon::now(),
        ]);
        $this->assertStringContainsString(__('Completed'), $completed->status_interval_description);
        $this->assertStringContainsString('font-weight-bold text-success', $completed->status_interval_description);

        // Cancelled -> uses updated_at
        $cancelled = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::CANCELLED,
            'description' => 'Cancelled',
            'due_at' => Carbon::now()->addDay(),
        ]);

        $cancelled->touch(); // ensure updated_at is present
        $this->assertStringContainsString(__('Cancelled'), $cancelled->status_interval_description);
        $this->assertStringContainsString('font-weight-bold text-warning', $cancelled->status_interval_description);

        $this->assertEquals($assignee->name, $cancelled->assignee_name);
        $this->assertEquals('/vendor/modularous/jpg/anonymous.jpg', $cancelled->assignee_avatar);
        $this->assertEquals($assigner->name, $cancelled->assigner_name);

        Carbon::setTestNow(); // clear
    }

    public function test_due_at_mutator_formats_value()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $dateString = '2025-02-03 14:15:16';

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Due at format',
            'due_at' => $dateString,
        ]);

        $this->assertEquals($dateString, $assignment->getRawOriginal('due_at'));
        $this->assertInstanceOf(Carbon::class, $assignment->due_at);
        $this->assertEquals($dateString, $assignment->due_at->format('Y-m-d H:i:s'));
    }

    public function test_assigner_is_auto_populated_when_authenticated()
    {
        $assignee = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        Auth::login($assigner);

        $assignment = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            // no explicit assigner fields
            'description' => 'Auth assigner',
            'due_at' => Carbon::now()->addDays(3),
        ]);

        $this->assertEquals($assigner->id, $assignment->assigner_id);
        $this->assertEquals(get_class($assigner), $assignment->assigner_type);
    }

    public function test_attachments_accessor_is_empty_by_default()
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
            'description' => 'No attachments yet',
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertIsIterable($assignment->attachments);
        $this->assertCount(0, $assignment->attachments);
    }

    public function test_assignment_scopes_filter_by_status_and_participants()
    {
        $assigneeA = User::factory()->create();
        $assigneeB = User::factory()->create();
        $assigner = User::factory()->create();
        $assignable = User::factory()->create();

        $pending = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assigneeA->id,
            'assignee_type' => get_class($assigneeA),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::PENDING,
            'description' => 'P',
            'due_at' => Carbon::now()->addDay(),
        ]);

        $completed = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assigneeB->id,
            'assignee_type' => get_class($assigneeB),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::COMPLETED,
            'description' => 'C',
            'due_at' => Carbon::now()->addDay(),
        ]);

        $cancelled = Assignment::create([
            'assignable_id' => $assignable->id,
            'assignable_type' => get_class($assignable),
            'assignee_id' => $assigneeB->id,
            'assignee_type' => get_class($assigneeB),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'status' => AssignmentStatus::CANCELLED,
            'description' => 'X',
            'due_at' => Carbon::now()->addDay(),
        ]);

        $this->assertCount(1, Assignment::query()->isPending()->get());
        $this->assertTrue(Assignment::query()->isPending()->first()->is($pending));

        $this->assertCount(1, Assignment::query()->isCompleted()->get());
        $this->assertTrue(Assignment::query()->isCompleted()->first()->is($completed));

        $this->assertCount(1, Assignment::query()->isCancelled()->get());
        $this->assertTrue(Assignment::query()->isCancelled()->first()->is($cancelled));

        $this->assertCount(3, Assignment::query()->isAssigneeType(get_class($assigneeA))->get());
        $this->assertCount(1, Assignment::query()->isAssignee($assigneeA)->get());
    }
}
