<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportVisualFlagsToReports extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('reports')) {
            return;
        }

        $columns = [];

        if (!$this->db->fieldExists('grass_even', 'reports')) {
            $columns['grass_even'] = [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'null'    => true,
                'default' => null,
                'after'   => 'grass_color',
            ];
        }

        if (!$this->db->fieldExists('spots', 'reports')) {
            $columns['spots'] = [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'null'    => true,
                'default' => null,
                'after'   => 'grass_even',
            ];
        }

        if (!$this->db->fieldExists('weeds_visible', 'reports')) {
            $columns['weeds_visible'] = [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'null'    => true,
                'default' => null,
                'after'   => 'spots',
            ];
        }

        if (!empty($columns)) {
            $this->forge->addColumn('reports', $columns);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('reports')) {
            return;
        }

        if ($this->db->fieldExists('weeds_visible', 'reports')) {
            $this->forge->dropColumn('reports', 'weeds_visible');
        }
        if ($this->db->fieldExists('spots', 'reports')) {
            $this->forge->dropColumn('reports', 'spots');
        }
        if ($this->db->fieldExists('grass_even', 'reports')) {
            $this->forge->dropColumn('reports', 'grass_even');
        }
    }
}

