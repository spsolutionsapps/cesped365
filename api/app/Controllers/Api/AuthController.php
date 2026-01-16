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
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        
        // Buscar usuario por email
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            return $this->fail('Credenciales inválidas', 401);
        }
        
        // Verificar password
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            return $this->fail('Credenciales inválidas', 401);
        }
        
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
}
