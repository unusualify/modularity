<?php

namespace  Unusualify\Modularity\Database\Factories;

use Unusualify\Modularity\Entities\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(29),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->word(),
            'country' => fake()->countryCode(),
            'zip_code' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'vat_number' => fake()->unique()->randomNumber(2),
            'tax_id' => fake()->unique()->randomNumber(8),
        ];
    }
}
