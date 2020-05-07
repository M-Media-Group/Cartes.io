<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Map::class, function (Faker $faker) {
    return [
        'title' => $faker->userName,
        'slug' => $faker->slug,
        'uuid' => $faker->uuid,
        'token' => $faker->uuid,
        'privacy' => 'public',
    ];
});
