<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\Authorization;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class AuthorizationTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_authorization()
    {
        $authorization = new Authorization;
        $this->assertEquals(modularityConfig('tables.authorizations', 'modularity_authorizations'), $authorization->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'id',
            'authorized_id',
            'authorized_type',
            'authorizable_id',
            'authorizable_type',
        ];

        $authorization = new Authorization;
        $this->assertEquals($expectedFillable, $authorization->getFillable());
    }

    public function test_create_authorization()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $this->assertEquals($authorizedUser->id, $authorization->authorized_id);
        $this->assertEquals(get_class($authorizedUser), $authorization->authorized_type);
        $this->assertEquals($authorizableUser->id, $authorization->authorizable_id);
        $this->assertEquals(get_class($authorizableUser), $authorization->authorizable_type);
    }

    public function test_update_authorization()
    {
        $authorizedUser1 = User::factory()->create();
        $authorizedUser2 = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser1->id,
            'authorized_type' => get_class($authorizedUser1),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $authorization->update([
            'authorized_id' => $authorizedUser2->id,
            'authorized_type' => get_class($authorizedUser2),
        ]);

        $this->assertEquals($authorizedUser2->id, $authorization->authorized_id);
        $this->assertEquals(get_class($authorizedUser2), $authorization->authorized_type);
    }

    public function test_delete_authorization()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser1 = User::factory()->create();
        $authorizableUser2 = User::factory()->create();

        $authorization1 = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser1->id,
            'authorizable_type' => get_class($authorizableUser1),
        ]);

        $authorization2 = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser2->id,
            'authorizable_type' => get_class($authorizableUser2),
        ]);

        $this->assertCount(2, Authorization::all());

        $authorization2->delete();

        $this->assertFalse(Authorization::all()->contains('id', $authorization2->id));
        $this->assertTrue(Authorization::all()->contains('id', $authorization1->id));
        $this->assertCount(1, Authorization::all());
    }

    public function test_authorized_relationship()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $relation = $authorization->authorized();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $authorization->authorized);
        $this->assertEquals($authorizedUser->id, $authorization->authorized->id);
    }

    public function test_authorizable_relationship()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $relation = $authorization->authorizable();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $authorization->authorizable);
        $this->assertEquals($authorizableUser->id, $authorization->authorizable->id);
    }

    public function test_extends_base_model()
    {
        $authorization = new Authorization;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $authorization);
    }

    public function test_has_timestamps()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $this->assertTrue($authorization->timestamps);
        $this->assertNotNull($authorization->created_at);
        $this->assertNotNull($authorization->updated_at);
    }

    public function test_polymorphic_authorized_relationship()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        // Test polymorphic type storage for authorized
        $this->assertEquals(get_class($authorizedUser), $authorization->authorized_type);
        $this->assertEquals($authorizedUser->id, $authorization->authorized_id);

        // Test querying by polymorphic type
        $userAuthorizations = Authorization::where('authorized_type', get_class($authorizedUser))->get();
        $this->assertTrue($userAuthorizations->contains('id', $authorization->id));
    }

    public function test_polymorphic_authorizable_relationship()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        // Test polymorphic type storage for authorizable
        $this->assertEquals(get_class($authorizableUser), $authorization->authorizable_type);
        $this->assertEquals($authorizableUser->id, $authorization->authorizable_id);

        // Test querying by polymorphic type
        $userAuthorizables = Authorization::where('authorizable_type', get_class($authorizableUser))->get();
        $this->assertTrue($userAuthorizables->contains('id', $authorization->id));
    }

    public function test_multiple_authorizations_for_same_user()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser1 = User::factory()->create();
        $authorizableUser2 = User::factory()->create();

        // Same user can be authorized for multiple resources
        $authorization1 = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser1->id,
            'authorizable_type' => get_class($authorizableUser1),
        ]);

        $authorization2 = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser2->id,
            'authorizable_type' => get_class($authorizableUser2),
        ]);

        $userAuthorizations = Authorization::where('authorized_id', $authorizedUser->id)->get();

        $this->assertCount(2, $userAuthorizations);
        $this->assertEquals($authorizedUser->id, $authorization1->authorized->id);
        $this->assertEquals($authorizedUser->id, $authorization2->authorized->id);
        $this->assertNotEquals($authorization1->authorizable->id, $authorization2->authorizable->id);
    }

    public function test_multiple_users_authorized_for_same_resource()
    {
        $authorizedUser1 = User::factory()->create();
        $authorizedUser2 = User::factory()->create();
        $authorizableUser = User::factory()->create();

        // Multiple users can be authorized for the same resource
        $authorization1 = Authorization::create([
            'authorized_id' => $authorizedUser1->id,
            'authorized_type' => get_class($authorizedUser1),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $authorization2 = Authorization::create([
            'authorized_id' => $authorizedUser2->id,
            'authorized_type' => get_class($authorizedUser2),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $resourceAuthorizations = Authorization::where('authorizable_id', $authorizableUser->id)->get();

        $this->assertCount(2, $resourceAuthorizations);
        $this->assertEquals($authorizableUser->id, $authorization1->authorizable->id);
        $this->assertEquals($authorizableUser->id, $authorization2->authorizable->id);
        $this->assertNotEquals($authorization1->authorized->id, $authorization2->authorized->id);
    }

    public function test_authorization_pivot_table_functionality()
    {
        // Test that Authorization acts as a pivot table between authorized and authorizable models
        $manager = User::factory()->create();
        $employee1 = User::factory()->create();
        $employee2 = User::factory()->create();

        // Manager is authorized for both employees
        $authorization1 = Authorization::create([
            'authorized_id' => $manager->id,
            'authorized_type' => get_class($manager),
            'authorizable_id' => $employee1->id,
            'authorizable_type' => get_class($employee1),
        ]);

        $authorization2 = Authorization::create([
            'authorized_id' => $manager->id,
            'authorized_type' => get_class($manager),
            'authorizable_id' => $employee2->id,
            'authorizable_type' => get_class($employee2),
        ]);

        // Test querying by authorized user (manager)
        $managerAuthorizations = Authorization::where('authorized_id', $manager->id)->get();
        $employee1Authorizations = Authorization::where('authorizable_id', $employee1->id)->get();

        $this->assertCount(2, $managerAuthorizations);
        $this->assertCount(1, $employee1Authorizations);
        $this->assertEquals($employee1->id, $managerAuthorizations->first()->authorizable_id);
        $this->assertEquals($employee2->id, $managerAuthorizations->last()->authorizable_id);
    }

    public function test_authorization_with_different_model_types()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        // Test authorization between different types (if we had other models)
        // For now, test with User as both authorized and authorizable
        $authorization = Authorization::create([
            'authorized_id' => $user->id,
            'authorized_type' => get_class($user),
            'authorizable_id' => $anotherUser->id,
            'authorizable_type' => get_class($anotherUser),
        ]);

        $this->assertEquals(get_class($user), $authorization->authorized_type);
        $this->assertEquals(get_class($anotherUser), $authorization->authorizable_type);

        // Both could be the same type but different instances
        $this->assertInstanceOf(User::class, $authorization->authorized);
        $this->assertInstanceOf(User::class, $authorization->authorizable);
        $this->assertNotEquals($authorization->authorized->id, $authorization->authorizable->id);
    }

    public function test_authorization_cascade_deletion_simulation()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $authorizationId = $authorization->id;

        // Test that authorization can be deleted independently
        $authorization->delete();

        $this->assertDatabaseMissing(modularityConfig('tables.authorizations', 'um_authorizations'), ['id' => $authorizationId]);

        // Original models should still exist
        $this->assertDatabaseHas('um_users', ['id' => $authorizedUser->id]);
        $this->assertDatabaseHas('um_users', ['id' => $authorizableUser->id]);
    }

    public function test_authorization_role_based_simulation()
    {
        // Simulate role-based authorization like in PressRelease
        $admin = User::factory()->create();
        $manager = User::factory()->create();
        $editor = User::factory()->create();
        $client = User::factory()->create();

        // Simulate different authorization levels
        $adminAuthorization = Authorization::create([
            'authorized_id' => $admin->id,
            'authorized_type' => get_class($admin),
            'authorizable_id' => $client->id,
            'authorizable_type' => get_class($client),
        ]);

        $managerAuthorization = Authorization::create([
            'authorized_id' => $manager->id,
            'authorized_type' => get_class($manager),
            'authorizable_id' => $client->id,
            'authorizable_type' => get_class($client),
        ]);

        // Test that multiple users can be authorized for the same resource
        $clientAuthorizations = Authorization::where('authorizable_id', $client->id)->get();
        $this->assertCount(2, $clientAuthorizations);

        // Test that admin and manager are both authorized for client
        $authorizedUserIds = $clientAuthorizations->pluck('authorized_id')->toArray();
        $this->assertTrue(in_array($admin->id, $authorizedUserIds));
        $this->assertTrue(in_array($manager->id, $authorizedUserIds));
    }

    public function test_authorization_query_scopes_simulation()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Create various authorizations
        Authorization::create([
            'authorized_id' => $user1->id,
            'authorized_type' => get_class($user1),
            'authorizable_id' => $user2->id,
            'authorizable_type' => get_class($user2),
        ]);

        Authorization::create([
            'authorized_id' => $user1->id,
            'authorized_type' => get_class($user1),
            'authorizable_id' => $user3->id,
            'authorizable_type' => get_class($user3),
        ]);

        Authorization::create([
            'authorized_id' => $user2->id,
            'authorized_type' => get_class($user2),
            'authorizable_id' => $user3->id,
            'authorizable_type' => get_class($user3),
        ]);

        // Test querying authorizations by authorized user
        $user1Authorizations = Authorization::where('authorized_id', $user1->id)->get();
        $this->assertCount(2, $user1Authorizations);

        // Test querying authorizations by authorizable resource
        $user3Authorizations = Authorization::where('authorizable_id', $user3->id)->get();
        $this->assertCount(2, $user3Authorizations);

        // Test querying by specific relationship
        $user1ToUser2 = Authorization::where('authorized_id', $user1->id)
            ->where('authorizable_id', $user2->id)
            ->first();

        $this->assertNotNull($user1ToUser2);
        $this->assertEquals($user1->id, $user1ToUser2->authorized_id);
        $this->assertEquals($user2->id, $user1ToUser2->authorizable_id);
    }

    public function test_authorization_with_has_authorizable_trait_integration()
    {
        // Simulate how Authorization works with HasAuthorizable trait
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        // Test the morphTo relationships work correctly
        $this->assertEquals($authorizedUser->id, $authorization->authorized->id);
        $this->assertEquals($authorizableUser->id, $authorization->authorizable->id);

        // Test that we can find authorizations by both sides
        $authorizationsByAuthorized = Authorization::where('authorized_id', $authorizedUser->id)
            ->where('authorized_type', get_class($authorizedUser))
            ->get();

        $authorizationsByAuthorizable = Authorization::where('authorizable_id', $authorizableUser->id)
            ->where('authorizable_type', get_class($authorizableUser))
            ->get();

        $this->assertCount(1, $authorizationsByAuthorized);
        $this->assertCount(1, $authorizationsByAuthorizable);
        $this->assertEquals($authorization->id, $authorizationsByAuthorized->first()->id);
        $this->assertEquals($authorization->id, $authorizationsByAuthorizable->first()->id);
    }

    public function test_create_authorization_with_minimum_fields()
    {
        $authorizedUser = User::factory()->create();
        $authorizableUser = User::factory()->create();

        $authorization = Authorization::create([
            'authorized_id' => $authorizedUser->id,
            'authorized_type' => get_class($authorizedUser),
            'authorizable_id' => $authorizableUser->id,
            'authorizable_type' => get_class($authorizableUser),
        ]);

        $this->assertNotNull($authorization->id);
        $this->assertEquals($authorizedUser->id, $authorization->authorized_id);
        $this->assertEquals(get_class($authorizedUser), $authorization->authorized_type);
        $this->assertEquals($authorizableUser->id, $authorization->authorizable_id);
        $this->assertEquals(get_class($authorizableUser), $authorization->authorizable_type);
    }
}
