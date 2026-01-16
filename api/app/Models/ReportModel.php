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
        'date',
        'estado_general',
        'cesped_parejo',
        'color_ok',
        'manchas',
        'zonas_desgastadas',
        'malezas_visibles',
        'crecimiento_cm',
        'compactacion',
        'humedad',
        'plagas',
        'observaciones',
        'jardinero'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'cesped_parejo'       => 'boolean',
        'color_ok'            => 'boolean',
        'manchas'             => 'boolean',
        'zonas_desgastadas'   => 'boolean',
        'malezas_visibles'    => 'boolean',
        'plagas'              => 'boolean',
        'crecimiento_cm'      => 'float',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'garden_id'      => 'required|is_natural_no_zero',
        'date'           => 'required|valid_date',
        'estado_general' => 'required|in_list[Bueno,Regular,Malo]',
        'jardinero'      => 'required|min_length[3]|max_length[100]',
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
                    ->orderBy('date', 'DESC')
                    ->findAll();
    }

    /**
     * Get latest reports
     */
    public function getLatest($limit = 10)
    {
        return $this->orderBy('date', 'DESC')
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
