<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\ReportModel;
use App\Models\GardenModel;
use App\Models\ScheduledVisitModel;
use App\Models\UserSubscriptionModel;
use App\Models\SubscriptionModel;

class DashboardController extends ResourceController
{
    protected $format = 'json';
    protected $userModel;
    protected $reportModel;
    protected $gardenModel;
    protected $scheduledVisitModel;
    protected $userSubscriptionModel;
    protected $subscriptionModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->reportModel = new ReportModel();
        $this->gardenModel = new GardenModel();
        $this->scheduledVisitModel = new ScheduledVisitModel();
        $this->userSubscriptionModel = new UserSubscriptionModel();
        $this->subscriptionModel = new SubscriptionModel();
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
        $ultimoReporte = $this->reportModel->orderBy('visit_date', 'DESC')->first();
        
        // Visitas completadas de este mes (de scheduled_visits)
        $visitasCompletadasEsteMes = $this->scheduledVisitModel
            ->where('status', 'completada')
            ->where('MONTH(scheduled_date)', date('m'))
            ->where('YEAR(scheduled_date)', date('Y'))
            ->countAllResults();
        
        // Visitas de hoy (completadas)
        $visitasHoy = $this->scheduledVisitModel
            ->where('status', 'completada')
            ->where('DATE(scheduled_date)', date('Y-m-d'))
            ->countAllResults();
        
        // Últimos 5 usuarios registrados
        $ultimosUsuarios = $this->userModel
            ->where('role', 'cliente')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();
        
        $ultimosUsuariosFormateados = [];
        foreach ($ultimosUsuarios as $usuario) {
            $ultimosUsuariosFormateados[] = [
                'id' => $usuario['id'],
                'name' => $usuario['name'],
                'email' => $usuario['email'],
                'created_at' => $usuario['created_at']
            ];
        }
        
        // Ganancias del mes (suma de precios de suscripciones creadas este mes)
        $suscripcionesEsteMes = $this->userSubscriptionModel
            ->select('subscriptions.price')
            ->join('subscriptions', 'subscriptions.id = user_subscriptions.subscription_id')
            ->where('MONTH(user_subscriptions.created_at)', date('m'))
            ->where('YEAR(user_subscriptions.created_at)', date('Y'))
            ->findAll();
        
        $gananciasMes = 0;
        foreach ($suscripcionesEsteMes as $sub) {
            $gananciasMes += floatval($sub['price']);
        }
        
        $data = [
            'success' => true,
            'data' => [
                'estadoGeneral' => $ultimoReporte ? $ultimoReporte['grass_health'] : 'Sin datos',
                'ultimaVisita' => $ultimoReporte ? $ultimoReporte['visit_date'] : null,
                'totalReportes' => $totalReportes,
                'estadisticas' => [
                    'totalClientes' => $totalClientes,
                    'clientesActivos' => $totalClientes, // Por ahora, todos están activos
                    'visitasEsteMes' => $visitasCompletadasEsteMes,
                    'visitasHoy' => $visitasHoy,
                    'gananciasMes' => $gananciasMes,
                    'ultimosUsuarios' => $ultimosUsuariosFormateados,
                    'proximasVisitas' => 0, // Por implementar
                    'reportesPendientes' => 0 // Por implementar
                ]
            ]
        ];
        
        return $this->respond($data);
    }
}
