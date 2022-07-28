<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(App\Models\Category::class, function (Faker $faker) {
    return [
        'name' => $faker->text(32),
        'slug' => $faker->uuid,
        'icon' => '/images/logo.svg',
    ];
});
