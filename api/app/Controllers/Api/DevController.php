<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rutas de desarrollo (solo disponibles con CI_ENVIRONMENT=development).
 * Ej: previsualizar plantilla de email en el navegador.
 */
class DevController
{
    /**
     * Previsualiza la plantilla del email de reporte con datos de ejemplo.
     * Solo en desarrollo. URL: GET /dev/email-preview (con el servidor en /api/public sería /dev/email-preview)
     */
    public function emailPreview(): ResponseInterface
    {
        if (ENVIRONMENT !== 'development') {
            return service('response')->setStatusCode(404)->setBody('Not found');
        }

        $frontendBaseUrl = rtrim(env('FRONTEND_BASE_URL', 'http://localhost:3000'), '/');
        $report = [
            'user_name'         => 'María García',
            'address'           => 'Av. Siempre Viva 123, Springfield',
            'visit_date'        => date('Y-m-d'),
            'grass_health'      => 'bueno',
            'watering_status'   => 'optimo',
            'technician_notes'  => 'Juan Pérez',
            'work_done'         => "Corte de césped en todo el jardín.\nRevisión de bordes y desmalezado.",
            'recommendations'   => 'Mantener riego en horario matutino. Próxima visita en 15 días.',
        ];

        $data = [
            'report'        => $report,
            'viewReportUrl' => $frontendBaseUrl . '/dashboard/reportes',
            'logoUrl'       => $frontendBaseUrl . '/logo.png',
        ];

        $renderer = \Config\Services::renderer();
        $renderer->setData($data);
        $html = $renderer->render('emails/reporte_cliente');

        return service('response')
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($html);
    }
}
