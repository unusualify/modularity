<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Modules\SystemNotification\Events\ProcessHistoryCreated;
use Modules\SystemNotification\Events\ProcessHistoryUpdated;
use Unusualify\Modularity\Entities\Enums\ProcessStatus;
use Unusualify\Modularity\Entities\Process;
use Unusualify\Modularity\Entities\ProcessHistory;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\Processable;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class ProcessableTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test model table
        Schema::create('test_processable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('processable_status')->nullable();
            $table->text('processable_reason')->nullable();
            $table->timestamps();
        });

        // Create test user
        $this->user = User::factory()->create();
        Auth::login($this->user);

        // Create test model instance
        $this->model = new TestProcessableModel(['name' => 'Test Processable Model']);
        $this->model->save();

        // Fake events to prevent actual notifications
        Event::fake([
            ProcessHistoryCreated::class,
            ProcessHistoryUpdated::class,
        ]);
    }

    public function test_trait_initialization()
    {
        // Test that the trait is properly used
        $this->assertTrue(in_array(
            Processable::class,
            class_uses_recursive($this->model)
        ));

        // Test that HasFileponds trait is also included
        $this->assertTrue(in_array(
            HasFileponds::class,
            class_uses_recursive($this->model)
        ));

        // Test that ProcessableScopes is included
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Scopes\ProcessableScopes::class,
            class_uses_recursive($this->model)
        ));
    }

    public function test_process_relationship()
    {
        // Test the morphOne relationship
        $relationship = $this->model->process();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $relationship);
        $this->assertEquals(Process::class, get_class($relationship->getRelated()));
    }

    public function test_process_histories_relationship()
    {
        // Test the hasManyThrough relationship
        $relationship = $this->model->processHistories();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $relationship);
        $this->assertEquals(ProcessHistory::class, get_class($relationship->getRelated()));

        // Test the relationship works by creating data
        $this->model->setProcessStatus(ProcessStatus::PREPARING->value, 'Test history');
        $histories = $this->model->processHistories;
        $this->assertCount(1, $histories);
        $this->assertInstanceOf(ProcessHistory::class, $histories->first());

        sleep(1);

        $this->model->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Test history 2');

        $this->model->refresh();

        $this->assertCount(2, $this->model->processHistories);
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $this->model->processHistory->status);
        $this->assertEquals('Test history 2', $this->model->processHistory->reason);
        $this->assertEquals($this->user->id, $this->model->processHistory->user_id);

        $this->assertTrue($this->model->has_process_history);
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $this->model->process_history_status);
        $this->assertEquals('Test history 2', $this->model->process_history_reason);
    }

    public function test_boot_processable_creates_process_on_model_creation()
    {
        // Create a new model to test the boot method
        $newModel = new TestProcessableModel(['name' => 'New Test Model']);
        $newModel->save();

        // Check that a process was created
        $this->assertNotNull($newModel->process);
        $this->assertEquals(ProcessStatus::PREPARING, $newModel->process->status);
        $this->assertEquals($newModel->id, $newModel->process->processable_id);
        $this->assertEquals(get_class($newModel), $newModel->process->processable_type);
    }

    // public function test_boot_processable_does_not_duplicate_process_with_existing_process()
    // {
    //     $this->expectException(\Exception::class);
    //     $this->expectExceptionMessage('Processable model already exists');

    //     // Create a process manually first
    //     $existingProcess = $this->model->process()->create([
    //         'status' => ProcessStatus::CONFIRMED,
    //     ]);
    // }

    public function test_boot_processable_does_not_duplicate_process()
    {
        // Create a process manually first
        $existingProcess = $this->model->process;


        // Create another model to trigger the boot method
        $newModel = new TestProcessableModel(['name' => 'Another Test Model']);
        $newModel->save();

        // The existing model should still have its original process
        $this->model->refresh();
        $this->assertEquals($existingProcess->id, $this->model->process->id);

        // The new model should have its own process
        $this->assertNotEquals($existingProcess->id, $newModel->process->id);
        $this->assertEquals(ProcessStatus::PREPARING, $newModel->process->status);
    }

    public function test_set_process_status_creates_process_and_history()
    {
        // Set a process status
        $this->model->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Test reason');

        // Check that process was created/updated
        $this->assertNotNull($this->model->process);
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $this->model->process->status);

        // Check that process history was created
        $this->assertCount(1, $this->model->process->histories);
        $history = $this->model->process->histories->first();
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $history->status);
        $this->assertEquals('Test reason', $history->reason);
        $this->assertEquals($this->user->id, $history->user_id);
    }

    public function test_set_process_status_while_saving_model()
    {
        // Set a process status with no reason
        $this->model->processable_status = ProcessStatus::WAITING_FOR_CONFIRMATION->value;
        $this->model->processable_reason = 'Test reason';
        $this->model->save();


        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $this->model->process->status);
        $this->assertEquals('Test reason', $this->model->process->histories->first()->reason);
        $this->assertEquals($this->user->id, $this->model->process->histories->first()->user_id);
    }

    public function test_set_process_status_updates_existing_process()
    {
        // Update the process status
        $this->model->setProcessStatus(ProcessStatus::CONFIRMED->value, 'Approved Reason');

        $this->model->refresh();
        $this->assertEquals(ProcessStatus::CONFIRMED, $this->model->process->status);

        // Check that history was created
        $this->assertCount(1, $this->model->process->histories);
        $history = $this->model->process->histories->first();
        $this->assertEquals(ProcessStatus::CONFIRMED, $history->status);
        $this->assertEquals('Approved Reason', $history->reason);
    }

    public function test_set_process_status_creates_multiple_histories()
    {
        // Set multiple process statuses
        $this->model->setProcessStatus(ProcessStatus::PREPARING->value, 'Initial status');
        $this->model->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Sent for review');
        $this->model->setProcessStatus(ProcessStatus::CONFIRMED->value, 'Approved');

        $this->model->refresh();

        // Check final status
        $this->assertEquals(ProcessStatus::CONFIRMED, $this->model->process->status);

        // Check that all histories were created
        $this->assertCount(3, $this->model->process->histories);

        $histories = $this->model->process->histories()->orderBy('created_at')->get();
        $this->assertEquals(ProcessStatus::PREPARING, $histories[0]->status);
        $this->assertEquals('Initial status', $histories[0]->reason);

        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $histories[1]->status);
        $this->assertEquals('Sent for review', $histories[1]->reason);

        $this->assertEquals(ProcessStatus::CONFIRMED, $histories[2]->status);
        $this->assertEquals('Approved', $histories[2]->reason);
    }

    public function test_send_for_confirmation()
    {
        // Call the method
        $this->model->sendForConfirmation();

        $this->model->refresh();

        // Check that status was set correctly
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $this->model->process->status);

        // Check that history was created
        $this->assertCount(1, $this->model->process->histories);
        $history = $this->model->process->histories->first();
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $history->status);
        $this->assertNull($history->reason);
    }

    public function test_confirm()
    {
        // Call the method
        $this->model->confirm();

        $this->model->refresh();

        // Check that status was set correctly
        $this->assertEquals(ProcessStatus::CONFIRMED, $this->model->process->status);

        // Check that history was created
        $this->assertCount(1, $this->model->process->histories);
        $history = $this->model->process->histories->first();
        $this->assertEquals(ProcessStatus::CONFIRMED, $history->status);
        $this->assertNull($history->reason);
    }

    public function test_reject()
    {
        // Call the method with a reason
        $this->model->reject('Content does not meet requirements');

        $this->model->refresh();

        // Check that status was set correctly
        $this->assertEquals(ProcessStatus::REJECTED, $this->model->process->status);

        // Check that history was created with reason
        $this->assertCount(1, $this->model->process->histories);
        $history = $this->model->process->histories->first();
        $this->assertEquals(ProcessStatus::REJECTED, $history->status);
        $this->assertEquals('Content does not meet requirements', $history->reason);
    }

    public function test_is_process_status()
    {
        // Initially no process exists
        $this->assertTrue($this->model->isProcessStatus(ProcessStatus::PREPARING));

        // Create a process with a specific status
        $this->model->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Waiting for confirmation');

        $this->model->refresh();

        // Test the method
        $this->assertTrue($this->model->isProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION));
        $this->assertFalse($this->model->isProcessStatus(ProcessStatus::CONFIRMED));
        $this->assertFalse($this->model->isProcessStatus(ProcessStatus::REJECTED));
    }

    public function test_process_workflow_complete_cycle()
    {
        // Test a complete process workflow
        $this->model->sendForConfirmation();
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $this->model->process->status);
        $this->assertTrue($this->model->isProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION));

        sleep(1);

        // Confirm the process
        $this->model->confirm();
        $this->model->refresh();
        $this->assertEquals(ProcessStatus::CONFIRMED, $this->model->processHistory->status);
        $this->assertTrue($this->model->isProcessStatus(ProcessStatus::CONFIRMED));
        $this->assertFalse($this->model->isProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION));

        // Check that we have 2 histories
        $this->assertCount(2, $this->model->process->histories);
    }

    public function test_process_workflow_rejection_cycle()
    {
        // Test a rejection workflow
        $this->model->sendForConfirmation();
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $this->model->process->status);

        sleep(1);
        // Reject the process
        $this->model->reject('Insufficient information provided');
        $this->model->refresh();
        $this->assertEquals(ProcessStatus::REJECTED, $this->model->processHistory->status);
        $this->assertTrue($this->model->isProcessStatus(ProcessStatus::REJECTED));
        $this->assertFalse($this->model->isProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION));

        // Check that we have 2 histories with correct reasons
        $this->assertCount(2, $this->model->process->histories);
        $histories = $this->model->process->histories()->orderBy('created_at')->get();
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $histories[0]->status);
        $this->assertNull($histories[0]->reason);
        $this->assertEquals(ProcessStatus::REJECTED, $histories[1]->status);
        $this->assertEquals('Insufficient information provided', $histories[1]->reason);
    }

    public function test_process_histories_through_relationship()
    {
        // Create process and histories
        $this->model->setProcessStatus(ProcessStatus::PREPARING->value, 'Initial');
        $this->model->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Sent for review');
        $this->model->setProcessStatus(ProcessStatus::CONFIRMED->value, 'Approved');

        // Test the hasManyThrough relationship
        $histories = $this->model->processHistories;
        $this->assertCount(3, $histories);

        // Test that histories are properly related
        foreach ($histories as $history) {
            $this->assertEquals($this->model->process->id, $history->process_id);
            $this->assertEquals($this->user->id, $history->user_id);
        }
    }

    public function test_process_status_enum_integration()
    {
        // Test all process status enum values
        $statuses = [
            ProcessStatus::PREPARING,
            ProcessStatus::WAITING_FOR_CONFIRMATION,
            ProcessStatus::WAITING_FOR_REACTION,
            ProcessStatus::CONFIRMED,
            ProcessStatus::REJECTED,
        ];

        foreach ($statuses as $status) {
            $this->model->setProcessStatus($status->value, "Testing {$status->value}");
            $this->model->refresh();
            $this->assertEquals($status, $this->model->process->status);
            $this->assertTrue($this->model->isProcessStatus($status));
            sleep(1);
        }

        // Should have 5 histories
        $this->assertCount(5, $this->model->process->histories);
    }

    public function test_process_with_different_users()
    {
        // Create another user
        $anotherUser = User::factory()->create();

        // Set initial status with first user
        $this->model->setProcessStatus(ProcessStatus::PREPARING->value, 'Initial by first user');

        // Switch to another user
        Auth::login($anotherUser);
        $this->model->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Updated by second user');

        $this->model->refresh();
        $histories = $this->model->process->histories()->orderBy('created_at')->get();

        // Check that different users are recorded
        $this->assertEquals($this->user->id, $histories[0]->user_id);
        $this->assertEquals($anotherUser->id, $histories[1]->user_id);
    }

    public function test_process_without_authenticated_user()
    {
        // Logout user
        Auth::logout();

        // Set process status without authenticated user
        $this->model->setProcessStatus(ProcessStatus::PREPARING->value, 'No user');

        $this->model->refresh();
        $history = $this->model->process->histories->first();

        // Check that user_id is null when no user is authenticated
        $this->assertNull($history->user_id);
        $this->assertEquals('No user', $history->reason);
    }

    public function test_multiple_processable_models()
    {
        // Create another processable model
        $anotherModel = new TestProcessableModel(['name' => 'Another Model']);
        $anotherModel->save();

        // Set different statuses for both models
        $this->model->setProcessStatus(ProcessStatus::CONFIRMED->value, 'First model approved');
        $anotherModel->setProcessStatus(ProcessStatus::REJECTED->value, 'Second model rejected');

        // Check that each model has its own process
        $this->assertNotEquals($this->model->process->id, $anotherModel->process->id);
        $this->assertEquals(ProcessStatus::CONFIRMED, $this->model->process->status);
        $this->assertEquals(ProcessStatus::REJECTED, $anotherModel->process->status);

        // Check that histories are separate
        $this->assertEquals('First model approved', $this->model->process->histories->first()->reason);
        $this->assertEquals('Second model rejected', $anotherModel->process->histories->first()->reason);
    }

    public function test_process_status_convenience_methods()
    {
        // Test that convenience methods use correct enum values
        $this->model->sendForConfirmation();
        $this->assertEquals(ProcessStatus::get('WAITING_FOR_CONFIRMATION'), $this->model->process->status->value);

        sleep(1);
        $this->model->confirm();
        $this->model->refresh();
        $this->assertEquals(ProcessStatus::get('CONFIRMED'), $this->model->processHistory->status->value);

        sleep(1);
        $this->model->reject('Test rejection');
        $this->model->refresh();
        $this->assertEquals(ProcessStatus::get('REJECTED'), $this->model->processHistory->status->value);
    }

    public function test_process_events_are_dispatched()
    {
        Event::fake([
            ProcessHistoryCreated::class,
            ProcessHistoryUpdated::class,
        ]);

        // Create process history
        $this->model->setProcessStatus(ProcessStatus::get('WAITING_FOR_CONFIRMATION'), 'Test event');

        // Verify that ProcessHistoryCreated event was dispatched
        Event::assertDispatched(ProcessHistoryCreated::class, function ($event) {
            return $event->model->status === ProcessStatus::WAITING_FOR_CONFIRMATION
                && $event->model->reason === 'Test event';
        });
    }

    public function test_process_relationship_cascade_delete()
    {
        // Create process and history
        $this->model->setProcessStatus(ProcessStatus::PREPARING->value, 'Test cascade');
        $processId = $this->model->process->id;
        $historyId = $this->model->process->histories->first()->id;

        // Delete the processable model
        $this->model->delete();

        // Check that process still exists (no cascade from processable to process)
        $this->assertDatabaseHas(modularityConfig('tables.processes', 'm_processes'), ['id' => $processId]);
        $this->assertDatabaseHas(modularityConfig('tables.process_histories', 'm_process_histories'), ['id' => $historyId]);
    }

    public function test_integration_with_process_scopes()
    {
        // Create models with different process statuses
        $preparedModel = new TestProcessableModel(['name' => 'Prepared Model']);
        $preparedModel->save();
        $preparedModel->setProcessStatus(ProcessStatus::PREPARING->value);

        $confirmedModel = new TestProcessableModel(['name' => 'Confirmed Model']);
        $confirmedModel->save();
        $confirmedModel->setProcessStatus(ProcessStatus::CONFIRMED->value);

        $rejectedModel = new TestProcessableModel(['name' => 'Rejected Model']);
        $rejectedModel->save();
        $rejectedModel->setProcessStatus(ProcessStatus::REJECTED->value);

        // Test that we can query processes by status through the relationship
        $preparingProcesses = Process::where('status', ProcessStatus::PREPARING)->get();
        $confirmedProcesses = Process::where('status', ProcessStatus::CONFIRMED)->get();
        $rejectedProcesses = Process::where('status', ProcessStatus::REJECTED)->get();

        // Including the initial model created in setUp
        $this->assertGreaterThanOrEqual(2, $preparingProcesses->count()); // setUp model + preparedModel
        $this->assertEquals(1, $confirmedProcesses->count());
        $this->assertEquals(1, $rejectedProcesses->count());
    }

    public function test_scope_is_confirmed_process()
    {
        // Create models with different process statuses
        $confirmedModel1 = new TestProcessableModel(['name' => 'Confirmed Model 1']);
        $confirmedModel1->save();
        $confirmedModel1->setProcessStatus(ProcessStatus::CONFIRMED->value, 'Approved 1');

        $confirmedModel2 = new TestProcessableModel(['name' => 'Confirmed Model 2']);
        $confirmedModel2->save();
        $confirmedModel2->setProcessStatus(ProcessStatus::CONFIRMED->value, 'Approved 2');

        $rejectedModel = new TestProcessableModel(['name' => 'Rejected Model']);
        $rejectedModel->save();
        $rejectedModel->setProcessStatus(ProcessStatus::REJECTED->value, 'Not approved');

        // Test the scope
        $confirmedModels = TestProcessableModel::isConfirmedProcess()->get();
        $this->assertCount(2, $confirmedModels);
        $this->assertTrue($confirmedModels->contains('id', $confirmedModel1->id));
        $this->assertTrue($confirmedModels->contains('id', $confirmedModel2->id));
        $this->assertFalse($confirmedModels->contains('id', $rejectedModel->id));
    }

    public function test_scope_is_waiting_for_confirmation_process()
    {
        // Create models with different process statuses
        $waitingModel1 = new TestProcessableModel(['name' => 'Waiting Model 1']);
        $waitingModel1->save();
        $waitingModel1->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Sent for review 1');

        $waitingModel2 = new TestProcessableModel(['name' => 'Waiting Model 2']);
        $waitingModel2->save();
        $waitingModel2->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Sent for review 2');

        $confirmedModel = new TestProcessableModel(['name' => 'Confirmed Model']);
        $confirmedModel->save();
        $confirmedModel->setProcessStatus(ProcessStatus::CONFIRMED->value, 'Already approved');

        // Test the scope
        $waitingModels = TestProcessableModel::isWaitingForConfirmationProcess()->get();
        $this->assertCount(2, $waitingModels);
        $this->assertTrue($waitingModels->contains('id', $waitingModel1->id));
        $this->assertTrue($waitingModels->contains('id', $waitingModel2->id));
        $this->assertFalse($waitingModels->contains('id', $confirmedModel->id));
    }

    public function test_scope_is_waiting_for_reaction_process()
    {
        // Create models with different process statuses
        $reactionModel1 = new TestProcessableModel(['name' => 'Reaction Model 1']);
        $reactionModel1->save();
        $reactionModel1->setProcessStatus(ProcessStatus::WAITING_FOR_REACTION->value, 'Needs reaction 1');

        $reactionModel2 = new TestProcessableModel(['name' => 'Reaction Model 2']);
        $reactionModel2->save();
        $reactionModel2->setProcessStatus(ProcessStatus::WAITING_FOR_REACTION->value, 'Needs reaction 2');

        $preparingModel = new TestProcessableModel(['name' => 'Preparing Model']);
        $preparingModel->save();
        $preparingModel->setProcessStatus(ProcessStatus::PREPARING->value, 'Still preparing');

        // Test the scope
        $reactionModels = TestProcessableModel::isWaitingForReactionProcess()->get();
        $this->assertCount(2, $reactionModels);
        $this->assertTrue($reactionModels->contains('id', $reactionModel1->id));
        $this->assertTrue($reactionModels->contains('id', $reactionModel2->id));
        $this->assertFalse($reactionModels->contains('id', $preparingModel->id));
    }

    public function test_scope_is_rejected_process()
    {
        // Create models with different process statuses
        $rejectedModel1 = new TestProcessableModel(['name' => 'Rejected Model 1']);
        $rejectedModel1->save();
        $rejectedModel1->setProcessStatus(ProcessStatus::REJECTED->value, 'Rejected reason 1');

        $rejectedModel2 = new TestProcessableModel(['name' => 'Rejected Model 2']);
        $rejectedModel2->save();
        $rejectedModel2->setProcessStatus(ProcessStatus::REJECTED->value, 'Rejected reason 2');

        $confirmedModel = new TestProcessableModel(['name' => 'Confirmed Model']);
        $confirmedModel->save();
        $confirmedModel->setProcessStatus(ProcessStatus::CONFIRMED->value, 'Approved');

        // Test the scope
        $rejectedModels = TestProcessableModel::isRejectedProcess()->get();
        $this->assertCount(2, $rejectedModels);
        $this->assertTrue($rejectedModels->contains('id', $rejectedModel1->id));
        $this->assertTrue($rejectedModels->contains('id', $rejectedModel2->id));
        $this->assertFalse($rejectedModels->contains('id', $confirmedModel->id));
    }

    public function test_scope_is_preparing_process()
    {
        // Create models with different process statuses
        $preparingModel1 = new TestProcessableModel(['name' => 'Preparing Model 1']);
        $preparingModel1->save();
        $preparingModel1->setProcessStatus(ProcessStatus::PREPARING->value, 'In preparation 1');

        $preparingModel2 = new TestProcessableModel(['name' => 'Preparing Model 2']);
        $preparingModel2->save();
        $preparingModel2->setProcessStatus(ProcessStatus::PREPARING->value, 'In preparation 2');

        $rejectedModel = new TestProcessableModel(['name' => 'Rejected Model']);
        $rejectedModel->save();
        $rejectedModel->setProcessStatus(ProcessStatus::REJECTED->value, 'Rejected');

        // Test the scope
        $preparingModels = TestProcessableModel::isPreparingProcess()->get();

        // Should include the models we created plus the initial model from setUp
        $this->assertGreaterThanOrEqual(3, $preparingModels->count());
        $this->assertTrue($preparingModels->contains('id', $preparingModel1->id));
        $this->assertTrue($preparingModels->contains('id', $preparingModel2->id));
        $this->assertTrue($preparingModels->contains('id', $this->model->id)); // setUp model
        $this->assertFalse($preparingModels->contains('id', $rejectedModel->id));
    }

    public function test_scope_chaining()
    {
        // Create models with specific statuses
        $confirmedModel = new TestProcessableModel(['name' => 'Confirmed Model']);
        $confirmedModel->save();
        $confirmedModel->setProcessStatus(ProcessStatus::CONFIRMED->value, 'Approved');

        $waitingModel = new TestProcessableModel(['name' => 'Waiting Model']);
        $waitingModel->save();
        $waitingModel->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value, 'Pending');

        // Test that scopes can be chained with other query methods
        $confirmedCount = TestProcessableModel::isConfirmedProcess()->count();
        $waitingCount = TestProcessableModel::isWaitingForConfirmationProcess()->count();

        $this->assertEquals(1, $confirmedCount);
        $this->assertEquals(1, $waitingCount);

        // Test chaining with where clauses
        $confirmedWithName = TestProcessableModel::isConfirmedProcess()
            ->where('name', 'Confirmed Model')
            ->first();

        $this->assertNotNull($confirmedWithName);
        $this->assertEquals('Confirmed Model', $confirmedWithName->name);
        $this->assertEquals(ProcessStatus::CONFIRMED, $confirmedWithName->process->status);
    }

    public function test_multiple_scopes_combination()
    {
        // Create models with different statuses
        $confirmedModel = new TestProcessableModel(['name' => 'Confirmed Model']);
        $confirmedModel->save();
        $confirmedModel->setProcessStatus(ProcessStatus::CONFIRMED->value);

        $rejectedModel = new TestProcessableModel(['name' => 'Rejected Model']);
        $rejectedModel->save();
        $rejectedModel->setProcessStatus(ProcessStatus::REJECTED->value);

        $waitingModel = new TestProcessableModel(['name' => 'Waiting Model']);
        $waitingModel->save();
        $waitingModel->setProcessStatus(ProcessStatus::WAITING_FOR_CONFIRMATION->value);

        // Test that different scopes return different results
        $confirmedModels = TestProcessableModel::isConfirmedProcess()->get();
        $rejectedModels = TestProcessableModel::isRejectedProcess()->get();
        $waitingModels = TestProcessableModel::isWaitingForConfirmationProcess()->get();

        // Ensure no overlap between different status scopes
        $this->assertCount(1, $confirmedModels);
        $this->assertCount(1, $rejectedModels);
        $this->assertCount(1, $waitingModels);

        $this->assertNotEquals($confirmedModels->first()->id, $rejectedModels->first()->id);
        $this->assertNotEquals($confirmedModels->first()->id, $waitingModels->first()->id);
        $this->assertNotEquals($rejectedModels->first()->id, $waitingModels->first()->id);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

// Basic test model that uses Processable trait
class TestProcessableModel extends Model
{
    use Processable;

    protected $table = 'test_processable_models';
    protected $fillable = ['name', 'processable_status', 'processable_reason'];
}
