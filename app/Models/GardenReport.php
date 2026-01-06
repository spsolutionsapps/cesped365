<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GardenReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'report_date',
        'general_status',
        'grass_even',
        'grass_color',
        'grass_spots',
        'worn_areas',
        'visible_weeds',
        'grass_note',
        'growth_cm',
        'growth_category',
        'growth_estimated',
        'growth_note',
        'soil_condition',
        'aeration_recommended',
        'soil_note',
        'humidity_status',
        'humidity_note',
        'pests_status',
        'pests_note',
        'flowerbeds_status',
        'flowerbeds_note',
        'seasonal_recommendations',
        'general_observations',
    ];

    protected $casts = [
        'report_date' => 'date',
        'grass_even' => 'boolean',
        'grass_spots' => 'boolean',
        'worn_areas' => 'boolean',
        'visible_weeds' => 'boolean',
        'growth_cm' => 'decimal:2',
        'growth_estimated' => 'decimal:2',
        'aeration_recommended' => 'boolean',
    ];

    /**
     * Get the user that owns the garden report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription that owns the garden report.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the images for the garden report.
     */
    public function images()
    {
        return $this->hasMany(GardenReportImage::class);
    }
}

