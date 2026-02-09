<?php

namespace App\Models;

use CodeIgniter\Model;

class ManualGainModel extends Model
{
    protected $table            = 'manual_gains';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['gain_year', 'gain_month', 'amount', 'created_at'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = null;

    /**
     * Suma de montos manuales para un año/mes dados.
     */
    public function getTotalForMonth(int $year, int $month): float
    {
        $row = $this->selectSum('amount')
            ->where('gain_year', $year)
            ->where('gain_month', $month)
            ->first();
        return (float) ($row['amount'] ?? 0);
    }
}
