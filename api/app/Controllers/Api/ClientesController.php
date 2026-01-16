<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\GardenModel;
use App\Models\ReportModel;

class ClientesController extends ResourceController
{
    protected $format = 'json';
    protected $userModel;
    protected $gardenModel;
    protected $reportModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->gardenModel = new GardenModel();
        $this->reportModel = new ReportModel();
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
            // Obtener jardín del cliente
            $garden = $this->gardenModel->where('user_id', $cliente['id'])->first();
            
            // Obtener último reporte del jardín
            $ultimoReporte = null;
            if ($garden) {
                $ultimoReporte = $this->reportModel
                    ->where('garden_id', $garden['id'])
                    ->orderBy('date', 'DESC')
                    ->first();
            }
            
            $formatted[] = [
                'id' => $cliente['id'],
                'nombre' => $cliente['name'],
                'email' => $cliente['email'],
                'telefono' => $cliente['phone'] ?? 'Sin teléfono',
                'direccion' => $cliente['address'] ?? 'Sin dirección',
                'plan' => 'Premium', // Por ahora, todos tienen Premium (implementar en Fase 5)
                'estado' => 'Activo', // Por ahora, todos activos (implementar en Fase 5)
                'ultimaVisita' => $ultimoReporte ? $ultimoReporte['date'] : null,
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
        
        // Obtener jardín
        $garden = $this->gardenModel->where('user_id', $id)->first();
        
        // Obtener último reporte
        $ultimoReporte = null;
        if ($garden) {
            $ultimoReporte = $this->reportModel
                ->where('garden_id', $garden['id'])
                ->orderBy('date', 'DESC')
                ->first();
        }
        
        $formatted = [
            'id' => $cliente['id'],
            'nombre' => $cliente['name'],
            'email' => $cliente['email'],
            'telefono' => $cliente['phone'] ?? 'Sin teléfono',
            'direccion' => $cliente['address'] ?? 'Sin dirección',
            'plan' => 'Premium',
            'estado' => 'Activo',
            'ultimaVisita' => $ultimoReporte ? $ultimoReporte['date'] : null,
            'proximaVisita' => null
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    public function create()
    {
        // Validar datos
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'phone' => 'permit_empty|max_length[20]',
            'address' => 'permit_empty|max_length[255]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        // Crear usuario (cliente)
        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'), // El modelo lo hasheará automáticamente
            'role' => 'cliente',
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address')
        ];
        
        $userId = $this->userModel->insert($userData);
        
        if (!$userId) {
            return $this->fail('Error al crear el cliente', 500);
        }
        
        // Si se proporciona dirección, crear jardín
        if ($this->request->getPost('address')) {
            $gardenData = [
                'user_id' => $userId,
                'address' => $this->request->getPost('address'),
                'notes' => $this->request->getPost('garden_notes') ?? ''
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
            'address' => 'permit_empty|max_length[255]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        // Preparar datos para actualizar
        $userData = [];
        
        if ($this->request->getPost('name')) {
            $userData['name'] = $this->request->getPost('name');
        }
        if ($this->request->getPost('email')) {
            $userData['email'] = $this->request->getPost('email');
        }
        if ($this->request->getPost('password')) {
            $userData['password'] = $this->request->getPost('password');
        }
        if ($this->request->getPost('phone') !== null) {
            $userData['phone'] = $this->request->getPost('phone');
        }
        if ($this->request->getPost('address') !== null) {
            $userData['address'] = $this->request->getPost('address');
        }
        
        // Actualizar usuario
        if (!empty($userData)) {
            $this->userModel->update($id, $userData);
        }
        
        // Actualizar jardín si existe
        if ($this->request->getPost('address')) {
            $garden = $this->gardenModel->where('user_id', $id)->first();
            
            if ($garden) {
                $this->gardenModel->update($garden['id'], [
                    'address' => $this->request->getPost('address'),
                    'notes' => $this->request->getPost('garden_notes') ?? $garden['notes']
                ]);
            } else {
                // Crear jardín si no existe
                $this->gardenModel->insert([
                    'user_id' => $id,
                    'address' => $this->request->getPost('address'),
                    'notes' => $this->request->getPost('garden_notes') ?? ''
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
            ->orderBy('date', 'DESC')
            ->findAll();
        
        // Formatear para el frontend
        $formatted = [];
        foreach ($reports as $report) {
            $tipo = 'Mantenimiento Regular';
            if ($report['malezas_visibles'] || $report['manchas']) {
                $tipo = 'Mantenimiento + Tratamiento';
            }
            if ($report['zonas_desgastadas']) {
                $tipo = 'Mantenimiento + Resembrado';
            }
            
            $formatted[] = [
                'id' => $report['id'],
                'fecha' => $report['date'],
                'tipo' => $tipo,
                'estadoGeneral' => $report['estado_general'],
                'jardinero' => $report['jardinero'],
                'observaciones' => $report['observaciones']
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
