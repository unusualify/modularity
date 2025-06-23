<?php

namespace Unusualify\Modularity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Unusualify\Modularity\Entities\Company;

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
            'zip_code' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'vat_number' => fake()->unique()->randomNumber(2),
            'tax_id' => fake()->unique()->randomNumber(8),
        ];
    }
}
