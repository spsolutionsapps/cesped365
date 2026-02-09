<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlockedDaysTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('blocked_days')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'blocked_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addUniqueKey('blocked_date'); // índice único por fecha
        $this->forge->createTable('blocked_days');
    }

    public function down()
    {
        $this->forge->dropTable('blocked_days');
    }
}
