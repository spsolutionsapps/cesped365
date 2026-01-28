<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportsTable extends Migration
{
    public function up()
    {
        // Verificar si la tabla ya existe
        if ($this->db->tableExists('reports')) {
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
            'date' => [
                'type' => 'DATE',
            ],
            'estado_general' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'cesped_parejo' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'color_ok' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'manchas' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'zonas_desgastadas' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'malezas_visibles' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'crecimiento_cm' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'compactacion' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'humedad' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'plagas' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'jardinero' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
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
        $this->forge->createTable('reports');
    }

    public function down()
    {
        $this->forge->dropTable('reports');
    }
}
