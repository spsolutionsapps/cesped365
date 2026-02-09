<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ReportModel;
use App\Models\ReportImageModel;

class ReportesController extends ResourceController
{
    protected $format = 'json';
    protected $reportModel;
    protected $imageModel;
    
    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->imageModel = new ReportImageModel();
    }
    
    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 100; // Aumentar límite para mostrar todos
        
        $session = \Config\Services::session();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');
        $roleNormalized = $userRole !== null ? strtolower(trim((string) $userRole)) : '';
        $isCliente = ($roleNormalized === 'cliente' && $userId);
        
        // Todos ven todos los reportes. El cliente solo puede enviar evaluación en los de su jardín (can_rate + submitRating).
        $reports = $this->reportModel
            ->select('reports.*, gardens.address as garden_address, gardens.user_id as garden_user_id, users.name as client_name')
            ->join('gardens', 'gardens.id = reports.garden_id', 'left')
            ->join('users', 'users.id = gardens.user_id', 'left')
            ->orderBy('reports.id', 'DESC')
            ->paginate($limit);
        $total = $this->reportModel->pager ? $this->reportModel->pager->getTotal() : count($reports);
        
        $formatted = [];
        foreach ($reports as $report) {
            $gardenUserId = $this->getGardenUserIdFromReport($report);
            $canRate = $isCliente && $gardenUserId > 0 && $gardenUserId === (int) $userId;
            // Obtener imágenes del reporte
            $images = $this->imageModel->getByReport($report['id']);
            $baseUrl = config('App')->baseURL;
            // Remover trailing slash si existe
            $baseUrl = rtrim($baseUrl, '/');
            $imageUrls = array_map(function($img) use ($baseUrl) {
                // image_path ya incluye 'uploads/reportes/...', agregamos /api/public/ porque CodeIgniter está en subdirectorio
                return $baseUrl . '/api/public/' . $img['image_path'];
            }, $images);
            
            $grassHealth = $report['grass_health'] ?? null;
            if ($grassHealth === 'malo') {
                $grassHealth = 'regular';
            }
            $grassColor = $report['grass_color'] ?? ($grassHealth ?? 'bueno');
            if ($grassColor === 'malo') {
                $grassColor = 'regular';
            }
            
            $formatted[] = [
                'id' => $report['id'],
                'fecha' => $report['visit_date'],
                'estadoGeneral' => $grassHealth ?? 'Sin datos',
                'grass_color' => $grassColor,
                // Datos para título: "Nombre Apellido — Dirección"
                'cliente' => $report['client_name'] ?? '',
                'direccion' => $report['garden_address'] ?? '',
                'crecimientoCm' => (float)($report['growth_cm'] ?? 0),
                'plagas' => (bool)($report['pest_detected'] ?? false),
                'notaJardinero' => $report['recommendations'] ?? 'Sin observaciones',
                'jardinero' => $report['technician_notes'] ?? 'Sin jardinero',
                'observaciones' => $report['recommendations'] ?? '',
                'garden_id' => $report['garden_id'],
                'imagenes' => $imageUrls,
                // Campos de evaluación técnica con datos reales
                'grass_height_cm' => (float)($report['grass_height_cm'] ?? 0),
                'watering_status' => $report['watering_status'] ?? 'optimo',
                'pest_description' => $report['pest_description'] ?? '',
                'work_done' => $report['work_done'] ?? '',
                'fertilizer_applied' => (bool)($report['fertilizer_applied'] ?? false),
                'fertilizer_type' => $report['fertilizer_type'] ?? '',
                'weather_conditions' => $report['weather_conditions'] ?? '',
                // Campos para compatibilidad con UI vieja (basados en datos reales)
                'cespedParejo' => array_key_exists('grass_even', $report) && $report['grass_even'] !== null
                    ? (bool)$report['grass_even']
                    : ($grassHealth === 'excelente' || $grassHealth === 'bueno'),
                'colorOk' => ($grassColor === 'excelente' || $grassColor === 'bueno'),
                'manchas' => array_key_exists('spots', $report) && $report['spots'] !== null ? (bool)$report['spots'] : false,
                'zonasDesgastadas' => false,
                'malezasVisibles' => array_key_exists('weeds_visible', $report) && $report['weeds_visible'] !== null
                    ? (bool)$report['weeds_visible']
                    : (bool)($report['pest_detected'] ?? false),
                // Exponer flags reales para edición
                'grass_even' => $report['grass_even'] ?? null,
                'spots' => $report['spots'] ?? null,
                'weeds_visible' => $report['weeds_visible'] ?? null,
                // Evaluación del cliente (estrellas 1-5 + comentario)
                'client_rating' => isset($report['client_rating']) && $report['client_rating'] !== null ? (int) $report['client_rating'] : null,
                'client_feedback' => $report['client_feedback'] ?? null,
                // Si el usuario actual (cliente) es dueño del jardín de este reporte, puede evaluar
                'can_rate' => $canRate,
            ];
        }
        
        return $this->respond([
            'success' => true,
            'data' => $formatted,
            'pagination' => [
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    public function show($id = null)
    {
        $report = $this->reportModel
            ->select('reports.*, gardens.address as garden_address, gardens.user_id as garden_user_id, users.name as client_name')
            ->join('gardens', 'gardens.id = reports.garden_id', 'left')
            ->join('users', 'users.id = gardens.user_id', 'left')
            ->where('reports.id', $id)
            ->first();
        
        if (!$report) {
            return $this->fail('Reporte no encontrado', 404);
        }
        
        // Obtener imágenes
        $images = $this->imageModel->getByReport($id);
        $baseUrl = config('App')->baseURL;
        // Remover trailing slash si existe
        $baseUrl = rtrim($baseUrl, '/');
        $imageUrls = array_map(function($img) use ($baseUrl) {
            // image_path ya incluye 'uploads/reportes/...', agregamos /api/public/ porque CodeIgniter está en subdirectorio
            return $baseUrl . '/api/public/' . $img['image_path'];
        }, $images);
        
        // Formatear respuesta
        $grassHealth = $report['grass_health'] ?? null;
        if ($grassHealth === 'malo') {
            $grassHealth = 'regular';
        }
        $grassColor = $report['grass_color'] ?? ($grassHealth ?? 'bueno');
        if ($grassColor === 'malo') {
            $grassColor = 'regular';
        }
        
        $formatted = [
            'id' => $report['id'],
            'fecha' => $report['visit_date'],
            'estadoGeneral' => $grassHealth ?? 'Sin datos',
            'grass_color' => $grassColor,
            'cliente' => $report['client_name'] ?? '',
            'direccion' => $report['garden_address'] ?? '',
            'crecimientoCm' => (float)($report['growth_cm'] ?? 0),
            'plagas' => (bool)($report['pest_detected'] ?? false),
            'notaJardinero' => $report['recommendations'] ?? 'Sin observaciones',
            'jardinero' => $report['technician_notes'] ?? 'Sin jardinero',
            'observaciones' => $report['recommendations'] ?? '',
            'garden_id' => $report['garden_id'],
            'imagenes' => $imageUrls,
            // Campos de evaluación técnica con datos reales
            'grass_height_cm' => (float)($report['grass_height_cm'] ?? 0),
            'watering_status' => $report['watering_status'] ?? 'optimo',
            'pest_description' => $report['pest_description'] ?? '',
            'work_done' => $report['work_done'] ?? '',
            'fertilizer_applied' => (bool)($report['fertilizer_applied'] ?? false),
            'fertilizer_type' => $report['fertilizer_type'] ?? '',
            'weather_conditions' => $report['weather_conditions'] ?? '',
            // Campos para compatibilidad con UI vieja (basados en datos reales)
            'cespedParejo' => array_key_exists('grass_even', $report) && $report['grass_even'] !== null
                ? (bool)$report['grass_even']
                : ($grassHealth === 'excelente' || $grassHealth === 'bueno'),
            'colorOk' => ($grassColor === 'excelente' || $grassColor === 'bueno'),
            'manchas' => array_key_exists('spots', $report) && $report['spots'] !== null ? (bool)$report['spots'] : false,
            'zonasDesgastadas' => false,
            'malezasVisibles' => array_key_exists('weeds_visible', $report) && $report['weeds_visible'] !== null
                ? (bool)$report['weeds_visible']
                : (bool)($report['pest_detected'] ?? false),
            // Exponer flags reales para edición
            'grass_even' => $report['grass_even'] ?? null,
            'spots' => $report['spots'] ?? null,
            'weeds_visible' => $report['weeds_visible'] ?? null,
            // Evaluación del cliente
            'client_rating' => isset($report['client_rating']) && $report['client_rating'] !== null ? (int) $report['client_rating'] : null,
            'client_feedback' => $report['client_feedback'] ?? null,
            // Si el usuario actual (cliente) es dueño del jardín, puede evaluar
            'can_rate' => $this->reportCanRate($report),
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    /**
     * Obtiene el user_id del dueño del jardín del reporte (desde el row o consultando gardens).
     */
    private function getGardenUserIdFromReport(array $report)
    {
        $gardenUserId = isset($report['garden_user_id']) && $report['garden_user_id'] !== null && $report['garden_user_id'] !== ''
            ? (int) $report['garden_user_id']
            : 0;
        if ($gardenUserId <= 0 && !empty($report['garden_id'])) {
            $garden = (new \App\Models\GardenModel())->find($report['garden_id']);
            $gardenUserId = $garden ? (int) ($garden['user_id'] ?? 0) : 0;
        }
        return $gardenUserId;
    }
    
    /**
     * Indica si el usuario actual (cliente) puede evaluar este reporte (solo si es dueño del jardín).
     */
    private function reportCanRate(array $report)
    {
        $session = \Config\Services::session();
        $userId = $session->get('user_id');
        if (!$userId) {
            return false;
        }
        $role = $session->get('user_role');
        $role = $role !== null ? strtolower(trim((string) $role)) : '';
        if ($role !== 'cliente') {
            return false;
        }
        $gardenUserId = $this->getGardenUserIdFromReport($report);
        return $gardenUserId > 0 && $gardenUserId === (int) $userId;
    }
    
    /**
     * Evaluación del servicio por el cliente (estrellas 1-5 + comentario opcional).
     * Solo el cliente dueño del jardín del reporte puede evaluar.
     */
    public function submitRating($id = null)
    {
        $report = $this->reportModel->find($id);
        if (!$report) {
            return $this->fail('Reporte no encontrado', 404);
        }

        $session = \Config\Services::session();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');

        if (!$userId) {
            return $this->failUnauthorized('Debes iniciar sesión para evaluar');
        }

        // Solo el cliente puede evaluar (no el admin)
        $role = $userRole !== null ? trim((string) $userRole) : '';
        if (strtolower($role) !== 'cliente') {
            return $this->failForbidden('Solo el cliente puede evaluar este reporte');
        }

        // Verificar que el reporte pertenece a un jardín del cliente
        $gardenModel = new \App\Models\GardenModel();
        $garden = $gardenModel->find($report['garden_id']);
        if (!$garden) {
            return $this->fail('Jardín del reporte no encontrado', 404);
        }
        if ((int) $garden['user_id'] !== (int) $userId) {
            return $this->failForbidden('No puedes evaluar este reporte');
        }

        // PATCH con body form-urlencoded no llena $_POST; leer de raw input. POST sí llena $_POST.
        $rating = $this->request->getPost('rating');
        $feedback = $this->request->getPost('feedback');
        if ($rating === null || $rating === '') {
            $rating = $this->request->getRawInputVar('rating');
        }
        if ($feedback === null) {
            $feedback = $this->request->getRawInputVar('feedback');
        }

        $rating = $rating !== null && $rating !== '' ? (int) $rating : null;
        if ($rating === null || $rating < 1 || $rating > 5) {
            return $this->fail('La valoración debe ser entre 1 y 5 estrellas', 400);
        }

        $feedback = $feedback !== null && $feedback !== '' ? trim((string) $feedback) : null;

        $updated = $this->reportModel->update($id, [
            'client_rating' => $rating,
            'client_feedback' => $feedback,
        ]);

        if (!$updated) {
            return $this->fail('Error al guardar la evaluación', 500);
        }

        $updatedReport = $this->reportModel->find($id);
        return $this->respond([
            'success' => true,
            'data' => [
                'client_rating' => isset($updatedReport['client_rating']) ? (int) $updatedReport['client_rating'] : null,
                'client_feedback' => $updatedReport['client_feedback'] ?? null,
            ],
        ]);
    }
    
    public function create()
    {
        // Log COMPLETO de datos recibidos
        log_message('info', '=== CREAR REPORTE ===');
        log_message('info', 'Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
        log_message('info', 'POST data: ' . json_encode($this->request->getPost()));
        log_message('info', 'JSON data: ' . $this->request->getBody());
        log_message('info', 'All input: ' . json_encode($this->request->getVar()));
        
        // Validar datos de entrada
        $rules = [
            'garden_id' => 'required|is_natural_no_zero',
            'visit_date' => 'required|valid_date'
        ];
        
        if (!$this->validate($rules)) {
            log_message('error', 'Validation errors: ' . json_encode($this->validator->getErrors()));
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        // Obtener user_id del garden usando getVar() que funciona con POST form-urlencoded
        $db = \Config\Database::connect();
        $gardenId = $this->request->getVar('garden_id');
        $garden = $db->table('gardens')->where('id', $gardenId)->get()->getRowArray();
        
        if (!$garden) {
            log_message('error', 'Jardín no encontrado con ID: ' . $gardenId);
            return $this->fail('Jardín no encontrado', 404);
        }
        
        log_message('info', 'Jardín encontrado: ' . json_encode($garden));
        
        // Usar getVar() en lugar de getPost() para form-urlencoded
        // Convertir valores vacíos a null para campos opcionales
        $grassHeight = $this->request->getVar('grass_height_cm');
        $growthCm = $this->request->getVar('growth_cm');
        $nextVisit = $this->request->getVar('next_visit');
        $pestDesc = $this->request->getVar('pest_description');
        $fertType = $this->request->getVar('fertilizer_type');
        
        // CORREGIR FECHA: Sumar 1 día para compensar timezone
        $fechaRecibida = $this->request->getVar('visit_date');
        $fecha = new \DateTime($fechaRecibida);
        $fecha->modify('+1 day');
        $fechaFinal = $fecha->format('Y-m-d');
        
        log_message('info', 'Fecha recibida: ' . $fechaRecibida . ' -> Fecha final: ' . $fechaFinal);
        
        $data = [
            'garden_id' => $this->request->getVar('garden_id'),
            'user_id' => $garden['user_id'],
            'visit_date' => $fechaFinal,
            'status' => 'completado',
            'grass_health' => ($this->request->getVar('grass_health') === 'malo' ? 'regular' : ($this->request->getVar('grass_health') ?: 'bueno')),
            // Color del césped editable (si no viene, usar grass_health) - sin "malo"
            'grass_color' => ($this->request->getVar('grass_color') === 'malo'
                ? 'regular'
                : ($this->request->getVar('grass_color') ?: (($this->request->getVar('grass_health') === 'malo' ? 'regular' : ($this->request->getVar('grass_health') ?: 'bueno'))))),
            // Flags visuales editables
            'grass_even' => $this->request->getVar('grass_even') ? 1 : 0,
            'spots' => $this->request->getVar('spots') ? 1 : 0,
            'weeds_visible' => $this->request->getVar('weeds_visible') ? 1 : 0,
            'watering_status' => $this->request->getVar('watering_status') ?: 'optimo',
            'pest_detected' => $this->request->getVar('pest_detected') ? 1 : 0,
            'work_done' => $this->request->getVar('work_done') ?: '',
            'recommendations' => $this->request->getVar('recommendations') ?: '',
            'technician_notes' => $this->request->getVar('technician_notes') ?: ''
        ];
        
        // Solo agregar campos opcionales si tienen valor
        if ($grassHeight !== '' && $grassHeight !== null && $grassHeight !== '0') {
            $data['grass_height_cm'] = $grassHeight;
        }
        if ($growthCm !== '' && $growthCm !== null && $growthCm !== '0') {
            $data['growth_cm'] = $growthCm;
        }
        if ($nextVisit !== '' && $nextVisit !== null && $nextVisit !== '0000-00-00') {
            $data['next_visit'] = $nextVisit;
        }
        if ($pestDesc !== '' && $pestDesc !== null) {
            $data['pest_description'] = $pestDesc;
        }
        if ($fertType !== '' && $fertType !== null) {
            $data['fertilizer_type'] = $fertType;
        }
        
        $data['fertilizer_applied'] = $this->request->getVar('fertilizer_applied') ? 1 : 0;
        $weatherCond = $this->request->getVar('weather_conditions');
        if ($weatherCond !== '' && $weatherCond !== null) {
            $data['weather_conditions'] = $weatherCond;
        }
        
        log_message('info', 'Datos preparados para insertar: ' . json_encode($data));
        
        // Insertar reporte
        $reportId = $this->reportModel->insert($data);
        
        if (!$reportId) {
            return $this->fail('Error al crear el reporte', 500);
        }
        
        // Obtener el reporte creado
        $report = $this->reportModel->find($reportId);

        // Notificar por email al cliente (dueño del jardín)
        $this->sendReportEmailToClient((int) $reportId);
        
        return $this->respondCreated([
            'success' => true,
            'message' => 'Reporte creado exitosamente',
            'data' => [
                'id' => $report['id'],
                'garden_id' => $report['garden_id'],
                'visit_date' => $report['visit_date'],
                'grass_health' => $report['grass_health']
            ]
        ]);
    }
    
    public function update($id = null)
    {
        // Verificar que el reporte existe
        $report = $this->reportModel->find($id);
        if (!$report) {
            return $this->fail('Reporte no encontrado', 404);
        }
        
        // Log de datos recibidos
        log_message('info', '=== ACTUALIZAR REPORTE ===');
        log_message('info', 'Reporte ID: ' . $id);
        log_message('info', 'Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
        
        // IMPORTANTE: En PUT/PATCH PHP no llena $_POST, así que usamos raw input
        $input = $this->request->getVar();
        if (empty($input) || (is_array($input) && count($input) === 0)) {
            $input = $this->request->getRawInput();
        }
        if (empty($input) || (is_array($input) && count($input) === 0)) {
            $json = json_decode($this->request->getBody(), true);
            if (is_array($json)) {
                $input = $json;
            }
        }
        
        log_message('info', 'Parsed input: ' . json_encode($input));
        
        // Validar datos de entrada
        $rules = [
            'garden_id' => 'permit_empty|is_natural_no_zero',
            'visit_date' => 'permit_empty|valid_date'
        ];
        
        // Validar contra el array parseado (especialmente para PUT)
        if (!$this->validateData(is_array($input) ? $input : [], $rules)) {
            log_message('error', 'Validation errors: ' . json_encode($this->validator->getErrors()));
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        // Helper para ver si el campo vino en el request
        $has = function(string $key) use ($input): bool {
            return is_array($input) && array_key_exists($key, $input);
        };
        $get = function(string $key, $default = null) use ($input) {
            return (is_array($input) && array_key_exists($key, $input)) ? $input[$key] : $default;
        };
        
        // Preparar datos para actualizar (solo campos que vienen en el request)
        $data = [];
        
        if ($has('garden_id')) {
            $gardenId = $get('garden_id');
            // Evitar setear vacío
            if ($gardenId !== '') {
                $data['garden_id'] = $gardenId;
            }
        }
        
        if ($has('visit_date')) {
            // CORREGIR FECHA: Sumar 1 día para compensar timezone
            $fechaRecibida = $get('visit_date');
            if ($fechaRecibida !== '') {
                $fecha = new \DateTime($fechaRecibida);
                $fecha->modify('+1 day');
                $fechaFinal = $fecha->format('Y-m-d');
                $data['visit_date'] = $fechaFinal;
            }
        }
        
        if ($has('grass_health')) {
            $data['grass_health'] = $get('grass_health');
        }

        if ($has('grass_color')) {
            $data['grass_color'] = $get('grass_color') !== '' ? $get('grass_color') : null;
        }

        if ($has('grass_even')) {
            $val = $get('grass_even');
            $data['grass_even'] = (!empty($val) && $val !== '0') ? 1 : 0;
        }

        if ($has('spots')) {
            $val = $get('spots');
            $data['spots'] = (!empty($val) && $val !== '0') ? 1 : 0;
        }

        if ($has('weeds_visible')) {
            $val = $get('weeds_visible');
            $data['weeds_visible'] = (!empty($val) && $val !== '0') ? 1 : 0;
        }
        
        if ($has('watering_status')) {
            $data['watering_status'] = $get('watering_status');
        }
        
        if ($has('pest_detected')) {
            // request helper manda '1'/'0'
            $val = $get('pest_detected');
            $data['pest_detected'] = (!empty($val) && $val !== '0') ? 1 : 0;
        }
        
        if ($has('work_done')) {
            $data['work_done'] = $get('work_done') ?: '';
        }
        
        if ($has('recommendations')) {
            $data['recommendations'] = $get('recommendations') ?: '';
        }
        
        if ($has('technician_notes')) {
            $data['technician_notes'] = $get('technician_notes') ?: '';
        }
        
        // Campos opcionales
        if ($has('grass_height_cm')) {
            $grassHeight = $get('grass_height_cm');
            if ($grassHeight !== '' && $grassHeight !== '0') {
                $data['grass_height_cm'] = $grassHeight;
            } else {
                $data['grass_height_cm'] = null;
            }
        }
        
        if ($has('growth_cm')) {
            $growthCm = $get('growth_cm');
            if ($growthCm !== '' && $growthCm !== '0') {
                $data['growth_cm'] = $growthCm;
            } else {
                $data['growth_cm'] = null;
            }
        }
        
        if ($has('next_visit')) {
            $nextVisit = $get('next_visit');
            if ($nextVisit !== '' && $nextVisit !== '0000-00-00') {
                $data['next_visit'] = $nextVisit;
            } else {
                $data['next_visit'] = null;
            }
        }
        
        if ($has('pest_description')) {
            $pestDesc = $get('pest_description');
            $data['pest_description'] = $pestDesc !== '' ? $pestDesc : null;
        }
        
        if ($has('fertilizer_type')) {
            $fertType = $get('fertilizer_type');
            $data['fertilizer_type'] = $fertType !== '' ? $fertType : null;
        }
        
        if ($has('fertilizer_applied')) {
            $val = $get('fertilizer_applied');
            $data['fertilizer_applied'] = (!empty($val) && $val !== '0') ? 1 : 0;
        }
        
        if ($has('weather_conditions')) {
            $weatherCond = $get('weather_conditions');
            $data['weather_conditions'] = $weatherCond !== '' ? $weatherCond : null;
        }
        
        log_message('info', 'Datos preparados para actualizar: ' . json_encode($data));
        
        if (empty($data)) {
            return $this->fail('No hay datos para actualizar', 400);
        }
        
        // Actualizar reporte
        $updated = $this->reportModel->update($id, $data);
        
        if (!$updated) {
            return $this->fail('Error al actualizar el reporte', 500);
        }
        
        // Obtener el reporte actualizado
        $reporteActualizado = $this->reportModel->find($id);
        
        return $this->respond([
            'success' => true,
            'message' => 'Reporte actualizado exitosamente',
            'data' => [
                'id' => $reporteActualizado['id'],
                'garden_id' => $reporteActualizado['garden_id'],
                'visit_date' => $reporteActualizado['visit_date'],
                'grass_health' => $reporteActualizado['grass_health']
            ]
        ]);
    }
    
    public function delete($id = null)
    {
        $report = $this->reportModel->find($id);
        
        if (!$report) {
            return $this->fail('Reporte no encontrado', 404);
        }
        
        // Eliminar imágenes asociadas del disco
        $images = $this->imageModel->getByReport($id);
        foreach ($images as $image) {
            $filePath = FCPATH . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Eliminar imágenes de la base de datos
        $this->imageModel->where('report_id', $id)->delete();
        
        // Eliminar reporte
        $this->reportModel->delete($id);
        
        return $this->respond([
            'success' => true,
            'message' => 'Reporte eliminado exitosamente'
        ]);
    }
    
    public function uploadImage($reportId)
    {
        // Verificar que el reporte existe
        $report = $this->reportModel->find($reportId);
        if (!$report) {
            return $this->fail('Reporte no encontrado', 404);
        }
        
        // Validar archivo
        $validationRule = [
            'image' => [
                'rules' => 'uploaded[image]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png]|max_size[image,2048]',
            ],
        ];
        
        if (!$this->validate($validationRule)) {
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        $file = $this->request->getFile('image');
        
        if (!$file->isValid()) {
            return $this->fail('Archivo inválido', 400);
        }
        
        // Generar nombre único
        $newName = $file->getRandomName();
        
        // Mover archivo a carpeta uploads/reportes
        $uploadPath = FCPATH . 'uploads/reportes/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $file->move($uploadPath, $newName);
        
        // Guardar en base de datos
        $imagePath = 'uploads/reportes/' . $newName;
        $imageId = $this->imageModel->insert([
            'report_id' => $reportId,
            'image_path' => $imagePath
        ]);
        
        if (!$imageId) {
            return $this->fail('Error al guardar la imagen', 500);
        }
        
        // Construir URL completa de la imagen
        $baseUrl = config('App')->baseURL;
        $baseUrl = rtrim($baseUrl, '/');
        $fullImageUrl = $baseUrl . '/api/public/' . $imagePath;
        
        return $this->respondCreated([
            'success' => true,
            'message' => 'Imagen subida exitosamente',
            'data' => [
                'id' => $imageId,
                'image_url' => $fullImageUrl
            ]
        ]);
    }

    /**
     * Envía un email al cliente (dueño del jardín) cuando se crea un reporte.
     * Usa la plantilla emails/reporte_cliente y SMTP configurado en .env.
     * Si falla el envío, se registra en log pero no se falla la petición.
     */
    private function sendReportEmailToClient(int $reportId): void
    {
        $reportWithDetails = $this->reportModel->getWithDetails($reportId);
        if (!$reportWithDetails) {
            log_message('warning', 'Email reporte: no se encontró reporte con detalles para ID ' . $reportId);
            return;
        }

        $toEmail = $reportWithDetails['email'] ?? '';
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            log_message('warning', 'Email reporte: email de cliente inválido o vacío para reporte ' . $reportId);
            return;
        }

        // URL pública del frontend para enlaces e imágenes en el email (debe ser accesible desde internet)
        $publicBaseUrl = env('APP_PUBLIC_URL') ?: env('FRONTEND_BASE_URL');
        $publicBaseUrl = $publicBaseUrl !== '' && $publicBaseUrl !== false
            ? rtrim(preg_replace('#/$#', '', (string) $publicBaseUrl), '/')
            : '';
        $viewReportUrl = $publicBaseUrl !== '' ? $publicBaseUrl . '/dashboard/reportes' : '';
        $logoUrl = $publicBaseUrl !== '' ? $publicBaseUrl . '/logo_email.png' : '';

        $data = [
            'report'         => $reportWithDetails,
            'viewReportUrl'  => $viewReportUrl,
            'logoUrl'        => $logoUrl,
        ];

        $renderer = \Config\Services::renderer();
        $renderer->setData($data);
        $html = $renderer->render('emails/reporte_cliente');

        try {
            $email = \Config\Services::email();
            $email->setTo($toEmail);
            $email->setSubject('Nuevo reporte de tu jardín - Cesped365');
            $email->setMessage($html);

            if (!$email->send(false)) {
                $debug = $email->printDebugger([]);
                log_message('error', 'Email reporte: falló envío a ' . $toEmail . ' - ' . $debug);
                return;
            }
            log_message('info', 'Email reporte: enviado correctamente a ' . $toEmail . ' para reporte ' . $reportId);
        } catch (\Throwable $e) {
            log_message('error', 'Email reporte: excepción al enviar a ' . $toEmail . ' - ' . $e->getMessage());
        }
    }
}
