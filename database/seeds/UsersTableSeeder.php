<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\User::class, 100)->create()->each(
            function ($u) {
                $u->markers()->save(factory(App\Marker::class)->make());
            }
        );
    }
}
