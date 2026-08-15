<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Admin Sekolah',
            'username' => 'sekolah',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'level' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Staf Bidang',
            'username' => 'staf',
            'email' => 'staf@example.com',
            'password' => bcrypt('password'),
            'level' => 2,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kasubag',
            'username' => 'kasubag',
            'email' => 'kasubag@example.com',
            'password' => bcrypt('password'),
            'level' => 3,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kabag',
            'username' => 'kabag',
            'email' => 'kabag@example.com',
            'password' => bcrypt('password'),
            'level' => 4,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'KTU',
            'username' => 'ktu',
            'email' => 'ktu@example.com',
            'password' => bcrypt('password'),
            'level' => 5,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kabid',
            'username' => 'kabid',
            'email' => 'kabid@example.com',
            'password' => bcrypt('password'),
            'level' => 6,
        ]);
    }
}
