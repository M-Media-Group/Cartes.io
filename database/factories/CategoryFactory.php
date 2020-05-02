<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Category::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
        'slug' => $faker->slug,
        'icon' => '/images/logo.svg',
    ];
});
