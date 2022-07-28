<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MapsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //factory(App\Models\Map::class, 100)->create();

        factory(App\Models\Map::class, 1)->create()->each(
            function ($u) {
                $u->markers()->saveMany(factory(App\Models\Marker::class, 1000)->make());
            }
        );
        // DB::table('maps')->insert([
        //     'name' => Str::random(10),
        //     'slug' => Str::random(10),
        //     'token' => Str::random(32),
        //     'uuid' => (string) Uuid::generate(4),
        // ]);
    }
}
