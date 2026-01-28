<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReferidoPorToUsers extends Migration
{
    public function up()
    {
        // Verificar si la columna ya existe
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('users');
        $columnExists = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'referido_por') {
                $columnExists = true;
                break;
            }
        }
        
        // Solo agregar la columna si no existe
        if (!$columnExists) {
            $this->forge->addColumn('users', [
                'referido_por' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                    'after'      => 'estado'
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['referido_por']);
    }
}
