<?php

namespace Database\Factories;

use Faker\Generator as Faker;
use Grimzy\LaravelMysqlSpatial\Types\Point;

$factory->define(App\Models\Marker::class, function (Faker $faker) {
    $point = new Point($faker->latitude, $faker->longitude);

    return [

        'token' => $faker->uuid,
        'location' => $point,
        'description' => $faker->text(191),
        'category_id' => function () {
            return factory(App\Models\Category::class)->create()->id;
        },
    ];
});
