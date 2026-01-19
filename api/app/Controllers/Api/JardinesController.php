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
        // Obtener todos los jardines con información del usuario
        $gardens = $this->gardenModel
            ->select('gardens.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = gardens.user_id')
            ->findAll();
        
        return $this->respond([
            'success' => true,
            'data' => $gardens
        ]);
    }
}
