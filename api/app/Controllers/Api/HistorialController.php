<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ReportModel;

class HistorialController extends ResourceController
{
    protected $format = 'json';
    protected $reportModel;
    
    public function __construct()
    {
        $this->reportModel = new ReportModel();
    }
    
    public function index()
    {
        // Obtener todos los reportes con información del cliente y jardín
        $reports = $this->reportModel
            ->select('reports.*, gardens.address as garden_address, users.name as client_name, users.email')
            ->join('gardens', 'gardens.id = reports.garden_id')
            ->join('users', 'users.id = gardens.user_id')
            ->orderBy('visit_date', 'DESC')
            ->findAll();
        
        // Formatear para el frontend
        $formatted = [];
        foreach ($reports as $report) {
            // Determinar tipo basado en las condiciones del reporte
            $tipo = 'Mantenimiento Regular';
            if (isset($report['pest_detected']) && $report['pest_detected']) {
                $tipo = 'Mantenimiento + Tratamiento';
            }
            
            $formatted[] = [
                'id' => $report['id'],
                'fecha' => $report['visit_date'],
                'tipo' => $tipo,
                'estadoGeneral' => $report['grass_health'] ?? 'Sin datos',
                'jardinero' => $report['technician_notes'] ?? 'Sin datos',
                'cliente' => $report['client_name'] ?? 'Sin cliente',
                'jardin' => $report['garden_address'] ?? 'Sin dirección'
            ];
        }
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
}
