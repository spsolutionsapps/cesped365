<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Reportes para Juan Pérez (garden_id = 1)
            [
                'garden_id'           => 1,
                'date'                => '2026-01-10',
                'estado_general'      => 'Bueno',
                'cesped_parejo'       => true,
                'color_ok'            => true,
                'manchas'             => false,
                'zonas_desgastadas'   => false,
                'malezas_visibles'    => false,
                'crecimiento_cm'      => 2.5,
                'compactacion'        => 'Normal',
                'humedad'             => 'Adecuada',
                'plagas'              => false,
                'observaciones'       => 'El césped está en excelente estado. Se realizó corte regular y fertilización. Recomendamos mantener el riego actual.',
                'jardinero'           => 'Carlos Rodríguez',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ],
            [
                'garden_id'           => 1,
                'date'                => '2025-12-15',
                'estado_general'      => 'Regular',
                'cesped_parejo'       => true,
                'color_ok'            => false,
                'manchas'             => true,
                'zonas_desgastadas'   => false,
                'malezas_visibles'    => true,
                'crecimiento_cm'      => 3.2,
                'compactacion'        => 'Ligera',
                'humedad'             => 'Baja',
                'plagas'              => false,
                'observaciones'       => 'Se detectaron algunas manchas amarillas en la zona norte. Se aplicó tratamiento para malezas. Recomendamos aumentar el riego.',
                'jardinero'           => 'María González',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ],
            [
                'garden_id'           => 1,
                'date'                => '2025-11-20',
                'estado_general'      => 'Bueno',
                'cesped_parejo'       => true,
                'color_ok'            => true,
                'manchas'             => false,
                'zonas_desgastadas'   => true,
                'malezas_visibles'    => false,
                'crecimiento_cm'      => 2.8,
                'compactacion'        => 'Normal',
                'humedad'             => 'Adecuada',
                'plagas'              => false,
                'observaciones'       => 'Estado general bueno. Se identificó una pequeña zona desgastada cerca del árbol. Se realizó resembrado.',
                'jardinero'           => 'Carlos Rodríguez',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ],
            // Reportes para María García (garden_id = 2)
            [
                'garden_id'           => 2,
                'date'                => '2026-01-08',
                'estado_general'      => 'Bueno',
                'cesped_parejo'       => true,
                'color_ok'            => true,
                'manchas'             => false,
                'zonas_desgastadas'   => false,
                'malezas_visibles'    => false,
                'crecimiento_cm'      => 2.1,
                'compactacion'        => 'Normal',
                'humedad'             => 'Adecuada',
                'plagas'              => false,
                'observaciones'       => 'Jardín en perfecto estado. Mantenimiento regular completado.',
                'jardinero'           => 'Carlos Rodríguez',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ],
            // Reportes para Roberto López (garden_id = 3)
            [
                'garden_id'           => 3,
                'date'                => '2026-01-09',
                'estado_general'      => 'Bueno',
                'cesped_parejo'       => true,
                'color_ok'            => true,
                'manchas'             => false,
                'zonas_desgastadas'   => false,
                'malezas_visibles'    => false,
                'crecimiento_cm'      => 3.0,
                'compactacion'        => 'Normal',
                'humedad'             => 'Adecuada',
                'plagas'              => false,
                'observaciones'       => 'Jardín grande en excelente condiciones. Se realizó corte y perfilado de bordes.',
                'jardinero'           => 'María González',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('reports')->insertBatch($data);
    }
}
