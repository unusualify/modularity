<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Event;
use Modules\SystemNotification\Events\ProcessHistoryCreated;
use Modules\SystemNotification\Events\ProcessHistoryUpdated;
use Unusualify\Modularity\Entities\Process;
use Unusualify\Modularity\Entities\ProcessHistory;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Entities\Enums\ProcessStatus;
use Unusualify\Modularity\Tests\ModelTestCase;

class ProcessHistoryTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_process_history()
    {
        $processHistory = new ProcessHistory;
        $this->assertEquals(modularityConfig('tables.process_histories', 'm_process_histories'), $processHistory->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'process_id',
            'status',
            'reason',
            'user_id',
        ];

        $processHistory = new ProcessHistory;
        $this->assertEquals($expectedFillable, $processHistory->getFillable());
    }

    public function test_casts()
    {
        $expectedCasts = [
            'status' => ProcessStatus::class,
        ];

        $processHistory = new ProcessHistory;
        $casts = $processHistory->getCasts();

        foreach ($expectedCasts as $key => $value) {
            $this->assertArrayHasKey($key, $casts);
            $this->assertEquals($value, $casts[$key]);
        }
    }

    public function test_create_process_history()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Process started',
            'user_id' => $user->id,
        ]);

        $processHistory->process()->associate($process);
        $processHistory->save();

        $this->assertEquals(ProcessStatus::PREPARING, $processHistory->status);
        $this->assertEquals('Process started', $processHistory->reason);
        $this->assertEquals($user->id, $processHistory->user_id);
    }

    public function test_update_process_history()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Initial reason',
            'user_id' => $user->id,
        ]);
        $processHistory->process()->associate($process);
        $processHistory->save();

        $processHistory->update([
            'status' => ProcessStatus::CONFIRMED,
            'reason' => 'Updated reason',
        ]);

        $this->assertEquals(ProcessStatus::CONFIRMED, $processHistory->status);
        $this->assertEquals('Updated reason', $processHistory->reason);
    }

    public function test_delete_process_history()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory1 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'History 1',
            'user_id' => $user->id,
        ]);

        $processHistory2 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::CONFIRMED,
            'reason' => 'History 2',
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, ProcessHistory::all());

        $processHistory2->delete();

        $this->assertFalse(ProcessHistory::all()->contains('id', $processHistory2->id));
        $this->assertTrue(ProcessHistory::all()->contains('id', $processHistory1->id));
        $this->assertCount(1, ProcessHistory::all());
    }

    public function test_process_relationship()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Test relationship',
            'user_id' => $user->id,
        ]);

        $relation = $processHistory->process();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Process::class, $processHistory->process);
        $this->assertEquals($process->id, $processHistory->process->id);
    }

        public function test_user_relationship()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Test user relationship',
            'user_id' => $user->id,
        ]);

        $relation = $processHistory->user();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $processHistory->user);
        $this->assertEquals($user->id, $processHistory->user->id);
    }

    public function test_process_status_enum()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Status test',
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(ProcessStatus::class, $processHistory->status);

        $processHistory->update(['status' => ProcessStatus::CONFIRMED]);
        $this->assertEquals(ProcessStatus::CONFIRMED, $processHistory->status);

        $processHistory->update(['status' => ProcessStatus::REJECTED]);
        $this->assertEquals(ProcessStatus::REJECTED, $processHistory->status);
    }

    public function test_has_timestamps()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Timestamp test',
            'user_id' => $user->id,
        ]);

        $this->assertTrue($processHistory->timestamps);
        $this->assertNotNull($processHistory->created_at);
        $this->assertNotNull($processHistory->updated_at);
    }

    public function test_extends_base_model()
    {
        $processHistory = new ProcessHistory;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $processHistory);
    }

    public function test_create_process_history_with_minimum_fields()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
        ]);

        $this->assertNotNull($processHistory->id);
        $this->assertEquals($process->id, $processHistory->process_id);
        $this->assertEquals(ProcessStatus::PREPARING, $processHistory->status);
        $this->assertNull($processHistory->reason);
        $this->assertNull($processHistory->user_id);
    }

    public function test_process_history_chronological_order()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $history1 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Started',
            'user_id' => $user->id,
        ]);

        // Wait a moment to ensure different timestamps
        sleep(1);

        $history2 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::WAITING_FOR_CONFIRMATION,
            'reason' => 'Waiting for confirmation',
            'user_id' => $user->id,
        ]);

        $history3 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::CONFIRMED,
            'reason' => 'Confirmed',
            'user_id' => $user->id,
        ]);

        $histories = ProcessHistory::where('process_id', $process->id)
            ->orderBy('created_at')
            ->get();

        $this->assertCount(3, $histories);
        $this->assertEquals($history1->id, $histories[0]->id);
        $this->assertEquals($history2->id, $histories[1]->id);
        $this->assertEquals($history3->id, $histories[2]->id);
    }

    public function test_multiple_processes_histories()
    {
        $user = User::factory()->create();

        $process1 = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $process2 = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $history1 = ProcessHistory::create([
            'process_id' => $process1->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Process 1 started',
        ]);

        $history2 = ProcessHistory::create([
            'process_id' => $process2->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Process 2 started',
        ]);

        $this->assertEquals($process1->id, $history1->process_id);
        $this->assertEquals($process2->id, $history2->process_id);
        $this->assertNotEquals($history1->process_id, $history2->process_id);
    }

    public function test_process_history_created_event_dispatched()
    {
        Event::fake([
            ProcessHistoryCreated::class,
        ]);

        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Event test',
            'user_id' => $user->id,
        ]);

        Event::assertDispatched(ProcessHistoryCreated::class, function ($event) use ($processHistory) {
            return $event->model->id === $processHistory->id;
        });
    }

    public function test_process_history_updated_event_dispatched()
    {
        Event::fake([
            ProcessHistoryUpdated::class,
        ]);

        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Initial reason',
            'user_id' => $user->id,
        ]);

        // Clear events from creation
        Event::fake([
            ProcessHistoryUpdated::class,
        ]);

        $processHistory->update([
            'reason' => 'Updated reason',
        ]);

        Event::assertDispatched(ProcessHistoryUpdated::class, function ($event) use ($processHistory) {
            return $event->model->id === $processHistory->id;
        });
    }

    public function test_process_history_without_events()
    {
        // Test without faking events to ensure normal behavior works
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'No event test',
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($processHistory->id);
        $this->assertEquals('No event test', $processHistory->reason);
    }

    public function test_process_history_status_workflow()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Test a typical workflow progression
        $history1 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Process initialized',
            'user_id' => $user->id,
        ]);

        $history2 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::WAITING_FOR_CONFIRMATION,
            'reason' => 'Submitted for review',
            'user_id' => $user->id,
        ]);

        $history3 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::CONFIRMED,
            'reason' => 'Approved by reviewer',
            'user_id' => $user->id,
        ]);

        $histories = ProcessHistory::where('process_id', $process->id)
            ->orderBy('created_at')
            ->get();

        $this->assertCount(3, $histories);
        $this->assertEquals(ProcessStatus::PREPARING, $histories[0]->status);
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $histories[1]->status);
        $this->assertEquals(ProcessStatus::CONFIRMED, $histories[2]->status);
    }

    public function test_process_history_with_rejection_workflow()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Test rejection workflow
        $history1 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Process started',
            'user_id' => $user->id,
        ]);

        $history2 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::WAITING_FOR_CONFIRMATION,
            'reason' => 'Sent for review',
            'user_id' => $user->id,
        ]);

        $history3 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::REJECTED,
            'reason' => 'Missing required documentation',
            'user_id' => $user->id,
        ]);

        $rejectedHistory = ProcessHistory::where('process_id', $process->id)
            ->where('status', ProcessStatus::REJECTED)
            ->first();

        $this->assertNotNull($rejectedHistory);
        $this->assertEquals('Missing required documentation', $rejectedHistory->reason);
        $this->assertEquals(ProcessStatus::REJECTED, $rejectedHistory->status);
    }

    public function test_process_history_belongs_to_correct_process()
    {
        $user = User::factory()->create();

        // Create multiple processes
        $process1 = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $process2 = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Create histories for each process
        $history1 = ProcessHistory::create([
            'process_id' => $process1->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Process 1 history',
            'user_id' => $user->id,
        ]);

        $history2 = ProcessHistory::create([
            'process_id' => $process2->id,
            'status' => ProcessStatus::CONFIRMED,
            'reason' => 'Process 2 history',
            'user_id' => $user->id,
        ]);

        // Verify relationships
        $this->assertEquals($process1->id, $history1->process->id);
        $this->assertEquals($process2->id, $history2->process->id);
        $this->assertNotEquals($history1->process->id, $history2->process->id);
    }

    public function test_process_history_user_relationship_scenarios()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user1->id,
            'processable_type' => get_class($user1),
            'status' => ProcessStatus::PREPARING,
        ]);

        // History created by user1
        $history1 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'Created by user 1',
            'user_id' => $user1->id,
        ]);

        // History created by user2 (different user)
        $history2 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::CONFIRMED,
            'reason' => 'Approved by user 2',
            'user_id' => $user2->id,
        ]);

        $this->assertEquals($user1->id, $history1->user->id);
        $this->assertEquals($user2->id, $history2->user->id);
        $this->assertNotEquals($history1->user->id, $history2->user->id);
    }

    public function test_process_history_without_user()
    {
        $user = User::factory()->create();
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Create history without user (system generated)
        $processHistory = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'System generated history',
        ]);

        $this->assertNotNull($processHistory->id);
        $this->assertNull($processHistory->user_id);
        $this->assertNull($processHistory->user);
        $this->assertEquals('System generated history', $processHistory->reason);
    }
}
