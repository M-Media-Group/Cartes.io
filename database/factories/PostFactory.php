<?php

use Faker\Generator as Faker;

$factory->define(App\Post::class, function (Faker $faker) {
    return [
        'title' => $faker->bs,
        'body_markdown' => $faker->paragraphs(25, true),
        'excerpt' => $faker->sentences(3, true),
        'slug' => $faker->slug,
        'user_id' => $faker->randomDigit,
        'header_image' => '/images/villefranche.jpg',
        'published_at' => now(),
    ];
});
