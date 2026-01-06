<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@cesped365.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);

        User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);

        User::create([
            'name' => 'María González',
            'email' => 'maria@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);
    }
}

