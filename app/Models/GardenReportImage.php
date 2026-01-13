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

    /**
     * Public URL to serve this image reliably on shared hosting.
     *
     * We use the /storage/garden-reports/{filename} route so images work even when
     * the /public/storage symlink is missing.
     */
    public function getPublicUrlAttribute(): ?string
    {
        if (!$this->getKey()) {
            return null;
        }

        return route('garden-report-images.show', ['image' => $this->getKey()]);
    }
}

