<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class MarkMigrations extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:mark-migrations';
    protected $description = 'Marca las migraciones existentes como ejecutadas';

    public function run(array $params)
    {
        $db = Database::connect();

        try {
            // Verificar si la tabla migrations existe, si no, crearla
            if (!$db->tableExists('migrations')) {
                $db->query("CREATE TABLE IF NOT EXISTS migrations (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    version varchar(255) NOT NULL,
                    class varchar(255) NOT NULL,
                    group varchar(255) NOT NULL DEFAULT 'default',
                    namespace varchar(255) NOT NULL DEFAULT 'App',
                    time int(11) NOT NULL,
                    batch int(11) unsigned NOT NULL,
                    PRIMARY KEY (id),
                    KEY migrations_version (version)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
                CLI::write('✅ Tabla migrations creada.', 'green');
            }

            // Lista de migraciones que ya deberían estar ejecutadas (basado en las tablas existentes)
            $migrations = [
                '2026-01-13-000001' => 'CreateUsersTable',
                '2026-01-13-000002' => 'CreateGardensTable',
                '2026-01-13-000003' => 'CreateReportsTable',
                '2026-01-13-000004' => 'CreateReportImagesTable',
                '2026-01-14-000001' => 'CreateSubscriptionsTable',
                '2026-01-14-000002' => 'CreateUserSubscriptionsTable',
                '2026-01-18-000001' => 'add_plan_estado_to_users',
            ];

            $batch = 1;
            $time = time();

            foreach ($migrations as $version => $class) {
                // Verificar si ya está marcada
                $exists = $db->table('migrations')
                    ->where('version', $version)
                    ->where('class', $class)
                    ->countAllResults();

                if ($exists == 0) {
                    $db->table('migrations')->insert([
                        'version' => $version,
                        'class' => $class,
                        'group' => 'default',
                        'namespace' => 'App',
                        'time' => $time,
                        'batch' => $batch
                    ]);
                    CLI::write("✅ Migración {$version} ({$class}) marcada como ejecutada.", 'green');
                } else {
                    CLI::write("⏭️  Migración {$version} ({$class}) ya estaba marcada.", 'yellow');
                }
            }

            CLI::write("\n✅ Proceso completado. Ahora puedes ejecutar 'php spark migrate' sin errores.", 'green');
        } catch (\Exception $e) {
            CLI::error('❌ Error: ' . $e->getMessage());
            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
