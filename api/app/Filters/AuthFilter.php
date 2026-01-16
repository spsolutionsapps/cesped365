<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class AuthFilter implements FilterInterface
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
        
        // Agregar información del usuario al request para uso posterior
        $request->userId = $session->get('user_id');
        $request->userRole = $session->get('user_role');
        $request->userEmail = $session->get('user_email');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita acción después
    }
}
