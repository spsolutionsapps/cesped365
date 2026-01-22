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
                return 'https://cesped365.com/' . $img['image_path'];
            }, $images);
            
            $formatted[] = [
                'id' => $report['id'],
                'fecha' => $report['visit_date'],
                'estadoGeneral' => $report['grass_health'] ?? 'Sin datos',
                'crecimientoCm' => (float)($report['growth_cm'] ?? 0),
                'plagas' => (bool)($report['pest_detected'] ?? false),
                'notaJardinero' => $report['technician_notes'] ?? '',
                'observaciones' => $report['recommendations'] ?? '',
                'garden_id' => $report['garden_id'],
                'imagenes' => $imageUrls
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
            return 'https://cesped365.com/' . $img['image_path'];
        }, $images);
        
        // Formatear respuesta
        $formatted = [
            'id' => $report['id'],
            'fecha' => $report['visit_date'],
            'estadoGeneral' => $report['grass_health'] ?? 'Sin datos',
            'crecimientoCm' => (float)($report['growth_cm'] ?? 0),
            'plagas' => (bool)($report['pest_detected'] ?? false),
            'notaJardinero' => $report['technician_notes'] ?? '',
            'observaciones' => $report['recommendations'] ?? '',
            'garden_id' => $report['garden_id'],
            'imagenes' => $imageUrls
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    public function create()
    {
        // Log de datos recibidos
        log_message('info', 'POST data: ' . json_encode($this->request->getPost()));
        
        // Validar datos de entrada
        $rules = [
            'garden_id' => 'required|is_natural_no_zero',
            'visit_date' => 'required|valid_date'
        ];
        
        if (!$this->validate($rules)) {
            log_message('error', 'Validation errors: ' . json_encode($this->validator->getErrors()));
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        // Obtener user_id del garden
        $db = \Config\Database::connect();
        $garden = $db->table('gardens')->where('id', $this->request->getPost('garden_id'))->get()->getRowArray();
        
        if (!$garden) {
            return $this->fail('Jardín no encontrado', 404);
        }
        
        $data = [
            'garden_id' => $this->request->getPost('garden_id'),
            'user_id' => $garden['user_id'],
            'visit_date' => $this->request->getPost('visit_date'),
            'status' => 'completado',
            'grass_height_cm' => $this->request->getPost('grass_height_cm') ?? null,
            'grass_health' => $this->request->getPost('grass_health') ?? 'bueno',
            'watering_status' => $this->request->getPost('watering_status') ?? 'optimo',
            'pest_detected' => $this->request->getPost('pest_detected') ? 1 : 0,
            'pest_description' => $this->request->getPost('pest_description') ?? null,
            'work_done' => $this->request->getPost('work_done') ?? '',
            'recommendations' => $this->request->getPost('recommendations') ?? '',
            'next_visit' => $this->request->getPost('next_visit') ?? null,
            'growth_cm' => $this->request->getPost('growth_cm') ?? null,
            'fertilizer_applied' => $this->request->getPost('fertilizer_applied') ? 1 : 0,
            'fertilizer_type' => $this->request->getPost('fertilizer_type') ?? null,
            'weather_conditions' => $this->request->getPost('weather_conditions') ?? null,
            'technician_notes' => $this->request->getPost('technician_notes') ?? ''
        ];
        
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
