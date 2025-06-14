<?php

namespace Unusualify\Modularity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Unusualify\Modularity\Entities\File;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Unusualify\Modularity\Entities\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = File::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'filename' => fake()->name(),
            'size' => fake()->numberBetween(100, 1000000),
        ];
    }
}
