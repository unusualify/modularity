<?php

namespace Unusualify\Modularity\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\Demand;
use Unusualify\Modularity\Entities\Enums\DemandStatus;
use Unusualify\Modularity\Entities\Enums\DemandPriority;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\Traits\Demandable;
use Unusualify\Modularity\Tests\ModelTestCase;
use Unusualify\Modularity\Tests\TestCase;

class DemandFeatureTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_demand_workflow_complete_lifecycle()
    {
        $client = User::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => 'password',
        ]);

        $support = User::create([
            'name' => 'Support User',
            'email' => 'support@example.com',
            'password' => 'password',
        ]);

        $company = Company::create([
            'name' => 'Test Company',
        ]);

        // Step 1: Client creates a demand
        $this->actingAs($client);

        $demand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $client->id,
            'demander_type' => User::class,
            'title' => 'Website Performance Issue',
            'description' => 'The website is loading very slowly on mobile devices.',
            'priority' => DemandPriority::HIGH->value,
            'due_at' => now()->addDays(3),
        ]);

        $this->assertEquals(DemandStatus::PENDING, $demand->status);
        $this->assertEquals('Client User', $demand->demander_name);

        // Step 2: Support team evaluates the demand
        $this->actingAs($support);

        $demand->update([
            'status' => DemandStatus::EVALUATED->value,
            'responder_id' => $support->id,
            'responder_type' => User::class,
            'response' => 'We have received your request and are investigating the issue.',
            'response_at' => now(),
        ]);

        $demand = $demand->fresh();
        $this->assertEquals(DemandStatus::EVALUATED, $demand->status);
        $this->assertEquals('Support User', $demand->responder_name);
        $this->assertTrue($demand->has_response);

        // Step 3: Move to review
        $demand->update([
            'status' => DemandStatus::IN_REVIEW->value,
            'response' => 'We are currently reviewing your case and will provide a solution soon.',
            'response_at' => now(),
        ]);

        $demand = $demand->fresh();
        $this->assertEquals(DemandStatus::IN_REVIEW, $demand->status);
        $this->assertTrue($demand->status->isActive());

        // Step 4: Provide final answer
        $demand->update([
            'status' => DemandStatus::ANSWERED->value,
            'response' => 'We have optimized the mobile performance. Please check and let us know if the issue persists.',
            'resolved_at' => now(),
        ]);

        $demand = $demand->fresh();
        $this->assertEquals(DemandStatus::ANSWERED, $demand->status);
        $this->assertFalse($demand->status->isActive());
        $this->assertNotNull($demand->resolved_at);
    }

    public function test_demand_qa_thread_functionality()
    {
        $client = User::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => 'password',
        ]);

        $support = User::create([
            'name' => 'Support User',
            'email' => 'support@example.com',
            'password' => 'password',
        ]);

        $company = Company::create([
            'name' => 'Test Company',
        ]);

        $this->actingAs($client);

        // Create parent demand
        $parentDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $client->id,
            'demander_type' => User::class,
            'title' => 'Login Issue',
            'description' => 'I cannot log into my account.',
            'priority' => DemandPriority::URGENT->value,
        ]);

        $this->actingAs($support);

        // Support responds
        $parentDemand->update([
            'status' => DemandStatus::ANSWERED->value,
            'responder_id' => $support->id,
            'responder_type' => User::class,
            'response' => 'Please try resetting your password.',
            'resolved_at' => now(),
        ]);

        $this->actingAs($client);

        // Client follows up with additional question
        $followUpDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $client->id,
            'demander_type' => User::class,
            'title' => 'Follow-up: Password Reset Not Working',
            'description' => 'I tried resetting my password but did not receive the email.',
            'priority' => DemandPriority::HIGH->value,
            'parent_id' => $parentDemand->id,
        ]);

        // Test thread relationships
        $this->assertEquals($parentDemand->id, $followUpDemand->parent->id);
        $this->assertTrue($parentDemand->children->contains('id', $followUpDemand->id));
        $this->assertEquals(1, $parentDemand->children()->count());

        // Test thread querying
        $threadDemands = Demand::withThread()
            ->whereNull('parent_id')
            ->where('id', $parentDemand->id)
            ->first();

        $this->assertNotNull($threadDemands->children);
        $this->assertEquals(1, $threadDemands->children->count());
    }

    public function test_demand_priority_and_overdue_management()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $company = Company::create([
            'name' => 'Test Company',
        ]);

        $this->actingAs($user);

        // Create demands with different priorities and due dates
        $urgentOverdue = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Urgent Overdue',
            'description' => 'Urgent and overdue demand',
            'priority' => DemandPriority::URGENT->value,
            'due_at' => now()->subDays(2),
        ]);

        $highFuture = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'High Priority Future',
            'description' => 'High priority with future due date',
            'priority' => DemandPriority::HIGH->value,
            'due_at' => now()->addDays(5),
        ]);

        $lowOverdue = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Low Priority Overdue',
            'description' => 'Low priority but overdue',
            'priority' => DemandPriority::LOW->value,
            'due_at' => now()->subDay(),
        ]);

        // Test priority ordering
        $priorityOrdered = Demand::byPriorityOrder('desc')->get();
        $this->assertEquals($urgentOverdue->id, $priorityOrdered->first()->id);

        // Test overdue filtering
        $overdueDemands = Demand::overdue()->get();
        $this->assertEquals(2, $overdueDemands->count());
        $this->assertTrue($overdueDemands->contains('id', $urgentOverdue->id));
        $this->assertTrue($overdueDemands->contains('id', $lowOverdue->id));
        $this->assertFalse($overdueDemands->contains('id', $highFuture->id));

        // Test individual overdue status
        $urgentOverdue = $urgentOverdue->fresh();
        $highFuture = $highFuture->fresh();

        $this->assertTrue($urgentOverdue->is_overdue);
        $this->assertFalse($highFuture->is_overdue);

        // Test days until due
        $this->assertLessThan(0, $urgentOverdue->days_until_due); // Negative for overdue
        $this->assertGreaterThan(0, $highFuture->days_until_due); // Positive for future
    }

    public function test_demandable_trait_integration()
    {
        // Create a test model with Demandable trait
        $company = new class extends Company {
            use Demandable;
        };

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $testCompany = $company::create([
            'name' => 'Test Company',
        ]);

        $this->actingAs($user);

        // Create various demands
        Demand::create([
            'demandable_id' => $testCompany->id,
            'demandable_type' => get_class($testCompany),
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Pending High',
            'description' => 'Pending high priority demand',
            'status' => DemandStatus::PENDING->value,
            'priority' => DemandPriority::HIGH->value,
        ]);

        Demand::create([
            'demandable_id' => $testCompany->id,
            'demandable_type' => get_class($testCompany),
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Urgent InReview',
            'description' => 'Urgent in review demand',
            'status' => DemandStatus::IN_REVIEW->value,
            'priority' => DemandPriority::URGENT->value,
        ]);

        Demand::create([
            'demandable_id' => $testCompany->id,
            'demandable_type' => get_class($testCompany),
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Answered Low',
            'description' => 'Answered low priority demand',
            'status' => DemandStatus::ANSWERED->value,
            'priority' => DemandPriority::LOW->value,
        ]);

        // Test trait functionality
        $testCompany = $testCompany->fresh();

        $this->assertEquals(3, $testCompany->demands_count);
        $this->assertEquals(1, $testCompany->pending_demands_count);
        $this->assertEquals(1, $testCompany->resolved_demands_count);
        $this->assertTrue($testCompany->has_urgent_demands);

        // Test last demand is the most recent
        $this->assertEquals('Answered Low', $testCompany->lastDemand->title);

        // Test active demand status shows the latest active demand
        // dd($testCompany->lastDemand->status);
        // $this->assertStringContainsString('In Review', $testCompany->active_demand_status);
    }

    public function test_demand_status_transitions()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $company = Company::create([
            'name' => 'Test Company',
        ]);

        $this->actingAs($user);

        $demand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Status Transition Test',
            'description' => 'Testing status transitions',
            'priority' => DemandPriority::MEDIUM->value,
        ]);

        // Test initial state
        $this->assertEquals(DemandStatus::PENDING, $demand->status);
        $this->assertTrue($demand->status->isActive());
        $this->assertFalse($demand->status->allowsResponse());

        // Test transition to evaluated
        $demand->update(['status' => DemandStatus::EVALUATED->value]);
        $demand = $demand->fresh();

        $this->assertEquals(DemandStatus::EVALUATED, $demand->status);
        $this->assertTrue($demand->status->isActive());
        $this->assertTrue($demand->status->allowsResponse());

        // Test transition to rejected
        $demand->update([
            'status' => DemandStatus::REJECTED->value,
            'resolved_at' => now(),
        ]);
        $demand = $demand->fresh();

        $this->assertEquals(DemandStatus::REJECTED, $demand->status);
        $this->assertFalse($demand->status->isActive());
        $this->assertFalse($demand->status->allowsResponse());
        $this->assertNotNull($demand->resolved_at);
    }
}
