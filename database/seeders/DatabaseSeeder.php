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
        $this->call([
            MasterDataSeeder::class,
        ]);
        // User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Admin Sekolah',
            'username' => 'sekolah',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'level' => '1',
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Staf Bidang',
            'username' => 'staf',
            'email' => 'staf@example.com',
            'password' => bcrypt('password'),
            'level' => '2',
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kasubag',
            'username' => 'kasubag',
            'email' => 'kasubag@example.com',
            'password' => bcrypt('password'),
            'level' => '3',
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kabag',
            'username' => 'kabag',
            'email' => 'kabag@example.com',
            'password' => bcrypt('password'),
            'level' => '4',
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'KTU',
            'username' => 'ktu',
            'email' => 'ktu@example.com',
            'password' => bcrypt('password'),
            'level' => '5',
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kabid',
            'username' => 'kabid',
            'email' => 'kabid@example.com',
            'password' => bcrypt('password'),
            'level' => '6',
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'level' => '7',
            'status' => 1,
        ]);
    }
}
