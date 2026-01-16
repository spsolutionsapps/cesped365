<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Orden importante: primero users, luego gardens, luego reports
        $this->call('UserSeeder');
        $this->call('GardenSeeder');
        $this->call('ReportSeeder');
    }
}
