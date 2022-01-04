<?php

namespace Database\Seeders;

use App\Models\Contributor;
use Illuminate\Database\Seeder;

class ContributorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        Contributor::factory()->create([
            'id' => '00000000-1000-1000-1000-000000000000'
        ]);

        Contributor::factory()->create([
            'id' => '00000000-2000-2000-2000-000000000000'
        ]);

        // Other contributors
        Contributor::factory()->count(10)->create();
    }
}
