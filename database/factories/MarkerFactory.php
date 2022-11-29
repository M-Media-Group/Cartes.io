<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Grimzy\LaravelMysqlSpatial\Types\Point;

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
                'description' => 'Test description ' . rand(),
                'category_id' => Category::factory(),
            ];
    }
}
