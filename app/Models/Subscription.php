<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan that owns the subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the garden reports for the subscription.
     */
    public function gardenReports()
    {
        return $this->hasMany(GardenReport::class);
    }
}

