<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSubscriptionModel extends Model
{
    protected $table            = 'user_subscriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'subscription_id',
        'status',
        'start_date',
        'end_date',
        'next_billing_date',
        'auto_renew',
        'payment_method',
        'external_payment_id',
        'notes'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'subscription_id' => 'int',
        'auto_renew' => 'boolean'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'subscription_id' => 'required|is_natural_no_zero',
        'status' => 'required|in_list[activa,pausada,vencida,cancelada]',
        'start_date' => 'required|valid_date',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'El ID de usuario es requerido',
        ],
        'subscription_id' => [
            'required' => 'El ID de suscripción es requerido',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Obtener suscripción con detalles del plan
     */
    public function getWithDetails($id)
    {
        return $this->select('user_subscriptions.*, subscriptions.name as plan_name, subscriptions.price, subscriptions.frequency, subscriptions.visits_per_month, users.name as user_name, users.email as user_email')
            ->join('subscriptions', 'subscriptions.id = user_subscriptions.subscription_id')
            ->join('users', 'users.id = user_subscriptions.user_id')
            ->where('user_subscriptions.id', $id)
            ->first();
    }

    /**
     * Obtener suscripciones por usuario
     */
    public function getByUser($userId)
    {
        return $this->select('user_subscriptions.*, subscriptions.name as plan_name, subscriptions.price, subscriptions.frequency, subscriptions.visits_per_month')
            ->join('subscriptions', 'subscriptions.id = user_subscriptions.subscription_id')
            ->where('user_subscriptions.user_id', $userId)
            ->orderBy('user_subscriptions.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Obtener suscripción activa de un usuario
     */
    public function getActiveByUser($userId)
    {
        return $this->select('user_subscriptions.*, subscriptions.name as plan_name, subscriptions.price, subscriptions.frequency, subscriptions.visits_per_month')
            ->join('subscriptions', 'subscriptions.id = user_subscriptions.subscription_id')
            ->where('user_subscriptions.user_id', $userId)
            ->where('user_subscriptions.status', 'activa')
            ->first();
    }

    /**
     * Obtener todas las suscripciones con detalles
     */
    public function getAllWithDetails()
    {
        return $this->select('user_subscriptions.*, subscriptions.name as plan_name, subscriptions.price, subscriptions.frequency, users.name as user_name, users.email as user_email')
            ->join('subscriptions', 'subscriptions.id = user_subscriptions.subscription_id')
            ->join('users', 'users.id = user_subscriptions.user_id')
            ->orderBy('user_subscriptions.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Cancelar suscripción
     */
    public function cancelSubscription($id)
    {
        return $this->update($id, [
            'status' => 'cancelada',
            'auto_renew' => 0,
            'end_date' => date('Y-m-d')
        ]);
    }

    /**
     * Pausar suscripción
     */
    public function pauseSubscription($id)
    {
        return $this->update($id, [
            'status' => 'pausada'
        ]);
    }

    /**
     * Reactivar suscripción
     */
    public function reactivateSubscription($id)
    {
        return $this->update($id, [
            'status' => 'activa'
        ]);
    }
}
