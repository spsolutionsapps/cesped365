<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cesped365 API funcionando correctamente',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => phpversion(),
            'codeigniter_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
        ]);
    }
}
