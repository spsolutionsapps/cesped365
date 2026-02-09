<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\ReportModel;
use App\Models\GardenModel;
use App\Models\ScheduledVisitModel;
use App\Models\UserSubscriptionModel;
use App\Models\SubscriptionModel;
use App\Models\ManualGainModel;

class DashboardController extends ResourceController
{
    protected $format = 'json';
    protected $userModel;
    protected $reportModel;
    protected $gardenModel;
    protected $scheduledVisitModel;
    protected $userSubscriptionModel;
    protected $subscriptionModel;
    protected $manualGainModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->reportModel = new ReportModel();
        $this->gardenModel = new GardenModel();
        $this->scheduledVisitModel = new ScheduledVisitModel();
        $this->userSubscriptionModel = new UserSubscriptionModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->manualGainModel = new ManualGainModel();
        helper('session');
    }

    /**
     * Total de ganancias para un mes: suscripciones creadas ese mes + ganancias manuales.
     * No toca lógica de Mercado Pago; solo lee user_subscriptions y manual_gains.
     */
    private function getGananciasForMonth(int $year, int $month): float
    {
        $suscripciones = $this->userSubscriptionModel
            ->select('subscriptions.price')
            ->join('subscriptions', 'subscriptions.id = user_subscriptions.subscription_id')
            ->where('MONTH(user_subscriptions.created_at)', sprintf('%02d', $month))
            ->where('YEAR(user_subscriptions.created_at)', $year)
            ->findAll();

        $total = 0;
        foreach ($suscripciones as $sub) {
            $total += floatval($sub['price']);
        }
        $total += $this->manualGainModel->getTotalForMonth($year, $month);
        return $total;
    }

    /**
     * Ganancias por cada uno de los últimos N meses (para gráfico).
     * Devuelve array de [ 'mes' => 'Ene', 'año' => 2025, 'total' => 1234.56 ] ordenado del más antiguo al más reciente.
     */
    private function getGananciasPorUltimosMeses(int $cantidad = 12): array
    {
        $resultado = [];
        $y = (int) date('Y');
        $m = (int) date('n');
        $nombres = [ 1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
                     7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic' ];
        for ($i = 0; $i < $cantidad; $i++) {
            $resultado[] = [
                'mes' => $nombres[$m],
                'año' => $y,
                'total' => round($this->getGananciasForMonth($y, $m), 2),
            ];
            $m--;
            if ($m < 1) {
                $m = 12;
                $y--;
            }
        }
        return array_reverse($resultado);
    }

    /**
     * Ganancias de un mes desglosadas por día (día 1 hasta último día del mes).
     * Incluye suscripciones por created_at y manual_gains por created_at de ese mes.
     */
    private function getGananciasPorDia(int $year, int $month): array
    {
        $diasEnMes = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $db = \Config\Database::connect();

        // Suscripciones del mes agrupadas por día
        $builderSub = $db->table('user_subscriptions');
        $builderSub->select('DAY(user_subscriptions.created_at) as dia, SUM(subscriptions.price) as total');
        $builderSub->join('subscriptions', 'subscriptions.id = user_subscriptions.subscription_id');
        $builderSub->where('MONTH(user_subscriptions.created_at)', $month);
        $builderSub->where('YEAR(user_subscriptions.created_at)', $year);
        $builderSub->groupBy('DAY(user_subscriptions.created_at)');
        $rowsSub = $builderSub->get()->getResultArray();

        // Ganancias manuales del mes agrupadas por día (created_at)
        $builderMan = $db->table('manual_gains');
        $builderMan->select('DAY(created_at) as dia, SUM(amount) as total');
        $builderMan->where('gain_year', $year);
        $builderMan->where('gain_month', $month);
        $builderMan->groupBy('DAY(created_at)');
        $rowsMan = $builderMan->get()->getResultArray();

        $porDia = array_fill(1, $diasEnMes, 0.0);
        foreach ($rowsSub as $r) {
            $d = (int) $r['dia'];
            if ($d >= 1 && $d <= $diasEnMes) {
                $porDia[$d] += floatval($r['total']);
            }
        }
        foreach ($rowsMan as $r) {
            $d = (int) $r['dia'];
            if ($d >= 1 && $d <= $diasEnMes) {
                $porDia[$d] += floatval($r['total']);
            }
        }

        $out = [];
        for ($d = 1; $d <= $diasEnMes; $d++) {
            $out[] = [ 'dia' => $d, 'total' => round($porDia[$d], 2) ];
        }
        return $out;
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
        
        // Ganancias del mes actual (suscripciones + ganancias manuales)
        $gananciasMes = $this->getGananciasForMonth((int) date('Y'), (int) date('n'));

        // Ganancias del mes anterior (para comparativa en el gráfico)
        $mesAnterior = (int) date('n') - 1;
        $añoAnterior = (int) date('Y');
        if ($mesAnterior < 1) {
            $mesAnterior = 12;
            $añoAnterior--;
        }
        $gananciasMesAnterior = $this->getGananciasForMonth($añoAnterior, $mesAnterior);

        // Últimos 12 meses para gráfico (etiqueta + total)
        $gananciasPorMes = $this->getGananciasPorUltimosMeses(12);
        // Mes actual por día (día 1..31, total por día)
        $gananciasMesPorDia = $this->getGananciasPorDia((int) date('Y'), (int) date('n'));

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
                    'gananciasMesAnterior' => $gananciasMesAnterior,
                    'gananciasPorMes' => $gananciasPorMes,
                    'gananciasMesPorDia' => $gananciasMesPorDia,
                    'ultimosUsuarios' => $ultimosUsuariosFormateados,
                    'proximasVisitas' => 0, // Por implementar
                    'reportesPendientes' => 0 // Por implementar
                ]
            ]
        ];
        
        return $this->respond($data);
    }

    /**
     * Ganancias por día de un mes dado (solo admin). Query: year, month.
     * Útil para el select "Mes actual por día" en el frontend.
     */
    public function gananciasPorDia()
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));
        if ($month < 1 || $month > 12 || $year < 2020 || $year > 2100) {
            return $this->fail('Parámetros year/month inválidos', 400);
        }
        $data = $this->getGananciasPorDia($year, $month);
        return $this->respond([
            'success' => true,
            'data' => [ 'gananciasMesPorDia' => $data ],
        ]);
    }

    /**
     * Agregar ganancia manual al mes actual (solo admin). Se suma a la ganancia total del mes.
     * No modifica Mercado Pago ni suscripciones.
     */
    public function addGanancia()
    {
        $monto = $this->request->getPost('monto') ?? $this->request->getVar('monto');
        if ($monto === null || $monto === '') {
            return $this->fail('El monto es requerido', 400);
        }
        $monto = floatval(str_replace(',', '.', $monto));
        if ($monto <= 0) {
            return $this->fail('El monto debe ser mayor a cero', 400);
        }

        $year  = (int) date('Y');
        $month = (int) date('n');

        $db = \Config\Database::connect();
        $db->table('manual_gains')->insert([
            'gain_year'  => $year,
            'gain_month' => $month,
            'amount'     => $monto,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = $db->insertID();

        if (!$id) {
            return $this->fail('Error al guardar la ganancia', 500);
        }

        return $this->respondCreated([
            'success' => true,
            'message' => 'Ganancia agregada correctamente',
            'data' => [
                'id' => $id,
                'year' => $year,
                'month' => $month,
                'amount' => $monto,
            ],
        ]);
    }
}
