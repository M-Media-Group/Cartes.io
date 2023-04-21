<?php

namespace Database\Factories;

use App\Models\Map;
use Illuminate\Database\Eloquent\Factories\Factory;

class MapFactory extends Factory
{
    protected $model = Map::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create();

        return [
            'title' => $faker->userName,
            'slug' => $faker->slug,
            'uuid' => $faker->uuid,
            'token' => $faker->uuid,
            'privacy' => 'public',
        ];
    }
}
