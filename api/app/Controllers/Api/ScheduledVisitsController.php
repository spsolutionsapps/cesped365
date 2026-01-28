<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ScheduledVisitModel;
use App\Models\GardenModel;
use App\Models\UserModel;

class ScheduledVisitsController extends ResourceController
{
    protected $format = 'json';
    protected $scheduledVisitModel;
    protected $gardenModel;
    protected $userModel;
    
    public function __construct()
    {
        $this->scheduledVisitModel = new ScheduledVisitModel();
        $this->gardenModel = new GardenModel();
        $this->userModel = new UserModel();
    }
    
    public function index()
    {
        // Obtener visitas programadas
        // Si es admin, mostrar todas; si es cliente, solo las suyas
        $session = \Config\Services::session();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');
        
        $builder = $this->scheduledVisitModel->orderBy('scheduled_date', 'ASC');
        
        if ($userRole === 'cliente') {
            // Obtener jardines del cliente
            $gardens = $this->gardenModel->where('user_id', $userId)->findAll();
            $gardenIds = array_column($gardens, 'id');
            
            if (empty($gardenIds)) {
                return $this->respond([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $builder->whereIn('garden_id', $gardenIds);
            // Clientes solo ven visitas programadas
            $builder->where('status', 'programada');
        }
        // Admin ve todas las visitas (programadas, completadas, canceladas)
        
        $visits = $builder->findAll();
        
        // Formatear respuesta con información del jardín y cliente
        $formatted = [];
        foreach ($visits as $visit) {
            $garden = $this->gardenModel->find($visit['garden_id']);
            $user = null;
            if ($garden) {
                $user = $this->userModel->find($garden['user_id']);
            }
            
            $formatted[] = [
                'id' => $visit['id'],
                'garden_id' => $visit['garden_id'],
                'cliente_nombre' => $user ? $user['name'] : 'N/A',
                'direccion' => $garden ? $garden['address'] : 'N/A',
                'scheduled_date' => $visit['scheduled_date'],
                'scheduled_time' => $visit['scheduled_time'] ?? '',
                'gardener_name' => $visit['gardener_name'] ?? '',
                'notes' => $visit['notes'] ?? '',
                'status' => $visit['status']
            ];
        }
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    public function show($id = null)
    {
        $visit = $this->scheduledVisitModel->find($id);
        
        if (!$visit) {
            return $this->fail('Visita programada no encontrada', 404);
        }
        
        $garden = $this->gardenModel->find($visit['garden_id']);
        $user = null;
        if ($garden) {
            $user = $this->userModel->find($garden['user_id']);
        }
        
        $formatted = [
            'id' => $visit['id'],
            'garden_id' => $visit['garden_id'],
            'cliente_nombre' => $user ? $user['name'] : 'N/A',
            'direccion' => $garden ? $garden['address'] : 'N/A',
            'scheduled_date' => $visit['scheduled_date'],
            'scheduled_time' => $visit['scheduled_time'] ?? '',
            'gardener_name' => $visit['gardener_name'] ?? '',
            'notes' => $visit['notes'] ?? '',
            'status' => $visit['status']
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    public function create()
    {
        // Obtener datos del request
        $contentType = $this->request->getHeaderLine('Content-Type');
        $isJson = strpos($contentType, 'application/json') !== false;
        
        if ($isJson) {
            $rawBody = $this->request->getBody();
            $input = json_decode($rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Error al parsear JSON: ' . json_last_error_msg(),
                    'raw_body' => substr($rawBody, 0, 200)
                ], 400);
            }
            $input = $input ?? [];
        } else {
            $input = $this->request->getVar();
        }
        
        // Log para debugging
        log_message('debug', 'ScheduledVisitsController::create - Input recibido: ' . json_encode($input));
        
        // Validar datos
        $rules = [
            'garden_id'      => 'required|integer',
            'scheduled_date' => 'required|valid_date',
            'scheduled_time' => 'permit_empty|max_length[10]',
            'gardener_name'  => 'permit_empty|max_length[100]',
            'notes'          => 'permit_empty',
        ];
        
        if ($isJson) {
            $validator = \Config\Services::validation();
            $validator->setRules($rules);
            
            if (!$validator->run($input)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->getErrors()
                ], 400);
            }
        } else {
            if (!$this->validate($rules)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }
        }
        
        // Verificar que el jardín existe
        $garden = $this->gardenModel->find($input['garden_id']);
        if (!$garden) {
            return $this->fail('Jardín no encontrado', 404);
        }
        
        // Crear visita programada
        $visitData = [
            'garden_id'      => intval($input['garden_id']),
            'scheduled_date' => $input['scheduled_date'],
            'status'         => 'programada'
        ];
        
        // Agregar campos opcionales solo si tienen valor
        if (!empty($input['scheduled_time'])) {
            $visitData['scheduled_time'] = $input['scheduled_time'];
        }
        if (!empty($input['gardener_name'])) {
            $visitData['gardener_name'] = $input['gardener_name'];
        }
        if (!empty($input['notes'])) {
            $visitData['notes'] = $input['notes'];
        }
        
        log_message('debug', 'ScheduledVisitsController::create - Datos a insertar: ' . json_encode($visitData));
        
        $visitId = $this->scheduledVisitModel->insert($visitData);
        
        if (!$visitId) {
            $errors = $this->scheduledVisitModel->errors();
            $errorMessage = 'Error al crear la visita programada';
            if (!empty($errors)) {
                $errorMessage .= ': ' . implode(', ', $errors);
            }
            log_message('error', 'ScheduledVisitsController::create - Error: ' . $errorMessage . ' - Errores: ' . json_encode($errors));
            return $this->respond([
                'success' => false,
                'message' => $errorMessage,
                'errors' => $errors
            ], 500);
        }
        
        log_message('debug', 'ScheduledVisitsController::create - Visita creada con ID: ' . $visitId);
        
        // Obtener la visita creada
        $visit = $this->scheduledVisitModel->find($visitId);
        $user = $this->userModel->find($garden['user_id']);
        
        $formatted = [
            'id' => $visit['id'],
            'garden_id' => $visit['garden_id'],
            'cliente_nombre' => $user ? $user['name'] : 'N/A',
            'direccion' => $garden['address'],
            'scheduled_date' => $visit['scheduled_date'],
            'scheduled_time' => $visit['scheduled_time'] ?? '',
            'gardener_name' => $visit['gardener_name'] ?? '',
            'notes' => $visit['notes'] ?? '',
            'status' => $visit['status']
        ];
        
        return $this->respondCreated([
            'success' => true,
            'message' => 'Visita programada exitosamente',
            'data' => $formatted
        ]);
    }
    
    public function update($id = null)
    {
        $visit = $this->scheduledVisitModel->find($id);
        
        if (!$visit) {
            return $this->fail('Visita programada no encontrada', 404);
        }
        
        // Obtener datos del request (soporta JSON y form-data)
        $contentType = $this->request->getHeaderLine('Content-Type');
        $isJson = strpos($contentType, 'application/json') !== false;
        
        if ($isJson) {
            $input = $this->request->getJSON(true) ?? [];
        } else {
            $input = $this->request->getVar();
        }
        
        // Validar datos
        $rules = [
            'scheduled_date' => 'permit_empty|valid_date',
            'scheduled_time' => 'permit_empty|max_length[10]',
            'gardener_name'  => 'permit_empty|max_length[100]',
            'notes'          => 'permit_empty',
            'status'         => 'permit_empty|in_list[programada,completada,cancelada]',
        ];
        
        if ($isJson) {
            $validator = \Config\Services::validation();
            $validator->setRules($rules);
            
            if (!$validator->run($input)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->getErrors()
                ], 400);
            }
        } else {
            if (!$this->validate($rules)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }
        }
        
        // Preparar datos para actualizar
        $updateData = [];
        if (isset($input['scheduled_date'])) {
            $updateData['scheduled_date'] = $input['scheduled_date'];
        }
        if (isset($input['scheduled_time'])) {
            $updateData['scheduled_time'] = $input['scheduled_time'];
        }
        if (isset($input['gardener_name'])) {
            $updateData['gardener_name'] = $input['gardener_name'];
        }
        if (isset($input['notes'])) {
            $updateData['notes'] = $input['notes'];
        }
        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
        }
        
