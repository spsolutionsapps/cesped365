<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table            = 'subscriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'description',
        'price',
        'frequency',
        'visits_per_month',
        'features',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'int',
        'price' => 'float',
        'visits_per_month' => 'int',
        'is_active' => 'boolean'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
        'price' => 'required|decimal',
        'frequency' => 'required|in_list[mensual,trimestral,semestral,anual]',
        'visits_per_month' => 'required|integer',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'El nombre del plan es requerido',
            'min_length' => 'El nombre debe tener al menos 3 caracteres',
        ],
        'price' => [
            'required' => 'El precio es requerido',
            'decimal' => 'El precio debe ser un número válido',
        ],
    ];

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
     * Obtener planes activos
     */
    public function getActivePlans()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Obtener plan por nombre
     */
    public function getByName($name)
    {
        return $this->where('name', $name)->first();
    }
}
