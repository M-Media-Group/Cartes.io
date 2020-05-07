<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Category::class, function (Faker $faker) {
    return [
        'name' => $faker->uuid,
        'slug' => $faker->uuid,
        'icon' => '/images/logo.svg',
    ];
});
