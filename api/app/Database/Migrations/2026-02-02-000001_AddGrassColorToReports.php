<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGrassColorToReports extends Migration
{
    public function up()
    {
        // La tabla ya existe en instalaciones reales; agregamos columna si falta
        if (!$this->db->tableExists('reports')) {
            return;
        }

        if ($this->db->fieldExists('grass_color', 'reports')) {
            return;
        }

        $this->forge->addColumn('reports', [
            'grass_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'grass_health',
            ],
        ]);
    }

    public function down()
    {
        if (!$this->db->tableExists('reports')) {
            return;
        }

        if (!$this->db->fieldExists('grass_color', 'reports')) {
            return;
        }

        $this->forge->dropColumn('reports', 'grass_color');
    }
}

