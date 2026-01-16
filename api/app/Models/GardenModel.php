<?php

namespace App\Models;

use CodeIgniter\Model;

class GardenModel extends Model
{
    protected $table            = 'gardens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'address',
        'notes'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'user_id' => 'required|is_natural_no_zero',
        'address' => 'required|min_length[5]|max_length[255]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get garden with user info
     */
    public function getWithUser($id)
    {
        return $this->select('gardens.*, users.name as user_name, users.email')
                    ->join('users', 'users.id = gardens.user_id')
                    ->where('gardens.id', $id)
                    ->first();
    }

    /**
     * Get gardens by user
     */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }
}
