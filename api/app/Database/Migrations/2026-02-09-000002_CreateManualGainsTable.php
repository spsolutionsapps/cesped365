<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManualGainsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('manual_gains')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'year' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
            ],
            'month' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['year', 'month']);
        $this->forge->createTable('manual_gains');
    }

    public function down()
    {
        $this->forge->dropTable('manual_gains', true);
    }
}
