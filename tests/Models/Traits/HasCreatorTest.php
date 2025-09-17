<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Modules\SystemUser\Entities\Company;
use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Entities\CreatorRecord;
use Unusualify\Modularity\Entities\Traits\HasCreator;
use Unusualify\Modularity\Entities\Traits\HasUuid;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasCreatorTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_creator_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create test model instance
        $this->model = new TestCreatorModel(['name' => 'Test Model']);
        $this->model->save();
    }

    public function test_trait_initialization()
    {
        // Test that the trait properly initializes fillable attributes
        $model = new TestCreatorModel();
        $fillable = $model->getFillable();

        $this->assertContains('custom_creator_id', $fillable);
        $this->assertContains('custom_creator_type', $fillable);
        $this->assertContains('custom_guard_name', $fillable);
    }

    public function test_creator_record_relationship()
    {
        // Test the morphOne relationship to CreatorRecord
        $relationship = $this->model->creatorRecord();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $relationship);
        $this->assertEquals(CreatorRecord::class, get_class($relationship->getRelated()));
    }

    public function test_creator_relationship()
    {
        // Create a user and creator record
        $user = User::create(['name' => 'Creator User', 'email' => 'creator@example.com', 'published' => true]);
        $user->company()->create(['name' => 'Creator Company']);

        // Create creator record manually for testing
        $this->model->creatorRecord()->create([
            'creator_id' => $user->id,
            'creator_type' => get_class($user),
            'guard_name' => 'modularity',
        ]);

        // Test the hasOneThrough relationship
        $relationship = $this->model->creator();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOneThrough::class, $relationship);

        // Test that we can retrieve the creator
        $creator = $this->model->creator;
        $this->assertInstanceOf(User::class, $creator);
        $this->assertEquals($user->id, $creator->id);
        $this->assertEquals($user->name, $creator->name);
    }

    public function test_company_relationship()
    {
        // Test the company relationship (complex join)
        $relationship = $this->model->company();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relationship);
    }

    public function test_automatic_creator_record_creation_on_authenticated_user()
    {
        // Mock authentication
        $user = User::create(['name' => 'Auth User', 'email' => 'auth@example.com', 'published' => true]);

                        // Create a proper mock for the guard
        $guardMock = Mockery::mock();
        $guardMock->shouldReceive('id')->andReturn($user->id);
        $guardMock->name = 'modularity'; // Set as property, not method

        $providerMock = Mockery::mock();
        $providerMock->shouldReceive('getModel')->andReturn(get_class($user));

        $guardMock->shouldReceive('getProvider')->andReturn($providerMock);

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('guard')->andReturn($guardMock);

        // Create a new model to trigger the created event
        $newModel = new TestCreatorModel(['name' => 'New Model']);
        $newModel->save();

        // Check that creator record was created
        $this->assertDatabaseHas(modularityConfig('tables.creator_records', 'um_creator_records'), [
            'creatable_id' => $newModel->id,
            'creatable_type' => get_class($newModel),
            'creator_id' => $user->id,
            'creator_type' => get_class($user),
            'guard_name' => 'modularity',
        ]);
    }

    public function test_custom_creator_record_creation()
    {
        // Create a custom creator user
        $customCreator = User::create(['name' => 'Custom Creator', 'email' => 'custom@example.com', 'published' => true]);

        // Create model with custom creator
        $modelWithCustomCreator = new TestCreatorModel([
            'name' => 'Model with Custom Creator',
            'custom_creator_id' => $customCreator->id,
            'custom_creator_type' => get_class($customCreator),
            'custom_guard_name' => 'modularity',
        ]);
        $modelWithCustomCreator->save();

        // Check that creator record was created with custom values
        $this->assertDatabaseHas(modularityConfig('tables.creator_records', 'um_creator_records'), [
            'creatable_id' => $modelWithCustomCreator->id,
            'creatable_type' => get_class($modelWithCustomCreator),
            'creator_id' => $customCreator->id,
            'creator_type' => get_class($customCreator),
            'guard_name' => 'modularity',
        ]);

        // Check that custom fields were removed from model attributes
        $this->assertNull($modelWithCustomCreator->custom_creator_id);
        $this->assertNull($modelWithCustomCreator->custom_creator_type);
        $this->assertNull($modelWithCustomCreator->custom_guard_name);
    }

    public function test_creator_record_deletion_on_model_deletion()
    {
        // Create user and creator record
        $user = User::create(['name' => 'User to Delete', 'email' => 'delete@example.com', 'published' => true]);

        $this->model->creatorRecord()->create([
            'creator_id' => $user->id,
            'creator_type' => get_class($user),
            'guard_name' => 'modularity',
        ]);

        $creatorRecordId = $this->model->creatorRecord->id;

        // Delete the model
        $this->model->delete();

        // Check that creator record was also deleted
        $this->assertDatabaseMissing(modularityConfig('tables.creator_records', 'um_creator_records'), [
            'id' => $creatorRecordId,
        ]);
    }

    public function test_scope_is_creator()
    {
        // Create users
        $creator1 = User::create(['name' => 'Creator 1', 'email' => 'creator1@example.com', 'published' => true]);
        $creator2 = User::create(['name' => 'Creator 2', 'email' => 'creator2@example.com', 'published' => true]);

        // Create models with different creators
        $model1 = new TestCreatorModel(['name' => 'Model 1']);
        $model1->save();
        $model1->creatorRecord()->create([
            'creator_id' => $creator1->id,
            'creator_type' => get_class($creator1),
            'guard_name' => 'modularity',
        ]);

        $model2 = new TestCreatorModel(['name' => 'Model 2']);
        $model2->save();
        $model2->creatorRecord()->create([
            'creator_id' => $creator2->id,
            'creator_type' => get_class($creator2),
            'guard_name' => 'modularity',
        ]);

        // Test scope filtering by creator
        $creator1Models = TestCreatorModel::isCreator($creator1->id, 'modularity')->get();
        $this->assertCount(1, $creator1Models);
        $this->assertEquals($model1->id, $creator1Models->first()->id);

        $creator2Models = TestCreatorModel::isCreator($creator2->id, 'modularity')->get();
        $this->assertCount(1, $creator2Models);
        $this->assertEquals($model2->id, $creator2Models->first()->id);
    }

    public function test_scope_is_my_creation()
    {
        // Create user
        $user = User::create(['name' => 'My Creator', 'email' => 'my@example.com', 'published' => true]);

        // // Create a proper mock for the guard
        // $guardMock = Mockery::mock();
        // $guardMock->name = 'modularity'; // Set as property, not method

        // // Mock authentication
        // Auth::shouldReceiveOnce('check')->andReturn(true);
        // Auth::shouldReceiveOnce('guard')->andReturn($guardMock);
        // Auth::shouldReceiveOnce('id')->andReturn($user->id);

        // Create model with this creator
        $this->model->creatorRecord()->create([
            'creator_id' => $user->id,
            'creator_type' => get_class($user),
            'guard_name' => 'modularity',
        ]);

        // Create another model with different creator
        $otherUser = User::create(['name' => 'Other Creator', 'email' => 'other@example.com', 'published' => true]);
        $otherModel = new TestCreatorModel(['name' => 'Other Model']);
        $otherModel->save();
        // Mock authentication
        $otherModel->creatorRecord()->create([
            'creator_id' => $otherUser->id,
            'creator_type' => get_class($otherUser),
            'guard_name' => 'modularity',
        ]);

        // Test scope
        $myCreations = TestCreatorModel::isMyCreation($user, 'modularity')->get();
        $this->assertCount(1, $myCreations);
        $this->assertEquals($this->model->id, $myCreations->first()->id);
    }

    public function test_scope_has_access_to_creation_with_roles()
    {
        $testCreatorModelWithRoles = new class extends Model
        {
            use HasCreator;

            protected $table = 'test_creator_models';
            protected $fillable = ['name'];

            public static $defaultHasCreatorModel = User::class;

            // Override authorized roles (from ModelHelpers.php)
            public $rolesHasAccessToCreation = [
                'admin',
                'manager',
                'editor',
            ];

            public $companyRolesHasAccessToCreation = [
                'client-manager',
                'client-assistant',
            ];
        };

        // Create roles similar to ModelHelpers.php
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'modularity']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'modularity']);
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'modularity']);
        $clientManagerRole = Role::create(['name' => 'client-manager', 'guard_name' => 'modularity']);
        $reporterRole = Role::create(['name' => 'reporter', 'guard_name' => 'modularity']);

        // Create users with different roles
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'published' => true]);
        $admin->assignRole($adminRole);

        $manager = User::create(['name' => 'Manager', 'email' => 'manager@example.com', 'published' => true]);
        $manager->assignRole($managerRole);

        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.com', 'published' => true]);
        $editor->assignRole($editorRole);

        $clientManager = User::create(['name' => 'Client Manager', 'email' => 'client@example.com', 'published' => true]);
        $clientManager->assignRole($clientManagerRole);

        $reporter = User::create(['name' => 'Reporter', 'email' => 'reporter@example.com', 'published' => true]);
        $reporter->assignRole($reporterRole);

        // Create model with client manager as creator
        $this->model->creatorRecord()->create([
            'creator_id' => $clientManager->id,
            'creator_type' => get_class($clientManager),
            'guard_name' => 'modularity',
        ]);

        // Test access with different roles using anonymous model
        $modelWithRoles = new $testCreatorModelWithRoles(['name' => 'Model with Roles']);
        $modelWithRoles->save();
        $modelWithRoles->creatorRecord()->create([
            'creator_id' => $clientManager->id,
            'creator_type' => get_class($clientManager),
            'guard_name' => 'modularity',
        ]);

        // Admin should have access (authorized role)
        $adminAccess = $testCreatorModelWithRoles::hasAccessToCreation($admin)->get();
        $this->assertCount(2, $adminAccess);

        // Manager should have access (authorized role)
        $managerAccess = $testCreatorModelWithRoles::hasAccessToCreation($manager)->get();
        $this->assertCount(2, $managerAccess);

        // Editor should have access (authorized role)
        $editorAccess = $testCreatorModelWithRoles::hasAccessToCreation($editor)->get();
        $this->assertCount(2, $editorAccess);

        // Client manager should have access (creator)
        $clientManagerAccess = $testCreatorModelWithRoles::hasAccessToCreation($clientManager)->get();
        $this->assertCount(1, $clientManagerAccess);

        // Reporter should not have access (not in authorized roles and not creator)
        $reporterAccess = $testCreatorModelWithRoles::hasAccessToCreation($reporter)->get();
        $this->assertCount(0, $reporterAccess);
    }

    public function test_scope_has_access_to_creation_with_company_access()
    {
        $testCreatorModelWithCompanyAccess = new class extends Model
        {
            use HasCreator;

            protected $table = 'test_creator_models';
            protected $fillable = ['name'];

            public static $defaultHasCreatorModel = User::class;

            public $authorizedUserRolesForCreatorRecord = [
                'client-manager',
                'client-assistant',
            ];
        };

        // Create roles
        $company = Company::create(['name' => 'Company']);
        $clientManagerRole = Role::create(['name' => 'client-manager', 'guard_name' => 'modularity']);

        // Create users in the same company
        $clientManager1 = User::create([
            'name' => 'Client Manager 1',
            'email' => 'client1@example.com',
            'published' => true,
            'company_id' => $company->id,
        ]);
        $clientManager1->assignRole($clientManagerRole);

        $clientManager2 = User::create([
            'name' => 'Client Manager 2',
            'email' => 'client2@example.com',
            'published' => true,
            'company_id' => $company->id,
        ]);
        $clientManager2->assignRole($clientManagerRole);

        $company2 = Company::create(['name' => 'Company 2']);

        $clientManager3 = User::create([
            'name' => 'Client Manager 3',
            'email' => 'client3@example.com',
            'published' => true,
            'company_id' => $company2->id, // Different company
        ]);
        $clientManager3->assignRole($clientManagerRole);

        // Create model with client manager 1 as creator
        $modelWithCompany = new $testCreatorModelWithCompanyAccess(['name' => 'Company Model']);
        $modelWithCompany->save();
        $modelWithCompany->creatorRecord()->create([
            'creator_id' => $clientManager1->id,
            'creator_type' => get_class($clientManager1),
            'guard_name' => 'modularity',
        ]);

        // Client manager 2 should have access (same company)
        $sameCompanyAccess = $testCreatorModelWithCompanyAccess::hasAccessToCreation($clientManager2)->get();
        $this->assertCount(1, $sameCompanyAccess);

        // Client manager 3 should not have access (different company)
        $differentCompanyAccess = $testCreatorModelWithCompanyAccess::hasAccessToCreation($clientManager3)->get();
        $this->assertCount(0, $differentCompanyAccess);
    }

    public function test_abort_creator_role_exceptions()
    {
        $testCreatorModelWithAbortExceptions = new class extends Model
        {
            use HasCreator;

            protected $table = 'test_creator_models';
            protected $fillable = ['name'];

            public static $defaultHasCreatorModel = User::class;
            protected static $abortCreatorRoleExceptions = true;
        };

        // Test with model that aborts role exceptions
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'modularity']);
        $reporterRole = Role::create(['name' => 'reporter', 'guard_name' => 'modularity']);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'published' => true]);
        $admin->assignRole($adminRole);

        $reporter = User::create(['name' => 'Reporter', 'email' => 'reporter@example.com', 'published' => true]);
        $reporter->assignRole($reporterRole);

        // Create model that aborts role exceptions
        $strictModel = new $testCreatorModelWithAbortExceptions(['name' => 'Strict Model']);
        $strictModel->save();
        $strictModel->creatorRecord()->create([
            'creator_id' => $reporter->id,
            'creator_type' => get_class($reporter),
            'guard_name' => 'modularity',
        ]);

        // Admin should not have access when role exceptions are aborted
        $adminAccess = $testCreatorModelWithAbortExceptions::hasAccessToCreation($admin)->get();
        $this->assertCount(0, $adminAccess);

        // Only the creator (reporter) should have access
        $creatorAccess = $testCreatorModelWithAbortExceptions::hasAccessToCreation($reporter)->get();
        $this->assertCount(1, $creatorAccess);
    }

    public function test_get_creatable_class()
    {
        // Test default behavior
        $creatableClass = $this->model->getCreatableClass();
        $this->assertSame($this->model, $creatableClass);

        // Test with custom creatable class
        $testCreatorModelWithCustomCreatable = new class extends Model
        {
            use HasCreator;

            protected $table = 'test_creator_models';
            protected $fillable = ['name'];
            public static $creatableClass = TestCreatorModel::class;

            public static $defaultHasCreatorModel = User::class;
        };

        $customModel = new $testCreatorModelWithCustomCreatable(['name' => 'Custom Model']);
        $customModel->save();

        $creatableClass = $customModel->getCreatableClass();
        $this->assertInstanceOf(TestCreatorModel::class, $creatableClass);
        $this->assertEquals($customModel->id, $creatableClass->id);
    }

    public function test_default_creator_model()
    {
        // Test default creator model
        $defaultModel = TestCreatorModel::getDefaultCreatorModel();
        $this->assertEquals(User::class, $defaultModel);

        // Test with custom default
        $testCreatorModelWithCustomDefault = new class extends Model
        {
            use HasCreator;

            protected $table = 'test_creator_models';
            protected $fillable = ['name'];

            public static $defaultHasCreatorModel = User::class;
        };

        $customDefault = $testCreatorModelWithCustomDefault::getDefaultCreatorModel();
        $this->assertEquals(User::class, $customDefault);
    }

    public function test_creator_model_determination()
    {
        // Create user
        $user = User::create(['name' => 'Test Creator', 'email' => 'test@example.com', 'published' => true]);

        // Test with existing creator record - check that creator relationship works
        $this->model->creatorRecord()->create([
            'creator_id' => $user->id,
            'creator_type' => get_class($user),
            'guard_name' => 'modularity',
        ]);

        // Test that the creator relationship returns the correct user
        $creator = $this->model->creator;
        $this->assertInstanceOf(User::class, $creator);
        $this->assertEquals($user->id, $creator->id);

        // Test that creator record has the correct creator_type
        $creatorRecord = $this->model->creatorRecord;
        $this->assertEquals(get_class($user), $creatorRecord->creator_type);

        // Test with no creator record - should not have a creator
        $newModel = new TestCreatorModel(['name' => 'No Creator']);
        $newModel->save();

        $this->assertNull($newModel->creator);
        $this->assertFalse($newModel->creatorRecord()->exists());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

// Basic test model
class TestCreatorModel extends Model
{
    use HasCreator;

    protected $table = 'test_creator_models';
    protected $fillable = ['name', 'description'];

    public static $defaultHasCreatorModel = User::class;
}
