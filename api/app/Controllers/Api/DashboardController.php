<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\ReportModel;
use App\Models\GardenModel;

class DashboardController extends ResourceController
{
    protected $format = 'json';
    protected $userModel;
    protected $reportModel;
    protected $gardenModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->reportModel = new ReportModel();
        $this->gardenModel = new GardenModel();
        helper('session');
    }
    
    public function index()
    {
        // Obtener estadísticas reales
        $db = \Config\Database::connect();
        
        // Total de clientes (usuarios con role='cliente')
        $totalClientes = $this->userModel->where('role', 'cliente')->countAllResults();
        
        // Total de reportes
        $totalReportes = $this->reportModel->countAllResults();
        
        // Último reporte
        $ultimoReporte = $this->reportModel->orderBy('date', 'DESC')->first();
        
        // Reportes de este mes
        $reportesEsteMes = $this->reportModel
            ->where('MONTH(date)', date('m'))
            ->where('YEAR(date)', date('Y'))
            ->countAllResults();
        
        $data = [
            'success' => true,
            'data' => [
                'estadoGeneral' => $ultimoReporte ? $ultimoReporte['estado_general'] : 'Sin datos',
                'ultimaVisita' => $ultimoReporte ? $ultimoReporte['date'] : null,
                'totalReportes' => $totalReportes,
                'estadisticas' => [
                    'totalClientes' => $totalClientes,
                    'clientesActivos' => $totalClientes, // Por ahora, todos están activos
                    'visitasEsteMes' => $reportesEsteMes,
                    'proximasVisitas' => 0, // Por implementar
                    'reportesPendientes' => 0 // Por implementar
                ]
            ]
        ];
        
        return $this->respond($data);
    }
}
