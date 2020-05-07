<?php

use Faker\Generator as Faker;
use Grimzy\LaravelMysqlSpatial\Types\Point;

$factory->define(App\Models\Incident::class, function (Faker $faker) {
    $point = new Point($faker->longitude, $faker->latitude);

    return [

        'token' => $faker->uuid,
        'location' => $point,
        'category_id' => function () {
            return factory(App\Models\Category::class)->create()->id;
        },
    ];
});
