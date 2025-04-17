<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Tests\ModelTestCase;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Facades\Modularity;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Unusualify\Modularity\Entities\Filepond;

class UserTest extends ModelTestCase
{

    use RefreshDatabase;

    public function test_get_table_user()
    {
        $user = new User();
        $this->assertEquals(modularityConfig('tables.users', 'users'), $user->getTable());
    }

    public function test_create_user_with_factory()
    {
        User::factory(3)->create();
        $this->assertCount(3, User::all());
    }

    public function test_create_user()
    {

        $user = User::create([
            'name' => 'Test User',
            'surname' => 'Test Surname',
            'job_title' => 'Test Job Title',
            'email' => 'test_user@gmail.com',
            'language' => 'en',
            'timezone' => 'UTC',
            'phone' => '1234567890',
            'country' => 'Test Country',
            'password' => 'password',
            'published' => 1,
            'company_id' => null,
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('Test Surname', $user->surname);
        $this->assertEquals('Test Job Title', $user->job_title);
        $this->assertEquals('test_user@gmail.com',$user->email);
        $this->assertEquals('en', $user->language);
        $this->assertEquals('UTC', $user->timezone);
        $this->assertEquals('1234567890', $user->phone);
        $this->assertEquals('Test Country', $user->country);
        $this->assertEquals('password', $user->password);
        $this->assertEquals(1, $user->published);
    }

    public function test_update_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'surname' => 'Test Surname',
            'job_title' => 'Test Job Title',
            'email' => 'test_user@gmail.com',
            'language' => 'en',
            'timezone' => 'UTC',
            'phone' => '1234567890',
            'country' => 'Test Country',
            'password' => 'password',
            'published' => 1,
            'company_id' => null,
            ]);


        $user->update([
            'name' =>'Updated User',
            'surname' => 'Updated Surname',
            'job_title' => 'Updated Job Title',
            'email' => 'updated_test_user@gmail.com',
            'language' => 'fr',
            'timezone' => 'America/New_York',
            'phone' => '0987654321',
            'country' => 'Updated Country',
            'password' => 'new_password',
            'published' => 0,
        ]);

