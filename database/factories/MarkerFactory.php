<?php

namespace Database\Factories;

use App\Models\Category;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarkerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = $this->faker;
        $point = new Point($faker->latitude, $faker->longitude);

        return
            [
                'token' => $faker->uuid,
                'location' => $point,
                'description' => $faker->words(1, true),
                'category_id' => Category::factory(),
            ];
    }
}
