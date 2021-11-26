<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        User::factory()->create([
            'id' => '00000000-1000-1000-1000-000000000000'
        ]);

        User::factory()->create([
            'id' => '00000000-2000-2000-2000-000000000000'
        ]);

        // Other users
        User::factory()->count(10)->create();
    }
}
