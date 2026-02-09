<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table            = 'reports';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'garden_id',
        'user_id',
        'visit_date',
        'status',
        'grass_height_cm',
        'grass_health',
        'grass_color',
        'grass_even',
        'spots',
        'weeds_visible',
        'watering_status',
        'pest_detected',
        'pest_description',
        'work_done',
        'recommendations',
        'next_visit',
        'growth_cm',
        'fertilizer_applied',
        'fertilizer_type',
        'weather_conditions',
        'technician_notes',
        'client_rating',
        'client_feedback'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'pest_detected'       => '?boolean',
        'fertilizer_applied'  => '?boolean',
        'grass_height_cm'     => '?float',
        'growth_cm'           => '?float',
    ];
    protected array $castHandlers = [];

    // Dates - Desactivar timestamps automáticos porque la tabla los maneja con DEFAULT CURRENT_TIMESTAMP
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    protected $dates = [];

    // Validation
    protected $validationRules      = [
        'garden_id'      => 'required|is_natural_no_zero',
        'user_id'        => 'required|is_natural_no_zero',
        'visit_date'     => 'required|valid_date',
        'grass_health'   => 'permit_empty|in_list[excelente,bueno,regular]',
        'grass_color'    => 'permit_empty|in_list[excelente,bueno,regular]',
        'grass_even'     => 'permit_empty|in_list[0,1]',
        'spots'          => 'permit_empty|in_list[0,1]',
        'weeds_visible'  => 'permit_empty|in_list[0,1]',
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
     * Get report with garden and user info
     */
    public function getWithDetails($id)
    {
        return $this->select('reports.*, gardens.address, users.name as user_name, users.email')
                    ->join('gardens', 'gardens.id = reports.garden_id')
                    ->join('users', 'users.id = gardens.user_id')
                    ->where('reports.id', $id)
                    ->first();
    }

    /**
     * Get reports by garden
     */
    public function getByGarden($gardenId)
    {
        return $this->where('garden_id', $gardenId)
                    ->orderBy('visit_date', 'DESC')
                    ->findAll();
    }

    /**
     * Get latest reports
     */
    public function getLatest($limit = 10)
    {
        return $this->orderBy('visit_date', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get reports with images
     */
    public function getWithImages($id)
    {
        $report = $this->find($id);
        
        if (!$report) {
            return null;
        }

        $imageModel = new ReportImageModel();
        $report['imagenes'] = $imageModel->where('report_id', $id)->findAll();
        
        return $report;
    }
}
