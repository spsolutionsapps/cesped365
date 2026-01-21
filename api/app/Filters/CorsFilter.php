<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Obtener orígenes permitidos desde .env (producción) o usar defaults (desarrollo)
        $envOrigins = env('cors.allowedOrigins', '');
        
        if (!empty($envOrigins)) {
            // En producción, usar los orígenes del .env
            $allowedOrigins = array_map('trim', explode(',', $envOrigins));
        } else {
            // En desarrollo, permitir orígenes locales
            $allowedOrigins = [
                'http://localhost:3000',
                'http://localhost:3001',
                'http://localhost:5173', // Vite default
                'http://localhost:5174',
                'http://127.0.0.1:3000',
                'http://127.0.0.1:5173'
            ];
        }
        
        $origin = $request->getHeaderLine('Origin');
        
        // Manejar preflight OPTIONS request
        if (strtolower($request->getMethod()) === 'options') {
            $response = service('response');
            
            if (in_array($origin, $allowedOrigins)) {
                $response->setHeader('Access-Control-Allow-Origin', $origin);
            }
            
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
            $response->setHeader('Access-Control-Max-Age', '86400');
            $response->setStatusCode(200);
            $response->setBody('');
            
            return $response;
        }
        
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Obtener orígenes permitidos desde .env (producción) o usar defaults (desarrollo)
        $envOrigins = env('cors.allowedOrigins', '');
        
        if (!empty($envOrigins)) {
            // En producción, usar los orígenes del .env
            $allowedOrigins = array_map('trim', explode(',', $envOrigins));
        } else {
            // En desarrollo, permitir orígenes locales
            $allowedOrigins = [
                'http://localhost:3000',
                'http://localhost:3001',
                'http://localhost:5173',
                'http://localhost:5174',
                'http://127.0.0.1:3000',
                'http://127.0.0.1:5173'
            ];
        }
        
        $origin = $request->getHeaderLine('Origin');
        
        if (in_array($origin, $allowedOrigins)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }
        
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        
        return $response;
    }
}
