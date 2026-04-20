<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@todof1.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Create regular demo user
        User::create([
            'name'     => 'Demo User',
            'email'    => 'user@todof1.com',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);

        // Seed F1 data in dependency order
        $this->call([
            TeamSeeder::class,
            CircuitSeeder::class,
            DriverSeeder::class,
            GrandPrixSeeder::class,
            RaceResultSeeder::class,
        ]);
    }
}
