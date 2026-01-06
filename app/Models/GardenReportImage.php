<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GardenReportImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'garden_report_id',
        'image_path',
        'image_date',
    ];

    protected $casts = [
        'image_date' => 'date',
    ];

    /**
     * Get the garden report that owns the image.
     */
    public function gardenReport()
    {
        return $this->belongsTo(GardenReport::class);
    }
}