        $this->assertEquals('Updated User', $user->name);
        $this->assertEquals('Updated Surname', $user->surname);
        $this->assertEquals('Updated Job Title', $user->job_title);
        $this->assertEquals('updated_test_user@gmail.com',$user->email);
        $this->assertEquals('fr', $user->language);
        $this->assertEquals('America/New_York', $user->timezone);
        $this->assertEquals('0987654321', $user->phone);
        $this->assertEquals('Updated Country', $user->country);
        $this->assertEquals('new_password', $user->password);
        $this->assertEquals(0, $user->published);
    }

    public function test_delete_user()
    {
        $user1 = User::create([
            'name' => 'User1',
            'surname' => 'Surname1',
            'job_title' => 'Job Title1',
            'email' => 'user_1@gmail.com',
            'language' => 'en',
            'timezone' => 'UTC',
            'phone' => '1234567890',
            'country' => 'Country1',
            'password' => 'password',
            'published' => 1,
            'company_id' => null,
            ]);


        $user2 = User::create([
            'name' =>'User2',
            'surname' => 'Surname2',
            'job_title' => 'Job Title2',
            'email' => 'user_2@gmail.com',
            'language' => 'fr',
            'timezone' => 'America/New_York',
            'phone' => '0987654321',
            'country' => 'Country2',
            'password' => 'new_password',
            'published' => 0,
            'company_id' => null,
        ]);

        $this->assertCount(2, User::all());
        $user2->delete();
        $this->assertFalse(User::all()->contains('id', $user2->id));
        $this->assertTrue(User::all()->contains('id', $user1->id));
        $this->assertCount(1, User::all());

    }


    public function test_user_belongs_to_a_company()
    {

        $company = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            // Add other required fields
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'company_id' => $company->id
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $user->company()
        );

        $this->assertInstanceOf(Company::class, $user->company);
        $this->assertEquals($company->id, $user->company->id);
    }



    //checks for setImpersonating, stopImpersonating and isImpersonating
    public function test_impersonating()
    {
        $user1 = User::create([
            'name' => 'User1',
            'surname' => 'Surname1',
            'job_title' => 'Job Title1',
            'email' => 'user1@gmail.com',
            'language' => 'en',
        ]);

        $user2 = User::create([
            'name' => 'User2',
            'surname' => 'Surname2',
            'job_title' => 'Job Title2',
            'email' => 'user2@gmail.com',
            'language' => 'fr',
        ]);

        // Check if user1 is not impersonating
        $this->assertFalse($user1->isImpersonating());

        // Set impersonation
        $user1->setImpersonating($user2->id);
        $this->assertEquals($user2->id, Session::get('impersonate'));

        // Check if user1 is not impersonating
        $this->assertTrue($user1->isImpersonating());

        //Stop impersonation
        $user1->stopImpersonating();

        // Check if user1 is not impersonating anymore
        $this->assertFalse($user1->isImpersonating());

    }


    //checks for isSuperAdmin, isAdmin, isClient
    public function test_role_types()
    {

        $this->authGuardName = Modularity::getAuthGuardName();

        // Create the roles
        Role::create(['name' => 'superadmin', 'guard_name' => $this->authGuardName]);
        Role::create(['name' => 'admin', 'guard_name' => $this->authGuardName]);
        Role::create(['name' => 'client-manager', 'guard_name' => $this->authGuardName]);

        // Create test users
        $superadminUser = User::create([
            'name' => 'Super Admin',
            'surname' => 'User',
            'job_title' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'language' => 'en',
        ]);

        $adminUser = User::create([
            'name' => 'Admin',
            'surname' => 'User',
            'job_title' => 'Administrator',
            'email' => 'admin@example.com',
            'language' => 'en',
        ]);

        $clientUser = User::create([
            'name' => 'Client Manager',
            'surname' => 'User',
            'job_title' => 'Editor',
            'email' => 'editor@example.com',
            'language' => 'en',
        ]);

        $superadminUser->assignRole('superadmin');
        $this->assertTrue($superadminUser->isSuperAdmin());

        $adminUser->assignRole('admin');
        $this->assertTrue($adminUser->isAdmin());

        $clientUser->assignRole('client-manager');
        $this->assertEquals(1,$clientUser->isClient());

        $this->assertFalse($superadminUser->isAdmin());
        $this->assertFalse($adminUser->isSuperAdmin());
        $this->assertFalse($clientUser->isAdmin());

        $this->assertNotEquals(1,$superadminUser->isClient());
        $this->assertNotEquals(1,$adminUser->isClient());
    }

    public function test_valid_company()
    {
        //User just have id.
        $userWithNullCompanyId = new User();
        // if company id is null is valid.
        $this->assertTrue($userWithNullCompanyId->valid_company);

        //User has a company that partially filled.
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_user@gmail.com',
        ]);

        $this->assertTrue($user->valid_company);

        //User has a company that fully filled.
        $company = Company::create([
            'name' => 'Test Company',
            'address' => 'Some Address',
            'city' => 'Istanbul',
            'state' => 'Turkish Republic',
            'country' => 'Turkey',
            'zip_code' => '12345',
            'phone' => '1234567890',
            'tax_id' => 'TAX123456',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->update([
            'company_id' => $company->id,
        ]);

        $this->assertFalse($user->valid_company);

        $company->update([
            'vat_number' => 'VAT123456',
        ]);

        $user->refresh();

        $this->assertTrue($user->valid_company);

    }

    public function test_company_name()
    {
        $company = Company::create([
            'name' => 'Test Company Inc.',
            // Add other required fields for your Company model
        ]);

        // Create your model with a relationship to this company
        $userWithCompany = User::create([
            'name' => 'Erdem',
            'email' => 'erdem@hotmail.com',
            'company_id' => $company->id,
        ]);

        $userWithoutCompany = User::create([
            'name' => 'Celik',
            'email' => 'celik@hotmail.com',
        ]);

        $this->assertEquals('Test Company Inc.', $userWithCompany->company_name);
        $this->assertNotEquals('Test Company Inc.', $userWithoutCompany->company_name);
    }

    public function test_name_with_company()
    {
        $companyWithName = Company::create([
            'name' => 'Test Company Inc.',
            'adresss' => '123 Test St',
            // Add other required fields for your Company model
        ]);

        $companyWithoutName = Company::create([
            'address' => '456 Test St',
        ]);

        // Create your model with a relationship to this company
        $user = User::create([
            'name' => 'Erdem',
            'email' => 'erdem@hotmail.com',
            'company_id' => $companyWithName->id,
        ]);

        $result = $user->name . ' (' . $user->company_name.')';
        $this->assertEquals($result, $user->nameWithCompany);

        // $user->refresh();

        $user->update([
            'company_id' => $companyWithoutName->id,
        ]);

        $user->refresh();

        $result = $user->name . ' (' . $user->company_name.'System User'.')';
        $this->assertEquals($result, $user->nameWithCompany);

    }

    public function test_scope_company_user()
    {
        $userWithoutCompany = User::create([
            'name' => 'User Without Company',
            'email' => 'user@gmail.com',
        ]);

        $countFromQuery = User::companyUser()->count();
        $this->assertEquals(0, $countFromQuery);

        $company1 = Company::create([
            'name' => 'Company 1',
            'address' => '123 Company St',
        ]);

        $companyUser1 = User::create([
            'name' => 'Company User 1',
            'email' => 'company_user1@gmail.com',
            'company_id' => $company1->id,
        ]);

        $countFromQuery = User::companyUser()->count();
        $this->assertEquals(1, $countFromQuery);
    }

    public function test_avatar()
    {
        //without any related filepond
        $userWithoutFilepond = User::factory()->create();
        $this->assertEquals('/vendor/modularity/jpg/anonymous.jpg', $userWithoutFilepond->avatar);

        // Create a filepond with role 'avatar' for this user
        $user = User::factory()->create();

        $firstFilepond = Filepond::create([
            'uuid' => 'first-uuid-123',
            'file_name' => 'avatar1.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'avatar',
            'locale' => 'en'
        ]);

        $expectedSource = route('filepond.preview', ['uuid' => 'first-uuid-123']);
        $this->assertEquals($expectedSource, $user->avatar);

        $secondFilepond = Filepond::create([
            'uuid' => 'second-uuid-456',
            'file_name' => 'avatar2.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'avatar',
            'locale' => 'en'
        ]);

        // Expected source URL from the first filepond
        $expectedSource = route('filepond.preview', ['uuid' => 'first-uuid-123']);
        // Check that the avatar returns the first filepond's source
        $this->assertEquals($expectedSource, $user->avatar);

        // $this->assertEquals($expectedSource, $firstFilepond->mediableFormat()['source']);
        // $this->assertNotEquals($expectedSource, $secondFilepond->mediableFormat()['source']);
    }

    public function test_company_has_many_users()
    {
        $company = Company::create([
            'name' => 'Company 1',
            'address' => '123 Test St',
        ]);

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@gmail.com',
            'company_id' => $company->id,
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@gmail.com',
            'company_id' => $company->id,
        ]);

        $company2 = Company::create([
            'name' => 'Company 2',
            'address' => '456 Test St',
        ]);

        $user3 = User::create([
            'name' => 'User 3',
            'email' => 'user3@gmail.com',
            'company_id' => $company2->id,
        ]);

        $relation = $company->users();
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('company_id', $relation->getForeignKeyName());
        $this->assertInstanceOf(User::class, $relation->getRelated());

        $this->assertCount(2, $company->users);
        $this->assertCount(1, $company2->users);

        foreach ($company->users as $user) {
            $this->assertEquals($company->id, $user->company_id);
        }

        foreach ($company2->users as $user) {
            $this->assertEquals($company2->id, $user->company_id);
        }

        $this->assertFalse($company->users->contains($user3));
        $this->assertFalse($company2->users->contains($user1));
        $this->assertFalse($company2->users->contains($user2));


    }



}
