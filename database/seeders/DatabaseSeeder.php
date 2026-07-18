<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@supply.io'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('Admin@12345'),
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@supply.io'],
            [
                'name'     => 'Demo User',
                'password' => Hash::make('User@12345'),
                'role'     => 'user',
            ]
        );

        $this->call([
            CountrySeeder::class,
            PortSeeder::class,
            SentimentLexiconSeeder::class,
        ]);
    }
}
