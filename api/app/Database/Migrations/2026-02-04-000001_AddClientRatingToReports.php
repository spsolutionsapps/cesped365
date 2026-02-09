<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClientRatingToReports extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('reports')) {
            return;
        }

        if (!$this->db->fieldExists('client_rating', 'reports')) {
            $this->forge->addColumn('reports', [
                'client_rating' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'technician_notes',
                ],
            ]);
        }

        if (!$this->db->fieldExists('client_feedback', 'reports')) {
            $this->forge->addColumn('reports', [
                'client_feedback' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'client_rating',
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('reports')) {
            return;
        }

        if ($this->db->fieldExists('client_feedback', 'reports')) {
            $this->forge->dropColumn('reports', 'client_feedback');
        }
        if ($this->db->fieldExists('client_rating', 'reports')) {
            $this->forge->dropColumn('reports', 'client_rating');
        }
    }
}
