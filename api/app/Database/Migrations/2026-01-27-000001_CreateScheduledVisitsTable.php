<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduledVisitsTable extends Migration
{
    public function up()
    {
        // Verificar si la tabla ya existe
        if ($this->db->tableExists('scheduled_visits')) {
            return; // La tabla ya existe, no hacer nada
        }
        
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'garden_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'scheduled_date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'scheduled_time' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
                'comment'    => 'Hora programada (ej: 09:00, 14:30)',
            ],
            'gardener_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['programada', 'completada', 'cancelada'],
                'default'    => 'programada',
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
        $this->forge->addForeignKey('garden_id', 'gardens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('scheduled_date');
        $this->forge->addKey('status');
        $this->forge->createTable('scheduled_visits');
    }

    public function down()
    {
        $this->forge->dropTable('scheduled_visits');
    }
}
