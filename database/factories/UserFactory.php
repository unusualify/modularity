<?php

namespace Unusualify\Modularity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company_id' => null,
            'surname' => fake()->name(),
            'job_title' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'language' => fake()->languageCode(),
            'timezone' => fake()->timezone(),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->country(),
            'email_verified_at' => now(),
            'password' => Hash::make(env('DEFAULT_USER_PASSWORD')), // password
            'remember_token' => Str::random(10),
            'published' => 0,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
