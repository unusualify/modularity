<?php

namespace Unusualify\Modularity\Tests\Models;

use Unusualify\Modularity\Entities\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Tests\ModelTestCase;

class CompanyTest extends ModelTestCase
{

    use RefreshDatabase;

    public function test_get_table_company()
    {
        $company = new Company();

        $this->assertEquals(modularityConfig('tables.companies', 'companies'), $company->getTable());
    }

    public function test_create_company_with_factory()
    {
        Company::factory(3)->create();
        $this->assertCount(3, Company::all());
    }

    public function test_create_company_without_any_field()
    {
       $company1 = Company::create([]);
       $company2 = Company::create([]);
       $this->assertEquals(1, $company1->id);
       $this->assertEquals(2, $company2->id);
       $this->assertCount(2, Company::all());
    }

    public function test_create_company()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'Test Country',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456'
        ]);

        $this->assertEquals('123 Test St', $company->address);
        $this->assertEquals('Test City', $company->city);
        $this->assertEquals('Test State', $company->state);
        $this->assertEquals('Test Country', $company->country);
        $this->assertEquals('12345', $company->zip_code);
        $this->assertEquals('123-456-7890', $company->phone);
        $this->assertEquals('VAT123456', $company->vat_number);
        $this->assertEquals('TAX123456', $company->tax_id);

    }

    public function test_update_company()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'Test Country',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456'
        ]);

        $company->update([
            'name' => 'Updated Company',
            'address' => '456 Updated St',
            'city' => 'Updated City',
            'state' => 'Updated State',
            'country' => 'Updated Country',
            'zip_code' => '67890',
            'phone' => '987-654-3210',
            'vat_number' => 'VAT654321',
            'tax_id' => 'TAX654321'
        ]);

        $this->assertEquals('Updated Company', $company->name);
        $this->assertEquals('456 Updated St', $company->address);
        $this->assertEquals('Updated City', $company->city);
        $this->assertEquals('Updated State', $company->state);
        $this->assertEquals('Updated Country', $company->country);
        $this->assertEquals('67890', $company->zip_code);
        $this->assertEquals('987-654-3210', $company->phone);
        $this->assertEquals('VAT654321', $company->vat_number);
        $this->assertEquals('TAX654321', $company->tax_id);
    }

    public function test_delete_company()
    {
        $company1 = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'Test Country',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456'
        ]);

        $company2 = Company::create([
            'name' => 'Test Company 2',
            'address' => '456 Test St',
            'city' => 'Test City 2',
            'state' => 'Test State 2',
            'country' => 'Test Country 2',
            'zip_code' => '67890',
            'phone' => '111-222-3334',
            'vat_number' => 'VAT234567',
            'tax_id' => 'TAX234567'
        ]);

        $this->assertCount(2, Company::all());
        $company2->delete();
        $this->assertFalse(Company::all()->contains('id', $company2->id));
        $this->assertTrue(Company::all()->contains('id', $company1->id));
        $this->assertCount(1, Company::all());

    }

}
