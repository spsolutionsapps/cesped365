<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class AuthController extends ResourceController
{
    protected $format = 'json';
    protected $userModel;
    protected $session;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
    }
    
    public function login()
    {
        // Aceptar tanto JSON como form-data
        $contentType = $this->request->getHeaderLine('Content-Type');
        $isJson = strpos($contentType, 'application/json') !== false;
        
        if ($isJson) {
            $input = $this->request->getJSON(true) ?? [];
            $email = $input['email'] ?? null;
            $password = $input['password'] ?? null;
        } else {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
        }
        
        // Validar que se recibieron los datos
        if (empty($email) || empty($password)) {
            return $this->fail('Email y contraseña son requeridos', 400);
        }
        
        // Buscar usuario por email
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            return $this->respond([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }
        
        // Verificar password
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            // Log para debugging (solo en desarrollo)
            log_message('debug', "Login fallido para {$email}: contraseña incorrecta");
            return $this->respond([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }
        
        // Log para debugging (solo en desarrollo)
        log_message('debug', "Login exitoso para {$email} (rol: {$user['role']})");
        
        // Crear sesión
        $sessionData = [
            'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_name' => $user['name'],
            'user_role' => $user['role'],
            'logged_in' => true
        ];
        
        $this->session->set($sessionData);
        
        // Generar token de sesión (ID de sesión)
        $token = $this->session->session_id;
        
        // Remover password de la respuesta
        unset($user['password']);
        
        return $this->respond([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }
    
    public function logout()
    {
        // Destruir sesión
        $this->session->destroy();
        
        return $this->respond([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
    
    public function me()
    {
        // Verificar si hay sesión activa
        if (!$this->session->has('user_id')) {
            return $this->fail('No autorizado', 401);
        }
        
        // Obtener datos del usuario desde la sesión
        $userId = $this->session->get('user_id');
        
        // Buscar usuario actualizado en la base de datos
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            // Si el usuario fue eliminado, destruir sesión
            $this->session->destroy();
            return $this->fail('Usuario no encontrado', 404);
        }
        
        // Remover password
        unset($user['password']);
        
        return $this->respond([
            'success' => true,
            'user' => $user
        ]);
    }
    
    public function updateProfile()
    {
        // Verificar si hay sesión activa
        if (!$this->session->has('user_id')) {
            return $this->fail('No autorizado', 401);
        }
        
        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return $this->fail('Usuario no encontrado', 404);
        }
        
        // Obtener datos del request
        $contentType = $this->request->getHeaderLine('Content-Type');
        $isJson = strpos($contentType, 'application/json') !== false;
        
        if ($isJson) {
            $input = $this->request->getJSON(true) ?? [];
        } else {
            $input = $this->request->getVar();
        }
        
        // Validar datos según el rol
        $rules = [];
        $updateData = [];
        
        if ($user['role'] === 'cliente') {
            // Clientes solo pueden cambiar: name, address
            if (isset($input['name'])) {
                $rules['name'] = 'permit_empty|min_length[3]|max_length[100]';
                if (!empty($input['name'])) {
                    $updateData['name'] = $input['name'];
                }
            }
            if (isset($input['address'])) {
                $rules['address'] = 'permit_empty|max_length[255]';
                if (!empty($input['address']) || $input['address'] === '') {
                    $updateData['address'] = $input['address'] ?: null;
                }
            }
        } else {
            // Admin puede cambiar: name
            if (isset($input['name'])) {
                $rules['name'] = 'permit_empty|min_length[3]|max_length[100]';
                if (!empty($input['name'])) {
                    $updateData['name'] = $input['name'];
                }
            }
        }
        
        if (!empty($rules)) {
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
        }
        
        if (empty($updateData)) {
            return $this->respond([
                'success' => false,
                'message' => 'No hay datos para actualizar'
            ], 400);
        }
        
        // Actualizar usuario
        if (!$this->userModel->update($userId, $updateData)) {
            $errors = $this->userModel->errors();
            return $this->respond([
                'success' => false,
                'message' => 'Error al actualizar el perfil',
                'errors' => $errors
            ], 500);
        }
        
        // Obtener usuario actualizado
        $updatedUser = $this->userModel->find($userId);
        unset($updatedUser['password']);
        
        // Actualizar sesión
        $this->session->set('user_name', $updatedUser['name']);
        
        return $this->respond([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente',
            'user' => $updatedUser
        ]);
    }
    
    public function updatePassword()
    {
        // Verificar si hay sesión activa
        if (!$this->session->has('user_id')) {
            return $this->fail('No autorizado', 401);
        }
        
        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return $this->fail('Usuario no encontrado', 404);
        }
        
        // Obtener datos del request
        $contentType = $this->request->getHeaderLine('Content-Type');
        $isJson = strpos($contentType, 'application/json') !== false;
        
        if ($isJson) {
            $input = $this->request->getJSON(true) ?? [];
        } else {
            $input = $this->request->getVar();
        }
        
        $currentPassword = $input['current_password'] ?? null;
        $newPassword = $input['new_password'] ?? null;
        $confirmPassword = $input['confirm_password'] ?? null;
        
        // Validar que se recibieron todos los campos
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return $this->respond([
                'success' => false,
                'message' => 'Todos los campos son requeridos'
            ], 400);
        }
        
        // Verificar contraseña actual
        if (!$this->userModel->verifyPassword($currentPassword, $user['password'])) {
            return $this->respond([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ], 400);
        }
        
        // Verificar que las nuevas contraseñas coincidan
        if ($newPassword !== $confirmPassword) {
            return $this->respond([
                'success' => false,
                'message' => 'Las contraseñas nuevas no coinciden'
            ], 400);
        }
        
        // Validar longitud mínima
        if (strlen($newPassword) < 6) {
            return $this->respond([
                'success' => false,
                'message' => 'La nueva contraseña debe tener al menos 6 caracteres'
            ], 400);
        }
        
        // Actualizar contraseña
        if (!$this->userModel->update($userId, ['password' => $newPassword])) {
            $errors = $this->userModel->errors();
            return $this->respond([
                'success' => false,
                'message' => 'Error al actualizar la contraseña',
                'errors' => $errors
            ], 500);
        }
        
        return $this->respond([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }
}
