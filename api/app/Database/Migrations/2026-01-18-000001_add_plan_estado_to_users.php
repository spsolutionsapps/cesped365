<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanEstadoToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'plan' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'default' => 'Urbano',
                'after' => 'address'
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => 'Pendiente',
                'after' => 'plan'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['plan', 'estado']);
    }
}
