<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'amount',
        'status',
        'provider',
        'provider_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription that owns the payment.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}

