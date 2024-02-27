<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return
            [
                'name' => $this->faker->words(2, true) . Str::random(5),
                'slug' => $this->faker->uuid,
                'icon' => '/images/marker-01.svg',
            ];
    }
}
