<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');
        
        // Manejar preflight OPTIONS request
        if (strtolower($request->getMethod()) === 'options') {
            $response = service('response');
            
            // En desarrollo, permitir cualquier origen localhost
            if (ENVIRONMENT === 'development' && !empty($origin)) {
                if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
                    $response->setHeader('Access-Control-Allow-Origin', $origin);
                }
            } else {
                // En producción, usar orígenes del .env
                $envOrigins = env('cors.allowedOrigins', '');
                if (!empty($envOrigins)) {
                    $allowedOrigins = array_map('trim', explode(',', $envOrigins));
                    if (in_array($origin, $allowedOrigins)) {
                        $response->setHeader('Access-Control-Allow-Origin', $origin);
                    }
                }
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
        $origin = $request->getHeaderLine('Origin');
        
        // En desarrollo, permitir cualquier origen localhost
        if (ENVIRONMENT === 'development' && !empty($origin)) {
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
                $response->setHeader('Access-Control-Allow-Origin', $origin);
            }
        } else {
            // En producción, usar orígenes del .env
            $envOrigins = env('cors.allowedOrigins', '');
            if (!empty($envOrigins)) {
                $allowedOrigins = array_map('trim', explode(',', $envOrigins));
                if (in_array($origin, $allowedOrigins)) {
                    $response->setHeader('Access-Control-Allow-Origin', $origin);
                }
            }
        }
        
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        
        return $response;
    }
}
