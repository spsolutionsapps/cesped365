<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\GardenModel;
use App\Models\UserModel;

class JardinesController extends ResourceController
{
    protected $format = 'json';
    protected $gardenModel;
    protected $userModel;
    
    public function __construct()
    {
        $this->gardenModel = new GardenModel();
        $this->userModel = new UserModel();
    }
    
    public function index()
    {
        $session = \Config\Services::session();
        $userRole = $session->get('user_role');
        $userId = $session->get('user_id');

        $builder = $this->gardenModel
            ->select('gardens.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = gardens.user_id');

        // Clientes solo ven sus propios jardines; admin ve todos
        if ($userRole === 'cliente' && $userId) {
            $builder->where('gardens.user_id', (int) $userId);
        }

        $gardens = $builder->findAll();

        return $this->respond([
            'success' => true,
            'data' => $gardens
        ]);
    }
}
