<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Event;
use Unusualify\Modularity\Entities\Process;
use Unusualify\Modularity\Entities\ProcessHistory;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Entities\Enums\ProcessStatus;
use Unusualify\Modularity\Tests\ModelTestCase;

class ProcessTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_process()
    {
        $process = new Process;
        $this->assertEquals(modularityConfig('tables.processes', 'm_processes'), $process->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'processable_id',
            'processable_type',
            'status',
            'reason',
        ];

        $process = new Process;
        $this->assertEquals($expectedFillable, $process->getFillable());
    }

    public function test_casts()
    {
        $process = new Process;
        $this->assertArrayHasKey('status', $process->getCasts());
        $this->assertEquals(ProcessStatus::class, $process->getCasts()['status']);
    }

    public function test_create_process()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $this->assertEquals($user->id, $process->processable_id);
        $this->assertEquals(get_class($user), $process->processable_type);
        $this->assertEquals(ProcessStatus::PREPARING, $process->status);
    }

    public function test_update_process()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $process->update([
            'status' => ProcessStatus::CONFIRMED,
        ]);

        $this->assertEquals(ProcessStatus::CONFIRMED, $process->status);
    }

    public function test_delete_process()
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
            'status' => ProcessStatus::CONFIRMED,
        ]);

        $this->assertCount(2, Process::all());

        $process2->delete();

        $this->assertFalse(Process::all()->contains('id', $process2->id));
        $this->assertTrue(Process::all()->contains('id', $process1->id));
        $this->assertCount(1, Process::all());
    }

    public function test_processable_relationship()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $relation = $process->processable();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $process->processable);
        $this->assertEquals($user->id, $process->processable->id);
    }

    public function test_histories_relationship()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $relation = $process->histories();
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('process_id', $relation->getForeignKeyName());
        $this->assertInstanceOf(ProcessHistory::class, $relation->getRelated());
    }

    public function test_last_history_relationship()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $relation = $process->lastHistory();
        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertInstanceOf(ProcessHistory::class, $relation->getRelated());
    }

    public function test_appended_attributes()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $expectedAppends = [
            'status_label',
            'status_color',
            'status_icon',
            'status_card_variant',
            'status_card_color',
            'status_reason_label',
            'status_informational_message',
            'next_action_label',
            'next_action_color',
            'status_dialog_titles',
            'status_dialog_messages',
        ];

        $this->assertEquals($expectedAppends, $process->getAppends());
    }

    public function test_process_status_enum()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $this->assertInstanceOf(ProcessStatus::class, $process->status);

        $process->update(['status' => ProcessStatus::CONFIRMED]);
        $this->assertEquals(ProcessStatus::CONFIRMED, $process->status);

        $process->update(['status' => ProcessStatus::REJECTED]);
        $this->assertEquals(ProcessStatus::REJECTED, $process->status);
    }

    public function test_has_timestamps()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $this->assertTrue($process->timestamps);
        $this->assertNotNull($process->created_at);
        $this->assertNotNull($process->updated_at);
    }

    public function test_process_scopes_trait()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Test that ProcessScopes trait is used
        $this->assertTrue(classHasTrait($process, 'Unusualify\Modularity\Entities\Scopes\ProcessScopes'));
    }

    public function test_process_with_processable_model_using_trait()
    {
        // Create a user that uses the Processable trait
        $user = User::factory()->create();

        // Test that when a processable model is created, it can have a process
        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        $this->assertEquals($user->id, $process->processable_id);
        $this->assertEquals(get_class($user), $process->processable_type);
        $this->assertInstanceOf(User::class, $process->processable);
    }

    public function test_process_status_transitions()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Test status transitions
        $process->update(['status' => ProcessStatus::WAITING_FOR_CONFIRMATION]);
        $this->assertEquals(ProcessStatus::WAITING_FOR_CONFIRMATION, $process->status);

        $process->update(['status' => ProcessStatus::WAITING_FOR_REACTION]);
        $this->assertEquals(ProcessStatus::WAITING_FOR_REACTION, $process->status);

        $process->update(['status' => ProcessStatus::CONFIRMED]);
        $this->assertEquals(ProcessStatus::CONFIRMED, $process->status);

        $process->update(['status' => ProcessStatus::REJECTED]);
        $this->assertEquals(ProcessStatus::REJECTED, $process->status);
    }

    public function test_process_with_reason()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::REJECTED,
        ]);

        $this->assertEquals(ProcessStatus::REJECTED, $process->status);
    }

    public function test_process_histories_relationship_with_data()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Create some process histories
        $history1 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'user_id' => $user->id,
        ]);

        $history2 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::CONFIRMED,
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $process->histories);
        $this->assertEquals($history1->id, $process->histories->first()->id);
        $this->assertEquals($history2->id, $process->histories->last()->id);
    }

    public function test_last_history_relationship_with_data()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Create histories in sequence
        $history1 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::PREPARING,
            'reason' => 'First history',
            'user_id' => $user->id,
        ]);

        sleep(1); // Ensure different timestamps

        $history2 = ProcessHistory::create([
            'process_id' => $process->id,
            'status' => ProcessStatus::CONFIRMED,
            'reason' => 'Latest history',
            'user_id' => $user->id,
        ]);

        $latestHistory = $process->lastHistory;
        $this->assertNotNull($latestHistory);
        $this->assertEquals($history2->id, $latestHistory->id);
        $this->assertEquals('Latest history', $latestHistory->reason);
    }

    public function test_process_status_accessors()
    {
        $user = User::factory()->create();

        $process = Process::create([
            'processable_id' => $user->id,
            'processable_type' => get_class($user),
            'status' => ProcessStatus::PREPARING,
        ]);

        // Test that appended attributes work
        $processArray = $process->toArray();

        $this->assertArrayHasKey('status_label', $processArray);
        $this->assertArrayHasKey('status_color', $processArray);
        $this->assertArrayHasKey('status_icon', $processArray);
        $this->assertArrayHasKey('status_card_variant', $processArray);
        $this->assertArrayHasKey('status_card_color', $processArray);
        $this->assertArrayHasKey('status_reason_label', $processArray);
        $this->assertArrayHasKey('status_informational_message', $processArray);
        $this->assertArrayHasKey('next_action_label', $processArray);
        $this->assertArrayHasKey('next_action_color', $processArray);
        $this->assertArrayHasKey('status_dialog_titles', $processArray);
        $this->assertArrayHasKey('status_dialog_messages', $processArray);

        // Test that the accessors return expected types
        $this->assertIsString($process->status_label);
        $this->assertIsString($process->status_color);
        $this->assertIsString($process->status_icon);
        $this->assertIsArray($process->status_dialog_titles);
        $this->assertIsArray($process->status_dialog_messages);
    }

    public function test_extends_base_model()
    {
        $process = new Process;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $process);
    }
}
