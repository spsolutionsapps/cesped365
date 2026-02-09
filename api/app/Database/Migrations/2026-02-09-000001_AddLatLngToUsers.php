<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLatLngToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('users');
        $hasLat = false;
        foreach ($fields as $field) {
            if ($field->name === 'lat') {
                $hasLat = true;
                break;
            }
        }
        if (!$hasLat) {
            $this->forge->addColumn('users', [
                'lat' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,8',
                    'null'       => true,
                    'after'      => 'referido_por',
                    'comment'    => 'Latitud GPS (opcional)',
                ],
                'lng' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '11,8',
                    'null'       => true,
                    'after'      => 'lat',
                    'comment'    => 'Longitud GPS (opcional)',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['lat', 'lng']);
    }
}
