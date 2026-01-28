<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduledVisitModel extends Model
{
    protected $table            = 'scheduled_visits';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'garden_id',
        'scheduled_date',
        'scheduled_time',
        'gardener_name',
        'notes',
        'status'
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
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'garden_id'      => 'required|integer',
        'scheduled_date' => 'required|valid_date',
        'scheduled_time' => 'permit_empty|max_length[10]',
        'gardener_name'  => 'permit_empty|max_length[100]',
        'status'         => 'permit_empty|in_list[programada,completada,cancelada]',
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
}
