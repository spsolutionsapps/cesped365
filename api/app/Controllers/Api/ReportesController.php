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
        $limit = $this->request->getGet('limit') ?? 10;
        
        // Obtener reportes con paginación
        $reports = $this->reportModel
            ->orderBy('date', 'DESC')
            ->paginate($limit);
        
        $total = $this->reportModel->countAll();
        
        // Formatear respuesta para coincidir con el formato del frontend
        $formatted = [];
        foreach ($reports as $report) {
            // Obtener imágenes del reporte
            $images = $this->imageModel->getByReport($report['id']);
            $imageUrls = array_map(function($img) {
                return 'http://localhost:8080/' . $img['image_path'];
            }, $images);
            
            $formatted[] = [
                'id' => $report['id'],
                'fecha' => $report['date'],
                'estadoGeneral' => $report['estado_general'],
                'cespedParejo' => (bool)$report['cesped_parejo'],
                'colorOk' => (bool)$report['color_ok'],
                'manchas' => (bool)$report['manchas'],
                'zonasDesgastadas' => (bool)$report['zonas_desgastadas'],
                'malezasVisibles' => (bool)$report['malezas_visibles'],
                'crecimientoCm' => (float)$report['crecimiento_cm'],
                'compactacion' => $report['compactacion'],
                'humedad' => $report['humedad'],
                'plagas' => (bool)$report['plagas'],
                'notaJardinero' => $report['observaciones'],
                'jardinero' => $report['jardinero'],
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
            return 'http://localhost:8080/' . $img['image_path'];
        }, $images);
        
        // Formatear respuesta
        $formatted = [
            'id' => $report['id'],
            'fecha' => $report['date'],
            'estadoGeneral' => $report['estado_general'],
            'cespedParejo' => (bool)$report['cesped_parejo'],
            'colorOk' => (bool)$report['color_ok'],
            'manchas' => (bool)$report['manchas'],
            'zonasDesgastadas' => (bool)$report['zonas_desgastadas'],
            'malezasVisibles' => (bool)$report['malezas_visibles'],
            'crecimientoCm' => (float)$report['crecimiento_cm'],
            'compactacion' => $report['compactacion'],
            'humedad' => $report['humedad'],
            'plagas' => (bool)$report['plagas'],
            'notaJardinero' => $report['observaciones'],
            'jardinero' => $report['jardinero'],
            'imagenes' => $imageUrls
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    public function create()
    {
        // Validar datos de entrada
        $rules = [
            'garden_id' => 'required|is_natural_no_zero',
            'date' => 'required|valid_date',
            'estado_general' => 'required|in_list[Bueno,Regular,Malo]',
            'jardinero' => 'required|min_length[3]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        // Preparar datos
        $data = [
            'garden_id' => $this->request->getPost('garden_id'),
            'date' => $this->request->getPost('date'),
            'estado_general' => $this->request->getPost('estado_general'),
            'cesped_parejo' => $this->request->getPost('cesped_parejo') ? 1 : 0,
            'color_ok' => $this->request->getPost('color_ok') ? 1 : 0,
            'manchas' => $this->request->getPost('manchas') ? 1 : 0,
            'zonas_desgastadas' => $this->request->getPost('zonas_desgastadas') ? 1 : 0,
            'malezas_visibles' => $this->request->getPost('malezas_visibles') ? 1 : 0,
            'crecimiento_cm' => $this->request->getPost('crecimiento_cm') ?? null,
            'compactacion' => $this->request->getPost('compactacion') ?? null,
            'humedad' => $this->request->getPost('humedad') ?? null,
            'plagas' => $this->request->getPost('plagas') ? 1 : 0,
            'observaciones' => $this->request->getPost('observaciones') ?? '',
            'jardinero' => $this->request->getPost('jardinero')
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
                'date' => $report['date'],
                'estado_general' => $report['estado_general']
            ]
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
