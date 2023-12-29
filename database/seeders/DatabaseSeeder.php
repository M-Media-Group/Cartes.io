<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(CategoriesTableSeeder::class);
        //$this->call(UsersTableSeeder::class);
        $this->call(MapsTableSeeder::class);
        //$this->call(MarkersTableSeeder::class);
    }
}
