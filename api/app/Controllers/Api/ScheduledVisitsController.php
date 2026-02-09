<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ScheduledVisitModel;
use App\Models\GardenModel;
use App\Models\UserModel;
use App\Models\BlockedDayModel;

class ScheduledVisitsController extends ResourceController
{
    /** Franjas fijas de 2 horas que los clientes pueden elegir */
    public const SLOTS = ['07:00', '09:00', '11:00', '14:00', '16:00'];

    protected $format = 'json';
    protected $scheduledVisitModel;
    protected $gardenModel;
    protected $userModel;
    protected $blockedDayModel;

    /**
     * Normaliza una hora guardada a una de las franjas (inicio de la franja de 2h).
     */
    protected function timeToSlot(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }
        $parts = array_map('intval', explode(':', trim($time)));
        $h = $parts[0] ?? 0;
        $m = $parts[1] ?? 0;
        $min = $h * 60 + $m;
        if ($min >= 7 * 60 && $min < 9 * 60) {
            return '07:00';
        }
        if ($min >= 9 * 60 && $min < 11 * 60) {
            return '09:00';
        }
        if ($min >= 11 * 60 && $min < 14 * 60) {
            return '11:00';
        }
        if ($min >= 14 * 60 && $min < 16 * 60) {
            return '14:00';
        }
        if ($min >= 16 * 60 && $min < 18 * 60) {
            return '16:00';
        }
        return null;
    }
    
    public function __construct()
    {
        $this->scheduledVisitModel = new ScheduledVisitModel();
        $this->gardenModel = new GardenModel();
        $this->userModel = new UserModel();
        $this->blockedDayModel = new BlockedDayModel();
    }
    
    public function index()
    {
        // Obtener visitas programadas
        // Solo filtrar si el rol es exactamente 'cliente'; admin (o cualquier otro) ve todas.
        $session = \Config\Services::session();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');
        $isCliente = ($userRole === 'cliente');

        $builder = $this->scheduledVisitModel->orderBy('scheduled_date', 'ASC');

        if ($isCliente && $userId) {
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
            // Cliente ve todas sus visitas (programada, completada, cancelada) para que vea en rojo las canceladas
        }
        // Admin (o rol no cliente): devolver todas las visitas sin filtrar por garden

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
    
    /**
     * Disponibilidad por rango de fechas.
     * - occupied_dates: días con todas las franjas ocupadas (para marcar en el calendario).
     * - occupied_slots_by_date: por cada fecha, lista de franjas ya tomadas (07:00, 09:00, etc.).
     * GET scheduled-visits/availability?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function availability()
    {
        $from = $this->request->getGet('from');
        $to = $this->request->getGet('to');

        if (!$from || !$to) {
            return $this->respond([
                'success' => false,
                'message' => 'Parámetros from y to (YYYY-MM-DD) son requeridos',
            ], 400);
        }

        $db = \Config\Database::connect();
        $rows = $db->query(
            "SELECT scheduled_date, scheduled_time 
             FROM scheduled_visits 
             WHERE status = 'programada' 
             AND DATE(scheduled_date) >= ? 
             AND DATE(scheduled_date) <= ?",
            [$from, $to]
        )->getResultArray();

        $occupiedSlotsByDate = [];
        foreach ($rows as $row) {
            $date = (string) substr($row['scheduled_date'], 0, 10);
            $slot = $this->timeToSlot($row['scheduled_time'] ?? '');
            if ($slot === null) {
                continue;
            }
            if (!isset($occupiedSlotsByDate[$date])) {
                $occupiedSlotsByDate[$date] = [];
            }
            if (!in_array($slot, $occupiedSlotsByDate[$date], true)) {
                $occupiedSlotsByDate[$date][] = $slot;
            }
        }

        $allSlots = self::SLOTS;
        $occupiedDates = [];
        foreach ($occupiedSlotsByDate as $date => $slots) {
            if (count($slots) >= count($allSlots)) {
                $occupiedDates[] = $date;
            }
        }

        // Días bloqueados por el admin (ej: Feriado): nadie puede reservar
        $blockedRows = $this->blockedDayModel
            ->where('blocked_date >=', $from)
            ->where('blocked_date <=', $to)
            ->findAll();
        $blockedDates = [];
        foreach ($blockedRows as $row) {
            $date = (string) substr($row['blocked_date'], 0, 10);
            $blockedDates[] = $date;
            if (!in_array($date, $occupiedDates, true)) {
                $occupiedDates[] = $date;
            }
            $occupiedSlotsByDate[$date] = $allSlots; // todas las franjas "ocupadas"
        }
        sort($occupiedDates);
        sort($blockedDates);

        return $this->respond([
            'success' => true,
            'data' => [
                'occupied_dates' => $occupiedDates,
                'occupied_slots_by_date' => $occupiedSlotsByDate,
                'blocked_dates' => $blockedDates,
            ],
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
        
        $session = \Config\Services::session();
        $userRole = $session->get('user_role');
        
        $rules = [
            'garden_id'      => 'required|integer',
            'scheduled_date' => 'required|valid_date',
            'gardener_name'  => 'permit_empty|max_length[100]',
            'notes'          => 'permit_empty',
        ];
        if ($userRole === 'cliente') {
            $rules['scheduled_time'] = 'required|in_list[07:00,09:00,11:00,14:00,16:00]';
        } else {
            $rules['scheduled_time'] = 'permit_empty|max_length[10]';
        }
        
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

        $userId = $session->get('user_id');
        if ($userRole === 'cliente') {
            if ((int) $garden['user_id'] !== (int) $userId) {
                return $this->fail('No puedes agendar una visita para un jardín que no es tuyo', 403);
            }
        }

        // No permitir reservar en días bloqueados por el admin (ej: feriado)
        $dateStr = date('Y-m-d', strtotime($input['scheduled_date']));
        $blocked = $this->blockedDayModel->where('blocked_date', $dateStr)->first();
        if ($blocked) {
            return $this->fail('Ese día está bloqueado. No se pueden reservar turnos.', 409);
        }

        $slot = $this->timeToSlot($input['scheduled_time'] ?? '');
        $date = $input['scheduled_date'];
        if ($slot !== null) {
            $existing = $this->scheduledVisitModel
                ->where('status', 'programada')
                ->where('DATE(scheduled_date)', $date)
                ->findAll();
            foreach ($existing as $v) {
                if ($this->timeToSlot($v['scheduled_time'] ?? '') === $slot) {
                    return $this->fail('Ese horario ya está ocupado para esa fecha. Elegí otro.', 409);
                }
            }
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
        
        $session = \Config\Services::session();
        $userRole = $session->get('user_role');
        $userId = $session->get('user_id');
        $garden = $this->gardenModel->find($visit['garden_id']);

        if ($userRole === 'cliente') {
            if (!$garden || (int) $garden['user_id'] !== (int) $userId) {
                return $this->fail('No podés modificar esta visita', 403);
            }
        }

        // Preparar datos para actualizar (cliente solo puede cambiar fecha, horario o cancelar)
        $updateData = [];
        if ($userRole === 'admin') {
            if (isset($input['gardener_name'])) {
                $updateData['gardener_name'] = $input['gardener_name'];
            }
            if (isset($input['notes'])) {
                $updateData['notes'] = $input['notes'];
            }
        }
        if (isset($input['scheduled_date'])) {
            $updateData['scheduled_date'] = $input['scheduled_date'];
        }
        if (isset($input['scheduled_time'])) {
            $updateData['scheduled_time'] = $input['scheduled_time'];
        }
        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
        }

        $newDate = $updateData['scheduled_date'] ?? $visit['scheduled_date'];
        $newTime = $updateData['scheduled_time'] ?? $visit['scheduled_time'];
        $newDateStr = is_string($newDate) ? substr($newDate, 0, 10) : date('Y-m-d', strtotime($newDate));
        $newSlot = $this->timeToSlot($newTime);
        if ($newSlot !== null) {
            $existing = $this->scheduledVisitModel
                ->where('status', 'programada')
                ->where('id !=', $id)
                ->where('DATE(scheduled_date)', $newDateStr)
                ->findAll();
            foreach ($existing as $v) {
                if ($this->timeToSlot($v['scheduled_time'] ?? '') === $newSlot) {
                    return $this->fail('Ese horario ya está ocupado para esa fecha. Elegí otro.', 409);
                }
            }
        }

        // Validar franja si es cliente y está cambiando horario
        if ($userRole === 'cliente' && isset($input['scheduled_time'])) {
            if (!in_array($input['scheduled_time'], self::SLOTS, true)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'El horario debe ser una de las franjas: 07:00, 09:00, 11:00, 14:00, 16:00',
                ], 400);
            }
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
