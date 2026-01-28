<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class AddReferidoPor extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:add-referido-por';
    protected $description = 'Agrega la columna referido_por a la tabla users';

    public function run(array $params)
    {
        $db = Database::connect();

        try {
            // Verificar si la columna ya existe
            $fields = $db->getFieldData('users');
            $columnExists = false;
            
            foreach ($fields as $field) {
                if ($field->name === 'referido_por') {
                    $columnExists = true;
                    break;
                }
            }
            
            if ($columnExists) {
                CLI::write('✅ La columna "referido_por" ya existe en la tabla "users".', 'green');
            } else {
                // Agregar la columna
                $sql = "ALTER TABLE users ADD COLUMN referido_por VARCHAR(255) NULL AFTER estado";
                $db->query($sql);
                CLI::write('✅ Columna "referido_por" agregada exitosamente a la tabla "users".', 'green');
            }
        } catch (\Exception $e) {
            CLI::error('❌ Error: ' . $e->getMessage());
            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
