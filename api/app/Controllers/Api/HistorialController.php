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
        // Obtener todos los reportes ordenados por fecha descendente
        $reports = $this->reportModel
            ->orderBy('date', 'DESC')
            ->findAll();
        
        // Formatear para el frontend
        $formatted = [];
        foreach ($reports as $report) {
            // Determinar tipo basado en las condiciones del reporte
            $tipo = 'Mantenimiento Regular';
            if ($report['malezas_visibles'] || $report['manchas']) {
                $tipo = 'Mantenimiento + Tratamiento';
            }
            if ($report['zonas_desgastadas']) {
                $tipo = 'Mantenimiento + Resembrado';
            }
            
            $formatted[] = [
                'id' => $report['id'],
                'fecha' => $report['date'],
                'tipo' => $tipo,
                'estadoGeneral' => $report['estado_general'],
                'jardinero' => $report['jardinero']
            ];
        }
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
}
