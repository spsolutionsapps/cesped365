<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateAdmin extends Command
{
    protected $signature = 'create:admin';
    protected $description = 'Create admin user';

    public function handle()
    {
        $user = User::where('email', 'admin@cesped365.com')->first();

        if ($user) {
            $this->info('Admin user already exists: ' . $user->email);
            return;
        }

        User::create([
            'name' => 'Administrador',
            'email' => 'admin@cesped365.com',
            'password' => bcrypt('Admin123!'),
            'role' => 'admin',
        ]);

        $this->info('Admin user created successfully!');
        $this->info('Email: admin@cesped365.com');
        $this->info('Password: Admin123!');
    }
}