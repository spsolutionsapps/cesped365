<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanEstadoToUsers extends Migration
{
    public function up()
    {
        // Verificar si las columnas ya existen
        $fields = $this->db->getFieldData('users');
        $planExists = false;
        $estadoExists = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'plan') $planExists = true;
            if ($field->name === 'estado') $estadoExists = true;
        }
        
        if ($planExists && $estadoExists) {
            return; // Las columnas ya existen, no hacer nada
        }
        
        $columnsToAdd = [];
        if (!$planExists) {
            $columnsToAdd['plan'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'default' => 'Urbano',
                'after' => 'address'
            ];
        }
        if (!$estadoExists) {
            $columnsToAdd['estado'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => 'Pendiente',
                'after' => $planExists ? 'plan' : 'address'
            ];
        }
        
        if (!empty($columnsToAdd)) {
            $this->forge->addColumn('users', $columnsToAdd);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['plan', 'estado']);
    }
}