        // Actualizar visita
        if (!empty($updateData)) {
            $this->scheduledVisitModel->update($id, $updateData);
        }
        
        // Obtener visita actualizada
        $visit = $this->scheduledVisitModel->find($id);
        $garden = $this->gardenModel->find($visit['garden_id']);
        $user = $this->userModel->find($garden['user_id']);
        
        $formatted = [
            'id' => $visit['id'],
            'garden_id' => $visit['garden_id'],
            'cliente_nombre' => $user ? $user['name'] : 'N/A',
            'direccion' => $garden ? $garden['address'] : 'N/A',
            'scheduled_date' => $visit['scheduled_date'],
            'scheduled_time' => $visit['scheduled_time'] ?? '',
            'gardener_name' => $visit['gardener_name'] ?? '',
            'notes' => $visit['notes'] ?? '',
            'status' => $visit['status']
        ];
        
        return $this->respond([
            'success' => true,
            'message' => 'Visita programada actualizada exitosamente',
            'data' => $formatted
        ]);
    }
    
    public function delete($id = null)
    {
        $visit = $this->scheduledVisitModel->find($id);
        
        if (!$visit) {
            return $this->fail('Visita programada no encontrada', 404);
        }
        
        $this->scheduledVisitModel->delete($id);
        
        return $this->respond([
            'success' => true,
            'message' => 'Visita programada eliminada exitosamente'
        ]);
    }
}
