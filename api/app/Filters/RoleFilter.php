<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = Services::session();
        
        // Verificar si el usuario está autenticado
        if (!$session->has('user_id')) {
            return Services::response()
                ->setJSON([
                    'success' => false,
                    'message' => 'No autorizado. Por favor, inicie sesión.'
                ])
                ->setStatusCode(401);
        }
        
        $userRole = $session->get('user_role');
        
        // Si se especificaron roles permitidos, verificar
        if (!empty($arguments)) {
            $allowedRoles = is_array($arguments) ? $arguments : [$arguments];
            
            if (!in_array($userRole, $allowedRoles)) {
                return Services::response()
                    ->setJSON([
                        'success' => false,
                        'message' => 'No tiene permisos para acceder a este recurso.'
                    ])
                    ->setStatusCode(403);
            }
        }
        
        // Agregar información del usuario al request
        $request->userId = $session->get('user_id');
        $request->userRole = $userRole;
        $request->userEmail = $session->get('user_email');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita acción después
    }
}
