<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\CreatorRecord;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class CreatorRecordTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_creator_record()
    {
        $creatorRecord = new CreatorRecord;
        $this->assertEquals(modularityConfig('tables.creator_records', 'modularity_creator_records'), $creatorRecord->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'id',
            'creator_type',
            'creator_id',
            'guard_name',
            'creatable_type',
            'creatable_id',
        ];

        $creatorRecord = new CreatorRecord;
        $this->assertEquals($expectedFillable, $creatorRecord->getFillable());
    }

    public function test_no_timestamps()
    {
        $creatorRecord = new CreatorRecord;
        $this->assertFalse($creatorRecord->timestamps);
    }

    public function test_create_creator_record()
    {
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        $creatorRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        $this->assertEquals(get_class($creator), $creatorRecord->creator_type);
        $this->assertEquals($creator->id, $creatorRecord->creator_id);
        $this->assertEquals('web', $creatorRecord->guard_name);
        $this->assertEquals(get_class($creatable), $creatorRecord->creatable_type);
        $this->assertEquals($creatable->id, $creatorRecord->creatable_id);
    }

    public function test_update_creator_record()
    {
        $creator1 = User::factory()->create();
        $creator2 = User::factory()->create();
        $creatable = User::factory()->create();

        $creatorRecord = CreatorRecord::create([
            'creator_type' => get_class($creator1),
            'creator_id' => $creator1->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        $creatorRecord->update([
            'creator_type' => get_class($creator2),
            'creator_id' => $creator2->id,
            'guard_name' => 'api',
        ]);

        $this->assertEquals(get_class($creator2), $creatorRecord->creator_type);
        $this->assertEquals($creator2->id, $creatorRecord->creator_id);
        $this->assertEquals('api', $creatorRecord->guard_name);
    }

    public function test_delete_creator_record()
    {
        $creator = User::factory()->create();
        $creatable1 = User::factory()->create();
        $creatable2 = User::factory()->create();

        $creatorRecord1 = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable1),
            'creatable_id' => $creatable1->id,
        ]);

        $creatorRecord2 = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable2),
            'creatable_id' => $creatable2->id,
        ]);

        $this->assertCount(2, CreatorRecord::all());

        $creatorRecord2->delete();

        $this->assertFalse(CreatorRecord::all()->contains('id', $creatorRecord2->id));
        $this->assertTrue(CreatorRecord::all()->contains('id', $creatorRecord1->id));
        $this->assertCount(1, CreatorRecord::all());
    }

    public function test_creator_relationship()
    {
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        $creatorRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        $relation = $creatorRecord->creator();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $creatorRecord->creator);
        $this->assertEquals($creator->id, $creatorRecord->creator->id);
    }

    public function test_creatable_relationship()
    {
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        $creatorRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        $relation = $creatorRecord->creatable();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $creatorRecord->creatable);
        $this->assertEquals($creatable->id, $creatorRecord->creatable->id);
    }

    public function test_extends_base_model()
    {
        $creatorRecord = new CreatorRecord;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $creatorRecord);
    }

    public function test_polymorphic_creator_relationship()
    {
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        $creatorRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        // Test polymorphic type storage for creator
        $this->assertEquals(get_class($creator), $creatorRecord->creator_type);
        $this->assertEquals($creator->id, $creatorRecord->creator_id);

        // Test querying by polymorphic type
        $userCreatorRecords = CreatorRecord::where('creator_type', get_class($creator))->get();
        $this->assertTrue($userCreatorRecords->contains('id', $creatorRecord->id));
    }

    public function test_polymorphic_creatable_relationship()
    {
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        $creatorRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        // Test polymorphic type storage for creatable
        $this->assertEquals(get_class($creatable), $creatorRecord->creatable_type);
        $this->assertEquals($creatable->id, $creatorRecord->creatable_id);

        // Test querying by polymorphic type
        $userCreatableRecords = CreatorRecord::where('creatable_type', get_class($creatable))->get();
        $this->assertTrue($userCreatableRecords->contains('id', $creatorRecord->id));
    }

    public function test_guard_name_functionality()
    {
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        // Test different guard names
        $webGuardRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        $apiGuardRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'api',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        $this->assertEquals('web', $webGuardRecord->guard_name);
        $this->assertEquals('api', $apiGuardRecord->guard_name);

        // Test querying by guard name
        $webRecords = CreatorRecord::where('guard_name', 'web')->get();
        $apiRecords = CreatorRecord::where('guard_name', 'api')->get();

        $this->assertTrue($webRecords->contains('id', $webGuardRecord->id));
        $this->assertTrue($apiRecords->contains('id', $apiGuardRecord->id));
    }

    public function test_multiple_creator_records_for_same_creator()
    {
        $creator = User::factory()->create();
        $creatable1 = User::factory()->create();
        $creatable2 = User::factory()->create();

        // Same creator can create multiple resources
        $creatorRecord1 = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable1),
            'creatable_id' => $creatable1->id,
        ]);

        $creatorRecord2 = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable2),
            'creatable_id' => $creatable2->id,
        ]);

        $creatorRecords = CreatorRecord::where('creator_id', $creator->id)->get();

        $this->assertCount(2, $creatorRecords);
        $this->assertEquals($creator->id, $creatorRecord1->creator->id);
        $this->assertEquals($creator->id, $creatorRecord2->creator->id);
        $this->assertNotEquals($creatorRecord1->creatable->id, $creatorRecord2->creatable->id);
    }

    public function test_creator_record_pivot_table_functionality()
    {
        // Test that CreatorRecord acts as a pivot table between creator and creatable models
        $admin = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Admin created both users
        $creatorRecord1 = CreatorRecord::create([
            'creator_type' => get_class($admin),
            'creator_id' => $admin->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($user1),
            'creatable_id' => $user1->id,
        ]);

        $creatorRecord2 = CreatorRecord::create([
            'creator_type' => get_class($admin),
            'creator_id' => $admin->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($user2),
            'creatable_id' => $user2->id,
        ]);

        // Test querying by creator
        $adminCreatorRecords = CreatorRecord::where('creator_id', $admin->id)->get();
        $user1CreatorRecords = CreatorRecord::where('creatable_id', $user1->id)->get();

        $this->assertCount(2, $adminCreatorRecords);
        $this->assertCount(1, $user1CreatorRecords);
        $this->assertEquals($user1->id, $adminCreatorRecords->first()->creatable_id);
        $this->assertEquals($user2->id, $adminCreatorRecords->last()->creatable_id);
    }

    public function test_creator_record_with_different_guards()
    {
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        // Test different authentication guards
        $guards = ['web', 'api', 'admin', 'sanctum'];

        $creatorRecords = [];
        foreach ($guards as $guard) {
            $creatorRecords[] = CreatorRecord::create([
                'creator_type' => get_class($creator),
                'creator_id' => $creator->id,
                'guard_name' => $guard,
                'creatable_type' => get_class($creatable),
                'creatable_id' => $creatable->id,
            ]);
        }

        $this->assertCount(4, $creatorRecords);

        // Test querying by each guard
        foreach ($guards as $index => $guard) {
            $guardRecords = CreatorRecord::where('guard_name', $guard)->get();
            $this->assertCount(1, $guardRecords);
            $this->assertEquals($guard, $guardRecords->first()->guard_name);
        }
    }

    public function test_creator_record_cascade_deletion_simulation()
    {
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        $creatorRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        $creatorRecordId = $creatorRecord->id;

        // Test that creator record can be deleted independently
        $creatorRecord->delete();

        $this->assertDatabaseMissing(modularityConfig('tables.creator_records', 'modularity_creator_records'), ['id' => $creatorRecordId]);

        // Original models should still exist
        $this->assertDatabaseHas('um_users', ['id' => $creator->id]);
        $this->assertDatabaseHas('um_users', ['id' => $creatable->id]);
    }

    public function test_creator_record_query_scopes_simulation()
    {
        $creator1 = User::factory()->create();
        $creator2 = User::factory()->create();
        $creatable1 = User::factory()->create();
        $creatable2 = User::factory()->create();

        // Create various creator records
        CreatorRecord::create([
            'creator_type' => get_class($creator1),
            'creator_id' => $creator1->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable1),
            'creatable_id' => $creatable1->id,
        ]);

        CreatorRecord::create([
            'creator_type' => get_class($creator1),
            'creator_id' => $creator1->id,
            'guard_name' => 'api',
            'creatable_type' => get_class($creatable2),
            'creatable_id' => $creatable2->id,
        ]);

        CreatorRecord::create([
            'creator_type' => get_class($creator2),
            'creator_id' => $creator2->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable1),
            'creatable_id' => $creatable1->id,
        ]);

        // Test querying creator records by creator
        $creator1Records = CreatorRecord::where('creator_id', $creator1->id)->get();
        $this->assertCount(2, $creator1Records);

        // Test querying creator records by creatable
        $creatable1Records = CreatorRecord::where('creatable_id', $creatable1->id)->get();
        $this->assertCount(2, $creatable1Records);

        // Test querying by guard and creator
        $creator1WebRecords = CreatorRecord::where('creator_id', $creator1->id)
            ->where('guard_name', 'web')
            ->get();

        $this->assertCount(1, $creator1WebRecords);
    }

    public function test_creator_record_with_has_creator_trait_integration()
    {
        // Simulate how CreatorRecord works with HasCreator trait
        $creator = User::factory()->create();
        $creatable = User::factory()->create();

        $creatorRecord = CreatorRecord::create([
            'creator_type' => get_class($creator),
            'creator_id' => $creator->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($creatable),
            'creatable_id' => $creatable->id,
        ]);

        // Test the morphTo relationships work correctly
        $this->assertEquals($creator->id, $creatorRecord->creator->id);
        $this->assertEquals($creatable->id, $creatorRecord->creatable->id);

        // Test that we can find creator records by both sides
        $creatorRecordsByCreator = CreatorRecord::where('creator_id', $creator->id)
            ->where('creator_type', get_class($creator))
            ->get();

        $creatorRecordsByCreatable = CreatorRecord::where('creatable_id', $creatable->id)
            ->where('creatable_type', get_class($creatable))
            ->get();

        $this->assertCount(1, $creatorRecordsByCreator);
        $this->assertCount(1, $creatorRecordsByCreatable);
        $this->assertEquals($creatorRecord->id, $creatorRecordsByCreator->first()->id);
        $this->assertEquals($creatorRecord->id, $creatorRecordsByCreatable->first()->id);
    }

    public function test_creator_record_audit_trail_simulation()
    {
        // Simulate audit trail functionality
        $admin = User::factory()->create();
        $editor = User::factory()->create();
        $document1 = User::factory()->create(); // Simulating documents
        $document2 = User::factory()->create();

        // Admin creates document1
        $creatorRecord1 = CreatorRecord::create([
            'creator_type' => get_class($admin),
            'creator_id' => $admin->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($document1),
            'creatable_id' => $document1->id,
        ]);

        // Editor creates document2
        $creatorRecord2 = CreatorRecord::create([
            'creator_type' => get_class($editor),
            'creator_id' => $editor->id,
            'guard_name' => 'web',
            'creatable_type' => get_class($document2),
            'creatable_id' => $document2->id,
        ]);

        // Test audit trail queries
        $adminCreations = CreatorRecord::where('creator_id', $admin->id)->get();
        $editorCreations = CreatorRecord::where('creator_id', $editor->id)->get();

        $this->assertCount(1, $adminCreations);
        $this->assertCount(1, $editorCreations);

        // Test finding who created what
        $document1Creator = CreatorRecord::where('creatable_id', $document1->id)->first();
        $document2Creator = CreatorRecord::where('creatable_id', $document2->id)->first();

        $this->assertEquals($admin->id, $document1Creator->creator_id);
        $this->assertEquals($editor->id, $document2Creator->creator_id);
    }
}
