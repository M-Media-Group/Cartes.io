<?php

namespace Database\Seeders;

use \App\Models\Map;
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

        $map = Map::factory(
            [
                'title' => 'Tempetetasd',
                'privacy' => 'public',
            ]
        )
            ->hasMarkers(15000)
            ->create();

        // DB::table('maps')->insert([
        //     'name' => Str::random(10),
        //     'slug' => Str::random(10),
        //     'token' => Str::random(32),
        //     'uuid' => (string) Uuid::generate(4),
        // ]);
    }
}
