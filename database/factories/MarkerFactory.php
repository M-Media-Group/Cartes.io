<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Objects\Point;

class MarkerFactory extends Factory
{

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(
            function ($marker) {
                $lat = $this->faker->latitude;
                $lng = $this->faker->longitude;

                if (!$lat || !$lng) {
                    // Throw an exception if the longitude is not valid
                    throw new \Exception('Invalid lat or lng');
                }

                $point = new Point($lat, $lng);

                /** @var Marker set here just so we can ovveride the type - we can't do it in the function because it would not be compatible */
                $marker = $marker;

                $marker->currentLocation()->create([
                    'location' => $point,
                ]);
            }
        );
    }
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = $this->faker;
        return
            [
                'token' => $faker->uuid,

                'description' => 'Test description ' . rand(),
                'category_id' => Category::factory(),
            ];
    }
}
