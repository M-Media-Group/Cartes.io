<?php

namespace Database\Seeders;

use App\Models\Category;
use \App\Models\Map;
use App\Models\Marker;
use App\Models\MarkerLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            ->create();

        $markerId = 100;

        $category = Category::factory()->create();
        $token = Str::random(32);

        for ($i = 0; $i < 50; $i++) {
            $data = [];
            $locationData = [];
            for ($v = 0; $v < 200; $v++) {

                $data[] = [
                    'id' => $markerId,
                    'token' => $token,
                    'category_id' => $category->id,
                    'map_id' => $map->id,
                ];

                $locationData[] = [
                    'marker_id' => $markerId,
                    'location' => DB::raw("ST_GeomFromText('POINT(" . rand(-180, 180) . " " . rand(-90, 90) . ")')"),
                ];

                $markerId++;
            }

            Marker::insert($data);
            MarkerLocation::insert($locationData);

            $markerId++;
        }

        // DB::table('maps')->insert([
        //     'name' => Str::random(10),
        //     'slug' => Str::random(10),
        //     'token' => Str::random(32),
        //     'uuid' => (string) Uuid::generate(4),
        // ]);
    }
}
