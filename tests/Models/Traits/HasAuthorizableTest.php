<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Entities\Authorization;
use Unusualify\Modularity\Entities\Traits\HasAuthorizable;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasAuthorizableTest extends ModelTestCase
{
    use RefreshDatabase;

    protected TestAuthorizableModel $model;

    protected $softDeletesModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tables
        Schema::create('test_authorizable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('test_authorizable_soft_deletes_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->model = new TestAuthorizableModel([
            'name' => 'Test Authorizable Model',
        ]);
        $this->model->save();

        $softDeletesModelClass = new class extends Model
        {
            use SoftDeletes, HasAuthorizable;

            protected $table = 'test_authorizable_soft_deletes_models';

            protected $fillable = ['name'];
        };

        $this->softDeletesModel = new $softDeletesModelClass([
            'name' => 'Test Soft Deletes Model',
        ]);
        $this->softDeletesModel->save();
    }

    public function test_model_uses_has_authorizable_trait()
    {
        $traits = class_uses_recursive($this->model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasAuthorizable', $traits);
    }

    public function test_authorization_record_relationship_exists()
    {
        $this->assertTrue(method_exists($this->model, 'authorizationRecord'));

        $relation = $this->model->authorizationRecord();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $relation);
    }

    public function test_authorization_record_relationship_configuration()
    {
        $relation = $this->model->authorizationRecord();

        $this->assertEquals('authorizable_id', $relation->getForeignKeyName());
        $this->assertEquals('authorizable_type', $relation->getMorphType());
        $this->assertEquals(Authorization::class, $relation->getRelated()::class);
    }

    public function test_authorized_user_relationship_exists()
    {
        $this->assertTrue(method_exists($this->model, 'authorizedUser'));

        $relation = $this->model->authorizedUser();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOneThrough::class, $relation);
    }

    public function test_can_create_authorization_record()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        // Set authorized_id and authorized_type, then save to trigger the saving event
        $this->model->authorized_id = $user->id;
        $this->model->authorized_type = get_class($user);
        $this->model->save();

        // Check that authorization record was created
        $authorizationTable = modularityConfig('tables.authorizations', 'um_authorizations');
        $this->assertDatabaseHas($authorizationTable, [
            'authorizable_id' => $this->model->id,
            'authorizable_type' => get_class($this->model),
            'authorized_id' => $user->id,
            'authorized_type' => get_class($user),
        ]);
    }

    public function test_boot_has_authorizable_retrieved_event()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        // Create authorization record directly
        $this->model->authorizationRecord()->create([
            'authorized_id' => $user->id,
            'authorized_type' => get_class($user),
        ]);

        // Retrieve the model fresh from database to trigger retrieved event
        $retrieved = TestAuthorizableModel::find($this->model->id);

        $this->assertEquals($user->id, $retrieved->authorized_id);
        $this->assertEquals(get_class($user), $retrieved->authorized_type);
    }

    public function test_boot_has_authorizable_updated_event()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        // First create an authorization
        $this->model->authorized_id = $user->id;
        $this->model->authorized_type = get_class($user);
        $this->model->save();

        // Now update with a different user
        $newUser = User::create([
            'name' => 'New Authorized User',
            'email' => 'newauthorized@example.com',
            'published' => true,
        ]);

        $this->model->authorized_id = $newUser->id;
        $this->model->authorized_type = get_class($newUser);
        $this->model->save();

        // Check that authorization record was updated
        $authorizationTable = modularityConfig('tables.authorizations', 'um_authorizations');
        $this->assertDatabaseHas($authorizationTable, [
            'authorizable_id' => $this->model->id,
            'authorizable_type' => get_class($this->model),
            'authorized_id' => $newUser->id,
            'authorized_type' => get_class($newUser),
        ]);

        // Check that old authorization doesn't exist
        $this->assertDatabaseMissing($authorizationTable, [
            'authorizable_id' => $this->model->id,
            'authorizable_type' => get_class($this->model),
            'authorized_id' => $user->id,
            'authorized_type' => get_class($user),
        ]);
    }

    public function test_boot_has_authorizable_saving_event_removes_fillable_fields()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        $this->model->authorized_id = $user->id;
        $this->model->authorized_type = get_class($user);
        $this->model->save();

        // Check that the fillable fields are removed from the model attributes
        $this->assertNull($this->model->getAttributeValue('authorized_id'));
        $this->assertNull($this->model->getAttributeValue('authorized_type'));
    }

    public function test_authorization_record_deleted_when_model_is_deleted()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        // Create authorization
        $this->model->authorized_id = $user->id;
        $this->model->authorized_type = get_class($user);
        $this->model->save();

        $authorizationId = $this->model->authorizationRecord->id;

        // Delete the model
        $this->model->delete();

        // Check that authorization record was deleted
        $authorizationTable = modularityConfig('tables.authorizations', 'um_authorizations');
        $this->assertDatabaseMissing($authorizationTable, [
            'id' => $authorizationId,
        ]);
    }

    public function test_authorization_record_deleted_when_soft_deletes_model_is_force_deleted()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        // Create authorization for soft deletes model
        $this->softDeletesModel->authorized_id = $user->id;
        $this->softDeletesModel->authorized_type = get_class($user);
        $this->softDeletesModel->save();

        $authorizationId = $this->softDeletesModel->authorizationRecord->id;

        // Force delete the model
        $this->softDeletesModel->forceDelete();

        // Check that authorization record was deleted
        $authorizationTable = modularityConfig('tables.authorizations', 'um_authorizations');
        $this->assertDatabaseMissing($authorizationTable, [
            'id' => $authorizationId,
        ]);
    }

    public function test_is_authorized_attribute_returns_true_when_authorized()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        // Create authorization
        $this->model->authorized_id = $user->id;
        $this->model->authorized_type = get_class($user);
        $this->model->save();

        $this->model->refresh();
        $this->assertTrue($this->model->is_authorized);
    }

    public function test_is_authorized_attribute_returns_false_when_not_authorized()
    {
        $this->assertFalse($this->model->is_authorized);
    }

    public function test_get_authorized_model_returns_default_when_no_authorization()
    {
        $authorizedModel = $this->model->getAuthorizedModel();
        $this->assertEquals(User::class, $authorizedModel);
    }

    public function test_get_authorized_model_returns_type_from_authorization_record()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        // Create authorization
        $this->model->authorizationRecord()->create([
            'authorized_id' => $user->id,
            'authorized_type' => get_class($user),
        ]);

        $authorizedModel = $this->model->getAuthorizedModel();
        $this->assertEquals(get_class($user), $authorizedModel);
    }

    public function test_get_default_authorized_model()
    {
        $defaultModel = TestAuthorizableModel::getDefaultAuthorizedModel();
        $this->assertEquals(User::class, $defaultModel);
    }

    public function test_get_user_for_has_authorization_returns_null_when_no_auth()
    {
        Auth::logout();

        $result = $this->model->getUserForHasAuthorization();
        $this->assertNull($result);
    }

    public function test_get_user_for_has_authorization_returns_provided_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'published' => true,
        ]);

        $result = $this->model->getUserForHasAuthorization($user);
        $this->assertSame($user, $result);
    }

    public function test_scope_has_authorization_filters_by_user()
    {
        $user1 = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'published' => true]);
        $user2 = User::create(['name' => 'User 2', 'email' => 'user2@example.com', 'published' => true]);

        // Create another authorizable model
        $model2 = new TestAuthorizableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Create authorizations
        $this->model->authorized_id = $user1->id;
        $this->model->authorized_type = get_class($user1);
        $this->model->save();

        $model2->authorized_id = $user2->id;
        $model2->authorized_type = get_class($user2);
        $model2->save();

        // Test scope filtering
        $modelsForUser1 = TestAuthorizableModel::hasAuthorization($user1)->get();
        $modelsForUser2 = TestAuthorizableModel::hasAuthorization($user2)->get();

        $this->assertCount(1, $modelsForUser1);
        $this->assertEquals($this->model->id, $modelsForUser1->first()->id);

        $this->assertCount(1, $modelsForUser2);
        $this->assertEquals($model2->id, $modelsForUser2->first()->id);
    }

    public function test_scope_has_authorization_with_roles()
    {
        $testAuthorizableModelWithRoleCheck = new class extends Model
        {
            use HasAuthorizable;

            protected $table = 'test_authorizable_models';

            protected $fillable = ['name'];

            public static $defaultAuthorizedModel = User::class;

            // Define roles that are allowed for authorization
            public static $authorizableRolesToCheck = ['manager'];
        };

        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'modularity']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'modularity']);
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'modularity']);

        // Create users with roles
        $adminUser = User::create(['name' => 'Admin User', 'email' => 'admin@example.com', 'published' => true]);
        $adminUser->assignRole($adminRole);

        $managerUser = User::create(['name' => 'Manager User', 'email' => 'manager@example.com', 'published' => true]);
        $managerUser->assignRole($managerRole);

        $managerUser2 = User::create(['name' => 'Manager User 2', 'email' => 'manager2@example.com', 'published' => true]);
        $managerUser2->assignRole($managerRole);

        $regularUser = User::create(['name' => 'Regular User', 'email' => 'user@example.com', 'published' => true]);
        $regularUser->assignRole($userRole);

        // Create a model that requires admin role
        $managerModel = new $testAuthorizableModelWithRoleCheck([
            'name' => 'Manager Model',
            'authorized_id' => $managerUser->id,
            'authorized_type' => get_class($managerUser),
        ]);
        $managerModel->save();

        $managerModel2 = new $testAuthorizableModelWithRoleCheck([
            'name' => 'Manager Model 2',
            'authorized_id' => $managerUser2->id,
            'authorized_type' => get_class($managerUser2),
        ]);
        $managerModel2->save();

        // Test scope with manager user (should pass)
        $modelsForManager = $testAuthorizableModelWithRoleCheck::hasAuthorization($managerUser)->get();
        $this->assertCount(1, $modelsForManager);

        // Test scope with manager user 1 (should pass)
        $modelsForManager2 = $testAuthorizableModelWithRoleCheck::hasAuthorization($managerUser2)->get();
        $this->assertCount(1, $modelsForManager2);

        // Test scope with admin user (should pass)
        $modelsForAdmin = $testAuthorizableModelWithRoleCheck::hasAuthorization($adminUser)->get();
        $this->assertCount(3, $modelsForAdmin);

        // Test scope with regular user (should pass)
        $modelsForRegularUser = $testAuthorizableModelWithRoleCheck::hasAuthorization($regularUser)->get();
        $this->assertCount(3, $modelsForRegularUser);
    }

    public function test_has_authorization_usage_returns_false_when_no_auth()
    {
        Auth::logout();
        $this->assertFalse($this->model->hasAuthorizationUsage());
    }

    public function test_scope_is_authorized_to_you()
    {
        $user1 = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'published' => true]);
        $user2 = User::create(['name' => 'User 2', 'email' => 'user2@example.com', 'published' => true]);

        // Create another model
        $model2 = new TestAuthorizableModel(['name' => 'Test Model 2']);
        $model2->save();

        // Create authorizations
        $this->model->authorized_id = $user1->id;
        $this->model->authorized_type = get_class($user1);
        $this->model->save();

        $model2->authorized_id = $user2->id;
        $model2->authorized_type = get_class($user2);
        $model2->save();

        $this->assertCount(0, TestAuthorizableModel::isAuthorizedToYou()->where('id', $this->model->id)->get());

        // Mock authentication for user1
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user1);

        $authorizedToYou = TestAuthorizableModel::isAuthorizedToYou()->get();

        $this->assertCount(1, $authorizedToYou);
        $this->assertEquals($this->model->id, $authorizedToYou->first()->id);
    }

    public function test_scope_is_authorized_to_your_role()
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

        // Create model authorized to manager1
        $this->model->authorized_id = $manager1->id;
        $this->model->authorized_type = get_class($manager1);
        $this->model->save();

        // Create another model authorized to editor
        $model2 = new TestAuthorizableModel(['name' => 'Test Model 2']);
        $model2->save();
        $model2->authorized_id = $editor->id;
        $model2->authorized_type = get_class($editor);
        $model2->save();

        // Test scope for manager2 (same role as manager1)
        $this->assertTrue(method_exists($this->model, 'scopeIsAuthorizedToYourRole'));

        $this->assertCount(0, TestAuthorizableModel::isAuthorizedToYourRole()->where('id', $this->model->id)->get());

        $modelsForManagerRole = TestAuthorizableModel::isAuthorizedToYourRole($manager2)->where('id', $this->model->id)->get();
        $this->assertCount(1, $modelsForManagerRole);
        $this->assertEquals($this->model->id, $modelsForManagerRole->first()->id);
    }

    public function test_scope_has_any_authorization()
    {
        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'published' => true]);

        // Create model with authorization
        $authorizedModel = new TestAuthorizableModel(['name' => 'Authorized Model']);
        $authorizedModel->save();
        $authorizedModel->authorized_id = $user->id;
        $authorizedModel->authorized_type = get_class($user);
        $authorizedModel->save();

        // Create model without authorization (this->model has no authorization)

        $hasAuthModels = TestAuthorizableModel::hasAnyAuthorization()->get();

        $this->assertCount(1, $hasAuthModels);
        $this->assertEquals($authorizedModel->id, $hasAuthModels->first()->id);
    }

    public function test_scope_unauthorized()
    {
        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'published' => true]);

        // Create model with authorization
        $authorizedModel = new TestAuthorizableModel(['name' => 'Authorized Model']);
        $authorizedModel->save();
        $authorizedModel->authorized_id = $user->id;
        $authorizedModel->authorized_type = get_class($user);
        $authorizedModel->save();

        // this->model has no authorization, so it should appear in unauthorized scope

        $unauthorizedModels = TestAuthorizableModel::unauthorized()->get();

        $this->assertCount(1, $unauthorizedModels);
        $this->assertEquals($this->model->id, $unauthorizedModels->first()->id);
    }

    public function test_scope_has_authorization_with_roles_to_check_without_authorizing_to_role()
    {
        $testModel = new class extends Model
        {
            use HasAuthorizable;

            protected $table = 'test_authorizable_models';

            protected $fillable = ['name'];

            public static $defaultAuthorizedModel = User::class;

            // Mimic PressRelease's allowedRolesForAuthorizationManagement
            protected $allowedRolesForAuthorizationManagement = [
                'superadmin',
                'admin',
                'manager',
            ];

            public static $authorizableRolesToCheck = ['manager'];
        };
        // Create roles similar to PressRelease model configuration
        $superAdminRole = Role::create(['name' => 'superadmin', 'guard_name' => 'modularity']);
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'modularity']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'modularity']);
        $clientManagerRole = Role::create(['name' => 'client-manager', 'guard_name' => 'modularity']);

        // Create users with different roles
        $superAdmin = User::create(['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'published' => true]);
        $superAdmin->assignRole($superAdminRole);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'published' => true]);
        $admin->assignRole($adminRole);

        $manager = User::create(['name' => 'Manager', 'email' => 'manager@example.com', 'published' => true]);
        $manager->assignRole($managerRole);

        $clientManager = User::create(['name' => 'Client Manager', 'email' => 'client@example.com', 'published' => true]);
        $clientManager->assignRole($clientManagerRole);

        // Create a model with PressRelease-like authorization configuration
        $pressReleaseModel = new $testModel(['name' => 'Press Release Model']);
        $pressReleaseModel->save();

        // Test authorization with different users
        $pressReleaseModel->authorized_id = $clientManager->id;
        $pressReleaseModel->authorized_type = get_class($clientManager);
        $pressReleaseModel->save();

        // Test that superadmin can access (should pass role check)
        $modelsForSuperAdmin = $testModel::hasAuthorization($superAdmin)->get();
        $this->assertCount(2, $modelsForSuperAdmin);

        // Test that admin can access (should pass role check)
        $modelsForAdmin = $testModel::hasAuthorization($admin)->get();
        $this->assertCount(2, $modelsForAdmin);

        // Test that manager can access (should pass role check)
        $modelsForManager = $testModel::hasAuthorization($manager)->get();
        $this->assertCount(0, $modelsForManager);

        // Test that authorized client manager can access
        $modelsForClientManager = $testModel::hasAuthorization($clientManager)->get();
        $this->assertCount(2, $modelsForClientManager);
    }

    public function test_authorization_usage_with_allowed_roles()
    {
        $testModel = new class extends Model
        {
            use HasAuthorizable;

            protected $table = 'test_authorizable_models';

            protected $fillable = ['name'];

            public static $defaultAuthorizedModel = User::class;

            // Only admin can manage authorizations
            protected $allowedRolesForAuthorizationManagement = ['admin'];
        };

        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'modularity']);
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'modularity']);

        // Create users
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'published' => true]);
        $admin->assignRole($adminRole);

        $regularUser = User::create(['name' => 'Regular User', 'email' => 'user@example.com', 'published' => true]);
        $regularUser->assignRole($userRole);

        // Create model with authorization management roles configured
        $restrictedModel = new $testModel(['name' => 'Restricted Model']);
        $restrictedModel->save();

        $this->assertTrue($restrictedModel->hasAuthorizationUsage($admin));
        $this->assertFalse($restrictedModel->hasAuthorizationUsage($regularUser));
    }

    public function test_authorization_events_are_dispatched()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        // Mock the events
        Event::fake([
            \Modules\SystemNotification\Events\AuthorizableCreated::class,
            \Modules\SystemNotification\Events\AuthorizableUpdated::class,
        ]);

        // Create authorization - should dispatch AuthorizableCreated
        $this->model->authorized_id = $user->id;
        $this->model->authorized_type = get_class($user);
        $this->model->save();

        Event::assertDispatched(\Modules\SystemNotification\Events\AuthorizableCreated::class);

        // Update authorization - should dispatch AuthorizableUpdated
        $newUser = User::create([
            'name' => 'New Authorized User',
            'email' => 'newauthorized@example.com',
            'published' => true,
        ]);

        $this->model->authorized_id = $newUser->id;
        $this->model->authorized_type = get_class($newUser);
        $this->model->save();

        Event::assertDispatched(\Modules\SystemNotification\Events\AuthorizableUpdated::class);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_authorizable_models');
        Schema::dropIfExists('test_authorizable_soft_deletes_models');
        parent::tearDown();
    }
}

// Test model that uses the HasAuthorizable trait
class TestAuthorizableModel extends Model
{
    use HasAuthorizable;

    protected $table = 'test_authorizable_models';

    protected $fillable = ['name'];

    public static $defaultAuthorizedModel = User::class;
}
