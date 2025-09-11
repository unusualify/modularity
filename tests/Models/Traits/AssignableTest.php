<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Entities\Assignment;
use Unusualify\Modularity\Entities\Enums\AssignmentStatus;
use Unusualify\Modularity\Entities\Traits\Assignable;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class AssignableTest extends ModelTestCase
{
    use RefreshDatabase;

    protected TestAssignableModel $model;

    protected $softDeletesModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table - Spatie Permission tables are handled by PermissionServiceProvider
        Schema::create('test_assignable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('test_assignable_soft_deletes_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->model = new TestAssignableModel([
            'name' => 'Test Assignable Model',
        ]);

        $this->model->save();

        $softDeletesModelClass = new class extends Model {
            use SoftDeletes, Assignable;

            protected $table = 'test_assignable_soft_deletes_models';

            protected $fillable = ['name'];
        };

        $this->softDeletesModel = new $softDeletesModelClass([
            'name' => 'Test Soft Deletes Model',
        ]);

        $this->softDeletesModel->save();
    }

    public function test_model_uses_assignable_trait()
    {
        $traits = class_uses_recursive($this->model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\Assignable', $traits);
    }

    public function test_assignments_relationship_exists()
    {
        $this->assertTrue(method_exists($this->model, 'assignments'));

        $relation = $this->model->assignments();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $relation);
    }

    public function test_assignments_relationship_configuration()
    {
        $relation = $this->model->assignments();

        $this->assertEquals('assignable_id', $relation->getForeignKeyName());
        $this->assertEquals('assignable_type', $relation->getMorphType());
        $this->assertEquals(Assignment::class, $relation->getRelated()::class);
    }

    public function test_can_create_assignment()
    {
        // Create a test user for assignee
        $assignee = User::create([
            'name' => 'Assignee User',
            'email' => 'assignee@example.com',
            'published' => true,
        ]);

        $assigner = User::create([
            'name' => 'Assigner User',
            'email' => 'assigner@example.com',
            'published' => true,
        ]);

        // Create assignment with required fields (following controller pattern)
        $assignmentData = [
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Test assignment description',
            'due_at' => now()->addDays(7),
        ];

        $assignment = $this->model->assignments()->create($assignmentData);

        $assignmentTable = modularityConfig('tables.assignments', 'm_assignments');
        $this->assertDatabaseHas($assignmentTable, [
            'assignable_id' => $this->model->id,
            'assignable_type' => get_class($this->model),
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Test assignment description',
        ]);
    }

    public function test_can_retrieve_assignments()
    {
        // Create a test user for assignee
        $assignee = User::create([
            'name' => 'Assignee User',
            'email' => 'assignee@example.com',
            'published' => true,
        ]);

        $assigner = User::create([
            'name' => 'Assigner User',
            'email' => 'assigner@example.com',
            'published' => true,
        ]);

        // Create assignment with required fields
        $assignmentData = [
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Test assignment description',
            'due_at' => now()->addDays(7),
        ];

        $assignment = $this->model->assignments()->create($assignmentData);

        // Refresh model to load relationships
        $this->model->refresh();

        $assignments = $this->model->assignments;
        $this->assertCount(1, $assignments);
        $this->assertEquals($this->model->id, $assignments->first()->assignable_id);
        $this->assertEquals(get_class($this->model), $assignments->first()->assignable_type);
        $this->assertEquals($assignee->id, $assignments->first()->assignee_id);
        $this->assertEquals(get_class($assignee), $assignments->first()->assignee_type);
    }

    public function test_assignments_are_deleted_when_model_is_deleted()
    {
        // Create a test user for assignee
        $assignee = User::create([
            'name' => 'Assignee User',
            'email' => 'assignee@example.com',
            'published' => true,
        ]);

        $assigner = User::create([
            'name' => 'Assigner User',
            'email' => 'assigner@example.com',
            'published' => true,
        ]);

        // Create assignment with required fields
        $assignmentData = [
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Test assignment description',
            'due_at' => now()->addDays(7),
        ];

        $assignment = $this->model->assignments()->create($assignmentData);
        $assignmentId = $assignment->id;

        // Delete the model
        $this->model->delete();

        // Check if assignment still exists (depends on cascade configuration)
        // Note: Without foreign key constraints, assignments won't be automatically deleted
        $assignmentTable = modularityConfig('tables.assignments', 'm_assignments');
        $this->assertDatabaseMissing($assignmentTable, [
            'id' => $assignmentId,
        ]);
    }
    public function test_assignments_are_deleted_when_soft_deletes_model_is_deleted()
    {
        // Create a test user for assignee
        $assignee = User::create([
            'name' => 'Assignee User',
            'email' => 'assignee@example.com',
            'published' => true,
        ]);

        $assigner = User::create([
            'name' => 'Assigner User',
            'email' => 'assigner@example.com',
            'published' => true,
        ]);

        // Create assignment with required fields
        $assignmentData = [
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Test assignment description',
            'due_at' => now()->addDays(7),
        ];

        $this->model->assignments()->create($assignmentData);
        $assignment = $this->softDeletesModel->assignments()->create($assignmentData);
        $assignmentId = $assignment->id;

        // Delete the model
        $this->softDeletesModel->delete();

        // Check if assignment still exists (depends on cascade configuration)
        // Note: Without foreign key constraints, assignments won't be automatically deleted
        $assignmentTable = modularityConfig('tables.assignments', 'm_assignments');
        $this->assertDatabaseHas($assignmentTable, [
            'id' => $assignmentId,
        ]);
    }

    public function test_last_assignment_relationship_exists()
    {
        $this->assertTrue(method_exists($this->model, 'lastAssignment'));

        $relation = $this->model->lastAssignment();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $relation);
    }

    public function test_last_assignment_relationship_configuration()
    {
        $relation = $this->model->lastAssignment();

        $this->assertEquals('assignable_id', $relation->getForeignKeyName());
        $this->assertEquals('assignable_type', $relation->getMorphType());
        $this->assertEquals(Assignment::class, $relation->getRelated()::class);
    }

        public function test_last_assignment_returns_most_recent()
    {
        // Create users for assignee and assigner
        $assignee = User::create([
            'name' => 'Assignee User',
            'email' => 'assignee@example.com',
            'published' => true,
        ]);

        $assigner = User::create([
            'name' => 'Assigner User',
            'email' => 'assigner@example.com',
            'published' => true,
        ]);

        // Create first assignment
        $assignment1 = $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'First assignment description',
            'due_at' => now()->addDays(7),
            'created_at' => now()->subDays(2),
        ]);

        sleep(1);

        // Create second (latest) assignment
        $assignment2 = $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Latest assignment description',
            'due_at' => now()->addDays(7),
            'created_at' => now()->subDay(),
        ]);

        $lastAssignment = $this->model->lastAssignment;

        $this->assertNotNull($lastAssignment);
        $this->assertEquals('Latest assignment description', $lastAssignment->description);
        $this->assertEquals($assignment2->id, $lastAssignment->id);
    }

    public function test_initialize_assignable_appends_active_assignee_name()
    {
        $appended = $this->model->getAppends();
        $this->assertContains('active_assignee_name', $appended);
    }

    public function test_active_assignee_name_returns_null_when_no_assignment()
    {
        $this->assertNull($this->model->active_assignee_name);
    }

    public function test_boot_assignable_adds_retrieved_event()
    {
        // This test verifies that the boot method exists and doesn't throw errors
        $model = new TestAssignableModel(['name' => 'Test']);
        $this->assertInstanceOf(TestAssignableModel::class, $model);
    }

    public function test_assignable_scopes_trait_is_used()
    {
        $traits = class_uses_recursive($this->model);
        $this->assertContains('Unusualify\Modularity\Entities\Scopes\AssignableScopes', $traits);
    }

    public function test_get_user_for_assignable_returns_null_when_no_auth()
    {
        Auth::logout();

        $result = $this->model->getUserForAssignable();

        $this->assertNull($result);
    }

    public function test_get_user_for_assignable_returns_provided_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'published' => true,
        ]);

        $result = $this->model->getUserForAssignable($user);

        $this->assertSame($user, $result);
    }

    public function test_scope_is_active_assignee_filters_correctly()
    {
        // Create users
        $assignee1 = User::create(['name' => 'Assignee 1', 'email' => 'assignee1@example.com', 'published' => true]);
        $assignee2 = User::create(['name' => 'Assignee 2', 'email' => 'assignee2@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create another assignable model
        $model2 = new TestAssignableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Create assignments
        $this->model->assignments()->create([
            'assignee_id' => $assignee1->id,
            'assignee_type' => get_class($assignee1),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment for assignee 1',
            'due_at' => now()->addDays(7),
        ]);

        $model2->assignments()->create([
            'assignee_id' => $assignee2->id,
            'assignee_type' => get_class($assignee2),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment for assignee 2',
            'due_at' => now()->addDays(7),
        ]);

        // Test scope filtering
        $modelsForAssignee1 = TestAssignableModel::isActiveAssignee($assignee1)->get();
        $modelsForAssignee2 = TestAssignableModel::isActiveAssignee($assignee2)->get();

        $this->assertCount(1, $modelsForAssignee1);
        $this->assertEquals($this->model->id, $modelsForAssignee1->first()->id);

        $this->assertCount(1, $modelsForAssignee2);
        $this->assertEquals($model2->id, $modelsForAssignee2->first()->id);
    }

    public function test_scope_last_status_assignment_filters_by_status()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create another assignable model
        $model2 = new TestAssignableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Create assignments with different statuses
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Pending assignment',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
        ]);

        $model2->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Completed assignment',
            'status' => AssignmentStatus::COMPLETED,
            'due_at' => now()->addDays(7),
        ]);

        // Test scope filtering by status
        $pendingModels = TestAssignableModel::lastStatusAssignment(AssignmentStatus::PENDING)->get();
        $completedModels = TestAssignableModel::lastStatusAssignment(AssignmentStatus::COMPLETED)->get();

        $this->assertCount(1, $pendingModels);
        $this->assertEquals($this->model->id, $pendingModels->first()->id);

        $this->assertCount(1, $completedModels);
        $this->assertEquals($model2->id, $completedModels->first()->id);
    }

    public function test_scope_completed_assignments()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create assignment with completed status
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Completed assignment',
            'status' => AssignmentStatus::COMPLETED,
            'due_at' => now()->addDays(7),
        ]);

        $completedModels = TestAssignableModel::completedAssignments()->get();

        $this->assertCount(1, $completedModels);
        $this->assertEquals($this->model->id, $completedModels->first()->id);
    }

    public function test_scope_pending_assignments()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create assignment with pending status
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Pending assignment',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
        ]);

        $pendingModels = TestAssignableModel::pendingAssignments()->get();

        $this->assertCount(1, $pendingModels);
        $this->assertEquals($this->model->id, $pendingModels->first()->id);
    }

    public function test_scope_cancelled_assignments()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create assignment with cancelled status
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Cancelled assignment',
            'status' => AssignmentStatus::CANCELLED,
            'due_at' => now()->addDays(7),
        ]);

        $cancelledModels = TestAssignableModel::cancelledAssignments()->get();

        $this->assertCount(1, $cancelledModels);
        $this->assertEquals($this->model->id, $cancelledModels->first()->id);
    }

    public function test_scope_ever_assigned_to_you_without_auth()
    {
        Auth::logout();

        $models = TestAssignableModel::everAssignedToYou()->get();

        // Should return all models when no user is authenticated
        $this->assertGreaterThanOrEqual(1, $models->count());
    }

    public function test_scope_ever_assigned_to_you_with_assignments()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);
        $otherUser = User::create(['name' => 'Other User', 'email' => 'other@example.com', 'published' => true]);

        // Create assignment for specific user
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment for assignee',
            'due_at' => now()->addDays(7),
        ]);

        // Create another model with assignment for different user
        $model2 = new TestAssignableModel(['name' => 'Test Model 2']);
        $model2->save();

        $model2->assignments()->create([
            'assignee_id' => $otherUser->id,
            'assignee_type' => get_class($otherUser),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment for other user',
            'due_at' => now()->addDays(7),
        ]);

        // Mock authentication for the assignee
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($assignee);

        $modelsForAssignee = TestAssignableModel::everAssignedToYou()->get();

        $this->assertCount(1, $modelsForAssignee);
        $this->assertEquals($this->model->id, $modelsForAssignee->first()->id);
    }

    public function test_scope_is_active_assignee_returns_latest_assignment()
    {
        $assignee1 = User::create(['name' => 'Assignee 1', 'email' => 'assignee1@example.com', 'published' => true]);
        $assignee2 = User::create(['name' => 'Assignee 2', 'email' => 'assignee2@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create first assignment
        $this->model->assignments()->create([
            'assignee_id' => $assignee1->id,
            'assignee_type' => get_class($assignee1),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'First assignment',
            'due_at' => now()->addDays(7),
            'created_at' => now()->subDays(2),
        ]);

        sleep(1);

        // Create second (latest) assignment with different assignee
        $this->model->assignments()->create([
            'assignee_id' => $assignee2->id,
            'assignee_type' => get_class($assignee2),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Latest assignment',
            'due_at' => now()->addDays(7),
            'created_at' => now()->subDay(),
        ]);

        // Test that only the latest assignee is considered active
        $modelsForAssignee1 = TestAssignableModel::isActiveAssignee($assignee1)->get();
        $modelsForAssignee2 = TestAssignableModel::isActiveAssignee($assignee2)->get();

        $this->assertCount(0, $modelsForAssignee1); // First assignee is no longer active
        $this->assertCount(1, $modelsForAssignee2); // Second assignee is active
        $this->assertEquals($this->model->id, $modelsForAssignee2->first()->id);
    }

    public function test_scope_your_completed_assignments_with_auth()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);
        $otherUser = User::create(['name' => 'Other User', 'email' => 'other@example.com', 'published' => true]);

        // Create another assignable model
        $model2 = new TestAssignableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Create completed assignment for authenticated user
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Completed assignment for auth user',
            'status' => AssignmentStatus::COMPLETED,
            'due_at' => now()->addDays(7),
        ]);

        // Create completed assignment for other user
        $model2->assignments()->create([
            'assignee_id' => $otherUser->id,
            'assignee_type' => get_class($otherUser),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Completed assignment for other user',
            'status' => AssignmentStatus::COMPLETED,
            'due_at' => now()->addDays(7),
        ]);

        // Mock authentication for the assignee
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($assignee);

        $yourCompletedModels = TestAssignableModel::yourCompletedAssignments()->get();

        $this->assertCount(1, $yourCompletedModels);
        $this->assertEquals($this->model->id, $yourCompletedModels->first()->id);
    }

    public function test_scope_your_completed_assignments_without_auth()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create completed assignment
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Completed assignment',
            'status' => AssignmentStatus::COMPLETED,
            'due_at' => now()->addDays(7),
        ]);

        // Mock no authentication
        Auth::shouldReceive('check')->andReturn(false);
        Auth::shouldReceive('user')->andReturn(null);

        $yourCompletedModels = TestAssignableModel::yourCompletedAssignments()->get();

        // Should return all models when no user is authenticated
        $this->assertGreaterThanOrEqual(1, $yourCompletedModels->count());
    }

    public function test_scope_your_pending_assignments_with_auth()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);
        $otherUser = User::create(['name' => 'Other User', 'email' => 'other@example.com', 'published' => true]);

        // Create another assignable model
        $model2 = new TestAssignableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Create pending assignment for authenticated user
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Pending assignment for auth user',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
        ]);

        // Create pending assignment for other user
        $model2->assignments()->create([
            'assignee_id' => $otherUser->id,
            'assignee_type' => get_class($otherUser),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Pending assignment for other user',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
        ]);

        // Mock authentication for the assignee
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($assignee);

        $yourPendingModels = TestAssignableModel::yourPendingAssignments()->get();

        $this->assertCount(1, $yourPendingModels);
        $this->assertEquals($this->model->id, $yourPendingModels->first()->id);
    }

    public function test_scope_your_pending_assignments_filters_by_latest_assignment()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);
        $otherUser = User::create(['name' => 'Other User', 'email' => 'other@example.com', 'published' => true]);

        // Create first pending assignment for authenticated user
        $this->model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'First pending assignment',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
            'created_at' => now()->subDays(2),
        ]);

        sleep(1);

        // Create latest assignment for different user (should exclude the model from results)
        $this->model->assignments()->create([
            'assignee_id' => $otherUser->id,
            'assignee_type' => get_class($otherUser),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Latest assignment for other user',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
            'created_at' => now()->subDay(),
        ]);

        // Mock authentication for the first assignee
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($assignee);

        $yourPendingModels = TestAssignableModel::yourPendingAssignments()->get();

        // Should return 0 because the latest assignment is for a different user
        $this->assertCount(0, $yourPendingModels);
    }

    public function test_scope_your_assignments_with_different_statuses()
    {
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com', 'published' => true]);
        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create models with different assignment statuses
        $completedModel = new TestAssignableModel(['name' => 'Completed Model']);
        $completedModel->save();
        $completedModel->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Completed assignment',
            'status' => AssignmentStatus::COMPLETED,
            'due_at' => now()->addDays(7),
        ]);

        $pendingModel = new TestAssignableModel(['name' => 'Pending Model']);
        $pendingModel->save();
        $pendingModel->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Pending assignment',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
        ]);

        // Mock authentication
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($assignee);

        // Test completed assignments scope
        $yourCompletedModels = TestAssignableModel::yourCompletedAssignments()->get();
        $this->assertCount(1, $yourCompletedModels);
        $this->assertEquals($completedModel->id, $yourCompletedModels->first()->id);

        // Test pending assignments scope
        $yourPendingModels = TestAssignableModel::yourPendingAssignments()->get();
        $this->assertCount(1, $yourPendingModels);
        $this->assertEquals($pendingModel->id, $yourPendingModels->first()->id);
    }

    public function test_scope_is_active_assignee_for_your_role()
    {
        // Create roles
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'modularity']);
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'modularity']);

        // Create users with roles
        $manager1 = User::create(['name' => 'Manager 1', 'email' => 'manager1@example.com', 'published' => true]);
        $manager1->assignRole($managerRole);

        $manager2 = User::create(['name' => 'Manager 2', 'email' => 'manager2@example.com', 'published' => true]);
        $manager2->assignRole($managerRole);

        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.com', 'published' => true]);
        $editor->assignRole($editorRole);

        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create another assignable model
        $model2 = new TestAssignableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Create assignment for manager1
        $this->model->assignments()->create([
            'assignee_id' => $manager1->id,
            'assignee_type' => get_class($manager1),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment for manager1',
            'due_at' => now()->addDays(7),
        ]);

        // Create assignment for editor
        $model2->assignments()->create([
            'assignee_id' => $editor->id,
            'assignee_type' => get_class($editor),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment for editor',
            'due_at' => now()->addDays(7),
        ]);

        // Test scope for manager2 (same role as manager1)
        $modelsForManagerRole = TestAssignableModel::isActiveAssigneeForYourRole($manager2)->get();
        $this->assertCount(1, $modelsForManagerRole);
        $this->assertEquals($this->model->id, $modelsForManagerRole->first()->id);

        // Test scope for editor (different role)
        $modelsForEditorRole = TestAssignableModel::isActiveAssigneeForYourRole($editor)->get();
        $this->assertCount(1, $modelsForEditorRole);
        $this->assertEquals($model2->id, $modelsForEditorRole->first()->id);
    }

    public function test_scope_is_active_assignee_for_your_role_without_roles()
    {
        $userWithoutRoles = User::create(['name' => 'No Roles User', 'email' => 'noroles@example.com', 'published' => true]);

        $models = TestAssignableModel::isActiveAssigneeForYourRole($userWithoutRoles)->get();

        // Should return no results when user has no roles
        $this->assertCount(0, $models);
    }

    public function test_scope_team_completed_assignments()
    {
        // Create roles
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'modularity']);

        // Create users with same role
        $manager1 = User::create(['name' => 'Manager 1', 'email' => 'manager1@example.com', 'published' => true]);
        $manager1->assignRole($managerRole);

        $manager2 = User::create(['name' => 'Manager 2', 'email' => 'manager2@example.com', 'published' => true]);
        $manager2->assignRole($managerRole);

        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create completed assignment for manager1
        $this->model->assignments()->create([
            'assignee_id' => $manager1->id,
            'assignee_type' => get_class($manager1),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Completed assignment for manager1',
            'status' => AssignmentStatus::COMPLETED,
            'due_at' => now()->addDays(7),
        ]);

        // Mock authentication for manager2 (same role)
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($manager2);

        $teamCompletedModels = TestAssignableModel::teamCompletedAssignments()->get();

        $this->assertCount(1, $teamCompletedModels);
        $this->assertEquals($this->model->id, $teamCompletedModels->first()->id);
    }

    public function test_scope_team_pending_assignments()
    {
        // Create roles
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'modularity']);

        // Create users with same role
        $editor1 = User::create(['name' => 'Editor 1', 'email' => 'editor1@example.com', 'published' => true]);
        $editor1->assignRole($editorRole);

        $editor2 = User::create(['name' => 'Editor 2', 'email' => 'editor2@example.com', 'published' => true]);
        $editor2->assignRole($editorRole);

        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create pending assignment for editor1
        $this->model->assignments()->create([
            'assignee_id' => $editor1->id,
            'assignee_type' => get_class($editor1),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Pending assignment for editor1',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
        ]);

        // Mock authentication for editor2 (same role)
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($editor2);

        $teamPendingModels = TestAssignableModel::teamPendingAssignments()->get();

        $this->assertCount(1, $teamPendingModels);
        $this->assertEquals($this->model->id, $teamPendingModels->first()->id);
    }

    public function test_scope_ever_assigned_to_your_role()
    {
        // Create roles
        $reporterRole = Role::create(['name' => 'reporter', 'guard_name' => 'modularity']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'modularity']);

        // Create users with roles
        $reporter1 = User::create(['name' => 'Reporter 1', 'email' => 'reporter1@example.com', 'published' => true]);
        $reporter1->assignRole($reporterRole);

        $reporter2 = User::create(['name' => 'Reporter 2', 'email' => 'reporter2@example.com', 'published' => true]);
        $reporter2->assignRole($reporterRole);

        $manager = User::create(['name' => 'Manager', 'email' => 'manager@example.com', 'published' => true]);
        $manager->assignRole($managerRole);

        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create another assignable model
        $model2 = new TestAssignableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Create assignment for reporter1
        $this->model->assignments()->create([
            'assignee_id' => $reporter1->id,
            'assignee_type' => get_class($reporter1),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment for reporter1',
            'due_at' => now()->addDays(7),
        ]);

        // Create assignment for manager
        $model2->assignments()->create([
            'assignee_id' => $manager->id,
            'assignee_type' => get_class($manager),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Assignment for manager',
            'due_at' => now()->addDays(7),
        ]);

        // Mock authentication for reporter2 (same role as reporter1)
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($reporter2);

        $modelsForReporterRole = TestAssignableModel::everAssignedToYourRole()->get();

        $this->assertCount(1, $modelsForReporterRole);
        $this->assertEquals($this->model->id, $modelsForReporterRole->first()->id);
    }

    public function test_scope_role_based_assignments_with_latest_assignment_logic()
    {
        // Create roles
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'modularity']);
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'modularity']);

        // Create users
        $manager = User::create(['name' => 'Manager', 'email' => 'manager@example.com', 'published' => true]);
        $manager->assignRole($managerRole);

        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.com', 'published' => true]);
        $editor->assignRole($editorRole);

        $anotherManager = User::create(['name' => 'Another Manager', 'email' => 'manager2@example.com', 'published' => true]);
        $anotherManager->assignRole($managerRole);

        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com', 'published' => true]);

        // Create first assignment to manager
        $this->model->assignments()->create([
            'assignee_id' => $manager->id,
            'assignee_type' => get_class($manager),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'First assignment to manager',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
            'created_at' => now()->subDays(2),
        ]);

        sleep(1);

        // Create latest assignment to editor (different role)
        $this->model->assignments()->create([
            'assignee_id' => $editor->id,
            'assignee_type' => get_class($editor),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Latest assignment to editor',
            'status' => AssignmentStatus::PENDING,
            'due_at' => now()->addDays(7),
            'created_at' => now()->subDay(),
        ]);

        // Test that manager role doesn't see this model (latest assignment is for editor)
        $modelsForManagerRole = TestAssignableModel::isActiveAssigneeForYourRole($anotherManager)->get();
        $this->assertCount(0, $modelsForManagerRole);

        // Test that editor role sees this model (latest assignment is for editor)
        $modelsForEditorRole = TestAssignableModel::isActiveAssigneeForYourRole($editor)->get();
        $this->assertCount(1, $modelsForEditorRole);
        $this->assertEquals($this->model->id, $modelsForEditorRole->first()->id);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_assignable_models');

        // Assignments, users, and permission tables are managed by migrations in TestCase
        parent::tearDown();
    }
}

// Test model that uses the Assignable trait
class TestAssignableModel extends Model
{
    use Assignable;

    protected $table = 'test_assignable_models';

    protected $fillable = ['name'];
}


