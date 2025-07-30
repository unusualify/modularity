<?php

namespace Unusualify\Modularity\Tests\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\Demand;
use Unusualify\Modularity\Entities\Enums\DemandStatus;
use Unusualify\Modularity\Entities\Enums\DemandPriority;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Tests\ModelTestCase;

class DemandTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test database tables
        $this->setUpDemandTables();
    }

    protected function setUpDemandTables()
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        // // Users table
        // $schema->create('users', function ($table) {
        //     $table->uuid('id')->primary();
        //     $table->string('name');
        //     $table->string('email')->unique();
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->string('password');
        //     $table->rememberToken();
        //     $table->timestamps();
        // });

        // // Companies table
        // $schema->create('companies', function ($table) {
        //     $table->uuid('id')->primary();
        //     $table->string('name');
        //     $table->timestamps();
        // });

        // // Demands table
        // $schema->create('um_demands', function ($table) {
        //     $table->uuid('id')->primary();
        //     $table->string('name')->nullable();
        //     $table->uuidMorphs('demandable');
        //     $table->uuidMorphs('demander');
        //     $table->uuidMorphs('responder');
        //     $table->string('status')->default('pending');
        //     $table->string('priority')->default('medium');
        //     $table->string('title');
        //     $table->text('description');
        //     $table->text('response')->nullable();
        //     $table->timestamp('due_at')->nullable();
        //     $table->timestamp('response_at')->nullable();
        //     $table->timestamp('resolved_at')->nullable();
        //     $table->uuid('parent_id')->nullable();
        //     $table->foreign('parent_id')->references('id')->on('m_demands')->onDelete('cascade');
        //     $table->boolean('published')->default(true);
        //     $table->timestamps();
        // });
    }

    public function test_get_table_demand()
    {
        $demand = new Demand;
        $this->assertEquals('um_demands', $demand->getTable());
    }

    public function test_create_demand()
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
            'title' => 'Test Demand',
            'description' => 'This is a test demand description',
            'priority' => DemandPriority::HIGH->value,
            'due_at' => now()->addDays(7),
            // 'status' => DemandStatus::PENDING->value,
        ]);

        $this->assertEquals('Test Demand', $demand->title);
        $this->assertEquals('This is a test demand description', $demand->description);
        $this->assertEquals(DemandPriority::HIGH, $demand->priority);
        $this->assertEquals(DemandStatus::PENDING, $demand->status);
        $this->assertEquals($user->id, $demand->demander_id);
        $this->assertEquals($company->id, $demand->demandable_id);
    }

    public function test_demand_relationships()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $responder = User::create([
            'name' => 'Responder User',
            'email' => 'responder@example.com',
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
            'responder_id' => $responder->id,
            'responder_type' => User::class,
            'title' => 'Test Demand',
            'description' => 'This is a test demand description',
        ]);

        // Test relationships
        $this->assertInstanceOf(Company::class, $demand->demandable);
        $this->assertInstanceOf(User::class, $demand->demander);
        $this->assertInstanceOf(User::class, $demand->responder);
        $this->assertEquals($company->id, $demand->demandable->id);
        $this->assertEquals($user->id, $demand->demander->id);
        $this->assertEquals($responder->id, $demand->responder->id);
    }

    public function test_demand_parent_child_relationships()
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

        $parentDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Parent Demand',
            'description' => 'This is a parent demand',
        ]);

        $childDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Child Demand',
            'description' => 'This is a child demand',
            'parent_id' => $parentDemand->id,
        ]);

        $this->assertEquals($parentDemand->id, $childDemand->parent->id);
        $this->assertTrue($parentDemand->children->contains('id', $childDemand->id));
    }

    public function test_demand_accessors()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $responder = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
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
            'responder_id' => $responder->id,
            'responder_type' => User::class,
            'title' => 'Test Demand',
            'description' => 'This is a test demand description',
            'response' => 'This is a response',
            'priority' => DemandPriority::HIGH->value,
            'status' => DemandStatus::ANSWERED->value,
            'due_at' => now()->addDay(),
        ]);

        // Reload to get accessors
        $demand = $demand->fresh();

        $this->assertEquals('John Doe', $demand->demander_name);
        $this->assertEquals('Jane Smith', $demand->responder_name);
        $this->assertEquals('High', $demand->priority_label);
        $this->assertEquals('Answered', $demand->status_label);
        $this->assertTrue($demand->has_response);
        $this->assertFalse($demand->is_overdue);
    }

    public function test_demand_scopes()
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

        // Create demands with different statuses and priorities
        $pendingDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Pending Demand',
            'description' => 'Pending description',
            'status' => DemandStatus::PENDING->value,
            'priority' => DemandPriority::HIGH->value,
        ]);

        $answeredDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Answered Demand',
            'description' => 'Answered description',
            'status' => DemandStatus::ANSWERED->value,
            'priority' => DemandPriority::URGENT->value,
        ]);

        $urgentDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Urgent Demand',
            'description' => 'Urgent description',
            'status' => DemandStatus::IN_REVIEW->value,
            'priority' => DemandPriority::URGENT->value,
        ]);

        // Test scopes
        $this->assertEquals(1, Demand::isPending()->count());
        $this->assertEquals(1, Demand::isAnswered()->count());
        $this->assertEquals(2, Demand::isActive()->count());
        $this->assertEquals(1, Demand::isResolved()->count());
        $this->assertEquals(2, Demand::byPriority(DemandPriority::URGENT->value)->count());
        $this->assertEquals(1, Demand::byPriority(DemandPriority::HIGH->value)->count());
    }

    public function test_demand_overdue_functionality()
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

        // Create overdue demand
        $overdueDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Overdue Demand',
            'description' => 'This demand is overdue',
            'due_at' => now()->subDay(),
            'status' => DemandStatus::PENDING->value,
        ]);

        // Create future demand
        $futureDemand = Demand::create([
            'demandable_id' => $company->id,
            'demandable_type' => Company::class,
            'demander_id' => $user->id,
            'demander_type' => User::class,
            'title' => 'Future Demand',
            'description' => 'This demand is not overdue',
            'due_at' => now()->addDay(),
            'status' => DemandStatus::PENDING->value,
        ]);

        $overdueDemand = $overdueDemand->fresh();
        $futureDemand = $futureDemand->fresh();

        $this->assertTrue($overdueDemand->is_overdue);
        $this->assertFalse($futureDemand->is_overdue);

        $this->assertEquals(1, Demand::overdue()->count());
    }

    public function test_demand_status_casting()
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
            'title' => 'Test Demand',
            'description' => 'Test description',
            'status' => 'in_review',
            'priority' => 'urgent',
        ]);

        $this->assertInstanceOf(DemandStatus::class, $demand->status);
        $this->assertInstanceOf(DemandPriority::class, $demand->priority);
        $this->assertEquals(DemandStatus::IN_REVIEW, $demand->status);
        $this->assertEquals(DemandPriority::URGENT, $demand->priority);
    }
}
