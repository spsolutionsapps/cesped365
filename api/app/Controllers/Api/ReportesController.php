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
        
        // Obtener reportes con paginación, ordenados por fecha DESC (más recientes primero)
        $reports = $this->reportModel
            ->orderBy('id', 'DESC') // Ordenar por ID para que los nuevos aparezcan primero
            ->paginate($limit);
        
        $total = $this->reportModel->countAll();
        
        // Formatear respuesta para coincidir con el formato del frontend
        $formatted = [];
        foreach ($reports as $report) {
            // Obtener imágenes del reporte
            $images = $this->imageModel->getByReport($report['id']);
            $imageUrls = array_map(function($img) {
                return 'https://cesped365.com/api/' . $img['image_path'];
            }, $images);
            
            $formatted[] = [
                'id' => $report['id'],
                'fecha' => $report['visit_date'],
                'estadoGeneral' => $report['grass_health'] ?? 'Sin datos',
                'crecimientoCm' => (float)($report['growth_cm'] ?? 0),
                'plagas' => (bool)($report['pest_detected'] ?? false),
                'notaJardinero' => $report['technician_notes'] ?? 'Sin notas',
                'jardinero' => $report['technician_notes'] ?? 'Sin notas',
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
                'cespedParejo' => ($report['grass_health'] === 'excelente' || $report['grass_health'] === 'bueno'),
                'colorOk' => ($report['grass_health'] === 'excelente' || $report['grass_health'] === 'bueno'),
                'manchas' => false,
                'zonasDesgastadas' => false,
                'malezasVisibles' => (bool)($report['pest_detected'] ?? false)
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
        $report = $this->reportModel->find($id);
        
        if (!$report) {
            return $this->fail('Reporte no encontrado', 404);
        }
        
        // Obtener imágenes
        $images = $this->imageModel->getByReport($id);
        $imageUrls = array_map(function($img) {
            return 'https://cesped365.com/api/' . $img['image_path'];
        }, $images);
        
        // Formatear respuesta
        $formatted = [
            'id' => $report['id'],
            'fecha' => $report['visit_date'],
            'estadoGeneral' => $report['grass_health'] ?? 'Sin datos',
            'crecimientoCm' => (float)($report['growth_cm'] ?? 0),
            'plagas' => (bool)($report['pest_detected'] ?? false),
            'notaJardinero' => $report['technician_notes'] ?? 'Sin notas',
            'jardinero' => $report['technician_notes'] ?? 'Sin notas',
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
            'cespedParejo' => ($report['grass_health'] === 'excelente' || $report['grass_health'] === 'bueno'),
            'colorOk' => ($report['grass_health'] === 'excelente' || $report['grass_health'] === 'bueno'),
            'manchas' => false,
            'zonasDesgastadas' => false,
            'malezasVisibles' => (bool)($report['pest_detected'] ?? false)
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
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
        
        $data = [
            'garden_id' => $this->request->getVar('garden_id'),
            'user_id' => $garden['user_id'],
            'visit_date' => $this->request->getVar('visit_date'),
            'status' => 'completado',
            'grass_health' => $this->request->getVar('grass_health') ?: 'bueno',
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
        
        return $this->respondCreated([
            'success' => true,
            'message' => 'Imagen subida exitosamente',
            'data' => [
                'id' => $imageId,
                'image_url' => base_url($imagePath)
            ]
        ]);
    }
}
