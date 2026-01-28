<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportImagesTable extends Migration
{
    public function up()
    {
        // Verificar si la tabla ya existe
        if ($this->db->tableExists('report_images')) {
            return; // La tabla ya existe, no hacer nada
        }
        
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'report_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'image_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('report_id', 'reports', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('report_images');
    }

    public function down()
    {
        $this->forge->dropTable('report_images');
    }
}
