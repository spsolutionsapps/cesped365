<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptionsTable extends Migration
{
    public function up()
    {
        // Verificar si la tabla ya existe
        if ($this->db->tableExists('subscriptions')) {
            return; // La tabla ya existe, no hacer nada
        }
        
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'frequency' => [
                'type' => 'ENUM',
                'constraint' => ['mensual', 'trimestral', 'semestral', 'anual'],
                'default' => 'mensual',
            ],
            'visits_per_month' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 4,
                'comment' => 'Número de visitas por mes',
            ],
            'features' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON con características del plan',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->createTable('subscriptions');
    }

    public function down()
    {
        $this->forge->dropTable('subscriptions');
    }
}
