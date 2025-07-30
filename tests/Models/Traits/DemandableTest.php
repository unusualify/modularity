<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\Demand;
use Unusualify\Modularity\Entities\Enums\DemandStatus;
use Unusualify\Modularity\Entities\Enums\DemandPriority;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\Traits\Demandable;
use Unusualify\Modularity\Tests\ModelTestCase;

class DemandableTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_demandable_relationships()
    {
        // Add the trait to Company model for testing
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

        // Create multiple demands
        $demand1 = $testCompany->createDemand([
            'title' => 'First Demand',
            'description' => 'First demand description',
            'status' => DemandStatus::PENDING->value,
            'priority' => DemandPriority::HIGH->value,
        ]);

        $demand2 = $testCompany->createDemand([
            'title' => 'Second Demand',
            'description' => 'Second demand description',
            'status' => DemandStatus::ANSWERED->value,
            'priority' => DemandPriority::URGENT->value,
        ]);

        $demand3 = $testCompany->createDemand([
            'title' => 'Third Demand',
            'description' => 'Third demand description',
            'status' => DemandStatus::PENDING->value,
            'priority' => DemandPriority::URGENT->value,
        ]);

        // Test relationships
        $this->assertEquals(3, $testCompany->demands()->count());
        $this->assertEquals($demand3->id, $testCompany->lastDemand->id); // Latest created
        $this->assertEquals(2, $testCompany->activeDemands()->count()); // pending + urgent
        $this->assertEquals(2, $testCompany->pendingDemands()->count());
        $this->assertEquals(1, $testCompany->resolvedDemands()->count()); // answered
        $this->assertEquals(1, $testCompany->urgentDemands()->count()); // urgent priority + active status
    }

    public function test_demandable_accessors()
    {
        // Add the trait to Company model for testing
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

        // Create demands with different statuses and priorities
        Demand::create([
            'demandable_id' => $testCompany->id,
            'demandable_type' => get_class($testCompany),
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Pending Demand',
            'description' => 'Pending description',
            'status' => DemandStatus::PENDING->value,
            'priority' => DemandPriority::MEDIUM->value,
        ]);

        Demand::create([
            'demandable_id' => $testCompany->id,
            'demandable_type' => get_class($testCompany),
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Urgent Demand',
            'description' => 'Urgent description',
            'status' => DemandStatus::IN_REVIEW->value,
            'priority' => DemandPriority::URGENT->value,
        ]);

        Demand::create([
            'demandable_id' => $testCompany->id,
            'demandable_type' => get_class($testCompany),
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Answered Demand',
            'description' => 'Answered description',
            'status' => DemandStatus::IN_REVIEW->value,
            'priority' => DemandPriority::LOW->value,
        ]);

        // Refresh to get accessors
        $testCompany = $testCompany->fresh();

        $this->assertEquals(3, $testCompany->demands_count);
        $this->assertEquals(1, $testCompany->pending_demands_count);
        $this->assertEquals(0, $testCompany->resolved_demands_count);
        $this->assertTrue($testCompany->has_urgent_demands);

        // Test active demand status (should show the last active demand)
        $this->assertStringContainsString('In Review', $testCompany->active_demand_status);
        $this->assertStringContainsString('v-chip', $testCompany->active_demand_status);
    }

    public function test_demandable_trait_initialization()
    {
        $company = new class extends Company {
            use Demandable;
        };

        $testCompany = new $company();

        // Check that the appends are set correctly
        $appends = $testCompany->getAppends();

        $this->assertContains('active_demand_status', $appends);
        $this->assertContains('demands_count', $appends);
        $this->assertContains('pending_demands_count', $appends);
        $this->assertContains('resolved_demands_count', $appends);
        $this->assertContains('last_demand_priority', $appends);
        $this->assertContains('has_urgent_demands', $appends);
    }
}
