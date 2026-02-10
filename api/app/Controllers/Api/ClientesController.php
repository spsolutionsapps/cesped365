<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\GardenModel;
use App\Models\ReportModel;
use App\Models\UserSubscriptionModel;

class ClientesController extends ResourceController
{
    protected $format = 'json';
    protected $userModel;
    protected $gardenModel;
    protected $reportModel;
    protected $userSubscriptionModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->gardenModel = new GardenModel();
        $this->reportModel = new ReportModel();
        $this->userSubscriptionModel = new UserSubscriptionModel();
    }
    
    public function index()
    {
        $search = $this->request->getGet('search');
        $plan = $this->request->getGet('plan');
        $estado = $this->request->getGet('estado');
        
        // Obtener solo clientes (role='cliente')
        $builder = $this->userModel->where('role', 'cliente');
        
        // Aplicar búsqueda
        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }
        
        $clientes = $builder->findAll();
        
        // Formatear para el frontend
        $formatted = [];
        foreach ($clientes as $cliente) {
            // Derivar estado real desde suscripción activa (fuente de verdad)
            $activeSub = $this->userSubscriptionModel
                ->where('user_id', $cliente['id'])
                ->where('status', 'activa')
                ->orderBy('id', 'DESC')
                ->first();

            // Si el admin puso Cancelado, respetarlo; si no, derivar de suscripción
            $estado = ($cliente['estado'] ?? '') === 'Cancelado'
                ? 'Cancelado'
                : ($activeSub ? 'Activo' : ($cliente['estado'] ?? 'Pendiente'));

            // Obtener jardín del cliente
            $garden = $this->gardenModel->where('user_id', $cliente['id'])->first();
            
            // Obtener último reporte del jardín
            $ultimoReporte = null;
            if ($garden) {
                $ultimoReporte = $this->reportModel
                    ->where('garden_id', $garden['id'])
                    ->orderBy('visit_date', 'DESC')
                    ->first();
            }
            
            $formatted[] = [
                'id' => $cliente['id'],
                'nombre' => $cliente['name'],
                'email' => $cliente['email'],
                'telefono' => $cliente['phone'] ?? 'Sin teléfono',
                'direccion' => $cliente['address'] ?? 'Sin dirección',
                'plan' => $cliente['plan'] ?? 'Urbano',
                'estado' => $estado,
                'referidoPor' => $cliente['referido_por'] ?? null,
                'lat' => isset($cliente['lat']) ? (float) $cliente['lat'] : null,
                'lng' => isset($cliente['lng']) ? (float) $cliente['lng'] : null,
                'ultimaVisita' => $ultimoReporte ? $ultimoReporte['visit_date'] : null,
                'proximaVisita' => null // Por implementar
            ];
        }
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    public function show($id = null)
    {
        // Verificar que sea un cliente
        $cliente = $this->userModel->where('id', $id)->where('role', 'cliente')->first();
        
        if (!$cliente) {
            return $this->fail('Cliente no encontrado', 404);
        }

        $activeSub = $this->userSubscriptionModel
            ->where('user_id', $cliente['id'])
            ->where('status', 'activa')
            ->orderBy('id', 'DESC')
            ->first();
        // Si el admin puso Cancelado, respetarlo; si no, derivar de suscripción
        $estado = ($cliente['estado'] ?? '') === 'Cancelado'
            ? 'Cancelado'
            : ($activeSub ? 'Activo' : ($cliente['estado'] ?? 'Pendiente'));
        
        // Obtener jardín
        $garden = $this->gardenModel->where('user_id', $id)->first();
        
        // Obtener último reporte
        $ultimoReporte = null;
        if ($garden) {
            $ultimoReporte = $this->reportModel
                ->where('garden_id', $garden['id'])
                ->orderBy('visit_date', 'DESC')
                ->first();
        }
        
        $formatted = [
            'id' => $cliente['id'],
            'nombre' => $cliente['name'],
            'email' => $cliente['email'],
            'telefono' => $cliente['phone'] ?? 'Sin teléfono',
            'direccion' => $cliente['address'] ?? 'Sin dirección',
            'plan' => $cliente['plan'] ?? 'Urbano',
            'estado' => $estado,
            'referidoPor' => $cliente['referido_por'] ?? null,
            'lat' => isset($cliente['lat']) ? (float) $cliente['lat'] : null,
            'lng' => isset($cliente['lng']) ? (float) $cliente['lng'] : null,
            'ultimaVisita' => $ultimoReporte ? $ultimoReporte['visit_date'] : null,
            'proximaVisita' => null
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    public function create()
    {
        // Obtener datos del request (soporta JSON y form-data)
        $contentType = $this->request->getHeaderLine('Content-Type');
        $isJson = strpos($contentType, 'application/json') !== false;
        
        if ($isJson) {
            $input = $this->request->getJSON(true);
            // Convertir null a string vacío para campos opcionales
            if ($input === null) {
                $input = [];
            }
        } else {
            $input = $this->request->getVar();
        }
        
        // Log para debugging
        log_message('info', 'Registro - Content-Type: ' . $contentType);
        log_message('info', 'Registro - Input recibido: ' . json_encode($input));
        
        // Validar datos directamente (aceptar referidoPor o referido_por según cómo llegue el body)
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'phone' => 'permit_empty|max_length[20]',
            'address' => 'permit_empty|max_length[255]',
            'referidoPor' => 'permit_empty|max_length[255]',
            'referido_por' => 'permit_empty|max_length[255]',
            'estado' => 'permit_empty|in_list[Activo,Pendiente,Cancelado]',
            'lat' => 'permit_empty|decimal',
            'lng' => 'permit_empty|decimal'
        ];
        
        // Para JSON, necesitamos validar manualmente
        if ($isJson) {
            $validator = \Config\Services::validation();
            $validator->setRules($rules);
            
            if (!$validator->run($input)) {
                $errors = $validator->getErrors();
                log_message('error', 'Errores de validación: ' . json_encode($errors));
                return $this->respond([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $errors
                ], 400);
            }
        } else {
            // Para form-data, usar el método normal
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                log_message('error', 'Errores de validación: ' . json_encode($errors));
                return $this->respond([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $errors
                ], 400);
            }
        }
        
        // Crear usuario (cliente)
        $userData = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'], // El modelo lo hasheará automáticamente
            'role' => 'cliente',
            'phone' => !empty($input['phone']) ? $input['phone'] : null,
            'address' => !empty($input['address']) ? $input['address'] : null,
            'plan' => $input['plan'] ?? 'Urbano',
            'estado' => in_array($input['estado'] ?? '', ['Activo', 'Pendiente', 'Cancelado']) ? $input['estado'] : 'Pendiente',
            'referido_por' => !empty($input['referidoPor']) ? $input['referidoPor'] : (!empty($input['referido_por']) ? $input['referido_por'] : null)
        ];
        // Coordenadas GPS opcionales
        if (isset($input['lat']) && $input['lat'] !== '' && $input['lat'] !== null) {
            $userData['lat'] = is_numeric($input['lat']) ? $input['lat'] : null;
        }
        if (isset($input['lng']) && $input['lng'] !== '' && $input['lng'] !== null) {
            $userData['lng'] = is_numeric($input['lng']) ? $input['lng'] : null;
        }
        
        $userId = $this->userModel->insert($userData);
        
        if (!$userId) {
            return $this->fail('Error al crear el cliente', 500);
        }
        
        // Si se proporciona dirección, crear jardín
        $address = !empty($input['address']) ? $input['address'] : null;
        if ($address) {
            $gardenData = [
                'user_id' => $userId,
                'address' => $address,
                'notes' => $input['garden_notes'] ?? ''
            ];
            
            $this->gardenModel->insert($gardenData);
        }
        
        // Obtener el cliente creado
        $cliente = $this->userModel->find($userId);
        unset($cliente['password']);
        
        return $this->respondCreated([
            'success' => true,
            'message' => 'Cliente creado exitosamente',
            'data' => [
                'id' => $cliente['id'],
                'nombre' => $cliente['name'],
                'email' => $cliente['email'],
                'telefono' => $cliente['phone'],
                'direccion' => $cliente['address']
            ]
        ]);
    }
    
    public function update($id = null)
    {
        // PUT puede llegar como JSON (recomendado) o form-urlencoded.
        $contentType = $this->request->getHeaderLine('Content-Type');
        $json = (strpos($contentType, 'application/json') !== false) ? $this->request->getJSON(true) : null;
        if (is_array($json)) {
            $input = [
                'name' => $json['name'] ?? null,
                'email' => $json['email'] ?? null,
                'password' => $json['password'] ?? null,
                'phone' => $json['phone'] ?? null,
                'address' => $json['address'] ?? null,
                'plan' => $json['plan'] ?? null,
                'estado' => $json['estado'] ?? null,
                'referido_por' => $json['referido_por'] ?? $json['referidoPor'] ?? null,
                'lat' => $json['lat'] ?? null,
                'lng' => $json['lng'] ?? null,
            ];
        } else {
            $raw = $this->request->getRawInput();
            $input = [
                'name' => $raw['name'] ?? $this->request->getVar('name'),
                'email' => $raw['email'] ?? $this->request->getVar('email'),
                'password' => $raw['password'] ?? $this->request->getVar('password'),
                'phone' => $raw['phone'] ?? $this->request->getVar('phone'),
                'address' => $raw['address'] ?? $this->request->getVar('address'),
                'plan' => $raw['plan'] ?? $this->request->getVar('plan'),
                'estado' => $raw['estado'] ?? $this->request->getVar('estado'),
                'referido_por' => $raw['referido_por'] ?? $this->request->getVar('referido_por'),
                'lat' => $raw['lat'] ?? $this->request->getVar('lat'),
                'lng' => $raw['lng'] ?? $this->request->getVar('lng'),
            ];
        }
        
        // Log de datos recibidos
        log_message('info', 'UPDATE Cliente - ID: ' . $id);
        log_message('info', 'Input recibido: ' . json_encode($input));
        
        // Verificar que el cliente existe
        $cliente = $this->userModel->where('id', $id)->where('role', 'cliente')->first();
        
        if (!$cliente) {
            return $this->fail('Cliente no encontrado', 404);
        }
        
        // Validar datos
        $rules = [
            'name' => 'permit_empty|min_length[3]|max_length[100]',
            'email' => "permit_empty|valid_email|is_unique[users.email,id,{$id}]",
            'password' => 'permit_empty|min_length[6]',
            'phone' => 'permit_empty|max_length[20]',
            'address' => 'permit_empty|max_length[255]',
            'referido_por' => 'permit_empty|max_length[255]',
            'estado' => 'permit_empty|in_list[Activo,Pendiente,Cancelado]',
            'lat' => 'permit_empty|decimal',
            'lng' => 'permit_empty|decimal'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        // Preparar datos para actualizar
        $userData = [];
        
        if (!empty($input['name'])) {
            $userData['name'] = $input['name'];
        }
        if (!empty($input['email'])) {
            $userData['email'] = $input['email'];
        }
        if (!empty($input['password'])) {
            $userData['password'] = $input['password'];
        }
        if (isset($input['phone'])) {
            $userData['phone'] = $input['phone'];
        }
        if (isset($input['address'])) {
            $userData['address'] = $input['address'];
        }
        if (isset($input['plan'])) {
            $userData['plan'] = $input['plan'];
            log_message('info', 'Plan a actualizar: ' . $input['plan']);
        }
        if (isset($input['estado']) && in_array($input['estado'], ['Activo', 'Pendiente', 'Cancelado'])) {
            $userData['estado'] = $input['estado'];
        }
        if (array_key_exists('referido_por', $input)) {
            $userData['referido_por'] = ($input['referido_por'] !== '' && $input['referido_por'] !== null) ? $input['referido_por'] : null;
        }
        if (array_key_exists('lat', $input)) {
            $userData['lat'] = ($input['lat'] !== '' && $input['lat'] !== null && is_numeric($input['lat'])) ? $input['lat'] : null;
        }
        if (array_key_exists('lng', $input)) {
            $userData['lng'] = ($input['lng'] !== '' && $input['lng'] !== null && is_numeric($input['lng'])) ? $input['lng'] : null;
        }
        
        // Log de datos a actualizar
        log_message('info', 'Datos a actualizar: ' . json_encode($userData));
        
        // Actualizar usuario (skipValidation: el modelo exige password required y en edición no lo enviamos)
        if (!empty($userData)) {
            $result = $this->userModel->skipValidation(true)->update($id, $userData);
            log_message('info', 'Resultado actualización: ' . ($result ? 'éxito' : 'fallo'));
        }
        
        // Actualizar jardín si existe
        if (!empty($input['address'])) {
            $garden = $this->gardenModel->where('user_id', $id)->first();
            
            if ($garden) {
                $this->gardenModel->update($garden['id'], [
                    'address' => $input['address'],
                    'notes' => $input['garden_notes'] ?? $garden['notes']
                ]);
            } else {
                // Crear jardín si no existe
                $this->gardenModel->insert([
                    'user_id' => $id,
                    'address' => $input['address'],
                    'notes' => $input['garden_notes'] ?? ''
                ]);
            }
        }
        
        // Obtener cliente actualizado
        $clienteActualizado = $this->userModel->find($id);
        unset($clienteActualizado['password']);
        
        return $this->respond([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente',
            'data' => [
                'id' => $clienteActualizado['id'],
                'nombre' => $clienteActualizado['name'],
                'email' => $clienteActualizado['email'],
                'telefono' => $clienteActualizado['phone'],
                'direccion' => $clienteActualizado['address']
            ]
        ]);
    }
    
    public function delete($id = null)
    {
        // Verificar que el cliente existe
        $cliente = $this->userModel->where('id', $id)->where('role', 'cliente')->first();
        
        if (!$cliente) {
            return $this->fail('Cliente no encontrado', 404);
        }
        
        // Eliminar cliente (las relaciones se eliminan en cascada)
        $this->userModel->delete($id);
        
        return $this->respond([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente'
        ]);
    }
    
    public function historial($id = null)
    {
        // Verificar que el cliente existe
        $cliente = $this->userModel->where('id', $id)->where('role', 'cliente')->first();
        
        if (!$cliente) {
            return $this->fail('Cliente no encontrado', 404);
        }
        
        // Obtener jardín del cliente
        $garden = $this->gardenModel->where('user_id', $id)->first();
        
        if (!$garden) {
            return $this->respond([
                'success' => true,
                'data' => []
            ]);
        }
        
        // Obtener todos los reportes del jardín
        $reports = $this->reportModel
            ->where('garden_id', $garden['id'])
            ->orderBy('visit_date', 'DESC')
            ->findAll();
        
        // Formatear para el frontend
        $formatted = [];
        foreach ($reports as $report) {
            $tipo = 'Mantenimiento Regular';
            if (isset($report['pest_detected']) && $report['pest_detected']) {
                $tipo = 'Mantenimiento + Tratamiento';
            }
            
            $formatted[] = [
                'id' => $report['id'],
                'fecha' => $report['visit_date'],
                'tipo' => $tipo,
                'estadoGeneral' => $report['grass_health'] ?? 'Sin datos',
                'jardinero' => $report['technician_notes'] ?? 'Sin datos',
                'observaciones' => $report['recommendations'] ?? ''
            ];
        }
        
        return $this->respond([
            'success' => true,
            'data' => $formatted,
            'cliente' => [
                'id' => $cliente['id'],
                'nombre' => $cliente['name'],
                'email' => $cliente['email']
            ]
        ]);
    }
}
