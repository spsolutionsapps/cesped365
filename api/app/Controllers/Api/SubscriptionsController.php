<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SubscriptionModel;
use App\Models\UserSubscriptionModel;

class SubscriptionsController extends ResourceController
{
    protected $format = 'json';
    protected $subscriptionModel;
    protected $userSubscriptionModel;
    
    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->userSubscriptionModel = new UserSubscriptionModel();
    }
    
    /**
     * Listar todos los planes de suscripción disponibles
     * GET /api/subscriptions/plans
     */
    public function plans()
    {
        $plans = $this->subscriptionModel->getActivePlans();
        
        $formatted = [];
        foreach ($plans as $plan) {
            $formatted[] = [
                'id' => $plan['id'],
                'name' => $plan['name'],
                'description' => $plan['description'],
                'price' => (float)$plan['price'],
                'frequency' => $plan['frequency'],
                'visitsPerMonth' => $plan['visits_per_month'],
                'features' => json_decode($plan['features'] ?? '[]'),
                'isActive' => (bool)$plan['is_active']
            ];
        }
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    /**
     * Ver detalles de un plan
     * GET /api/subscriptions/plans/:id
     */
    public function showPlan($id = null)
    {
        $plan = $this->subscriptionModel->find($id);
        
        if (!$plan) {
            return $this->fail('Plan no encontrado', 404);
        }
        
        $formatted = [
            'id' => $plan['id'],
            'name' => $plan['name'],
            'description' => $plan['description'],
            'price' => (float)$plan['price'],
            'frequency' => $plan['frequency'],
            'visitsPerMonth' => $plan['visits_per_month'],
            'features' => json_decode($plan['features'] ?? '[]'),
            'isActive' => (bool)$plan['is_active']
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    /**
     * Crear nuevo plan (admin)
     * POST /api/subscriptions/plans
     */
    public function createPlan()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'price' => 'required|decimal',
            'frequency' => 'required|in_list[mensual,trimestral,semestral,anual]',
            'visits_per_month' => 'required|integer'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'price' => $this->request->getPost('price'),
            'frequency' => $this->request->getPost('frequency'),
            'visits_per_month' => $this->request->getPost('visits_per_month'),
            'features' => $this->request->getPost('features') ? json_encode($this->request->getPost('features')) : null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 1
        ];
        
        $planId = $this->subscriptionModel->insert($data);
        
        if (!$planId) {
            return $this->fail('Error al crear el plan', 500);
        }
        
        $plan = $this->subscriptionModel->find($planId);
        
        return $this->respondCreated([
            'success' => true,
            'message' => 'Plan creado exitosamente',
            'data' => [
                'id' => $plan['id'],
                'name' => $plan['name'],
                'price' => (float)$plan['price']
            ]
        ]);
    }
    
    /**
     * Listar todas las suscripciones de usuarios (admin)
     * GET /api/subscriptions
     */
    public function index()
    {
        $subscriptions = $this->userSubscriptionModel->getAllWithDetails();
        
        $formatted = [];
        foreach ($subscriptions as $sub) {
            $formatted[] = [
                'id' => $sub['id'],
                'userId' => $sub['user_id'],
                'userName' => $sub['user_name'],
                'userEmail' => $sub['user_email'],
                'planName' => $sub['plan_name'],
                'price' => (float)$sub['price'],
                'frequency' => $sub['frequency'],
                'status' => $sub['status'],
                'startDate' => $sub['start_date'],
                'endDate' => $sub['end_date'],
                'nextBillingDate' => $sub['next_billing_date'],
                'autoRenew' => (bool)$sub['auto_renew'],
                'paymentMethod' => $sub['payment_method']
            ];
        }
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    /**
     * Ver detalles de una suscripción
     * GET /api/subscriptions/:id
     */
    public function show($id = null)
    {
        $subscription = $this->userSubscriptionModel->getWithDetails($id);
        
        if (!$subscription) {
            return $this->fail('Suscripción no encontrada', 404);
        }
        
        $formatted = [
            'id' => $subscription['id'],
            'userId' => $subscription['user_id'],
            'userName' => $subscription['user_name'],
            'userEmail' => $subscription['user_email'],
            'subscriptionId' => $subscription['subscription_id'],
            'planName' => $subscription['plan_name'],
            'price' => (float)$subscription['price'],
            'frequency' => $subscription['frequency'],
            'visitsPerMonth' => $subscription['visits_per_month'],
            'status' => $subscription['status'],
            'startDate' => $subscription['start_date'],
            'endDate' => $subscription['end_date'],
            'nextBillingDate' => $subscription['next_billing_date'],
            'autoRenew' => (bool)$subscription['auto_renew'],
            'paymentMethod' => $subscription['payment_method'],
            'externalPaymentId' => $subscription['external_payment_id'],
            'notes' => $subscription['notes']
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
    
    /**
     * Crear suscripción para un usuario (admin)
     * POST /api/subscriptions
     */
    public function create()
    {
        $rules = [
            'user_id' => 'required|is_natural_no_zero',
            'subscription_id' => 'required|is_natural_no_zero',
            'start_date' => 'required|valid_date',
            'status' => 'permit_empty|in_list[activa,pausada,vencida,cancelada]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }
        
        $data = [
            'user_id' => $this->request->getPost('user_id'),
            'subscription_id' => $this->request->getPost('subscription_id'),
            'status' => $this->request->getPost('status') ?? 'activa',
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'next_billing_date' => $this->request->getPost('next_billing_date'),
            'auto_renew' => $this->request->getPost('auto_renew') ? 1 : 1,
            'payment_method' => $this->request->getPost('payment_method'),
            'external_payment_id' => $this->request->getPost('external_payment_id'),
            'notes' => $this->request->getPost('notes')
        ];
        
        $subscriptionId = $this->userSubscriptionModel->insert($data);
        
        if (!$subscriptionId) {
            return $this->fail('Error al crear la suscripción', 500);
        }
        
        $subscription = $this->userSubscriptionModel->getWithDetails($subscriptionId);
        
        return $this->respondCreated([
            'success' => true,
            'message' => 'Suscripción creada exitosamente',
            'data' => [
                'id' => $subscription['id'],
                'userName' => $subscription['user_name'],
                'planName' => $subscription['plan_name'],
                'status' => $subscription['status']
            ]
        ]);
    }
    
    /**
     * Actualizar suscripción (admin)
     * PUT /api/subscriptions/:id
     */
    public function update($id = null)
    {
        $subscription = $this->userSubscriptionModel->find($id);
        
        if (!$subscription) {
            return $this->fail('Suscripción no encontrada', 404);
        }
        
        $data = [];
        
        if ($this->request->getPost('status')) {
            $data['status'] = $this->request->getPost('status');
        }
        if ($this->request->getPost('end_date') !== null) {
            $data['end_date'] = $this->request->getPost('end_date');
        }
        if ($this->request->getPost('next_billing_date') !== null) {
            $data['next_billing_date'] = $this->request->getPost('next_billing_date');
        }
        if ($this->request->getPost('auto_renew') !== null) {
            $data['auto_renew'] = $this->request->getPost('auto_renew') ? 1 : 0;
        }
        if ($this->request->getPost('payment_method')) {
            $data['payment_method'] = $this->request->getPost('payment_method');
        }
        if ($this->request->getPost('external_payment_id') !== null) {
            $data['external_payment_id'] = $this->request->getPost('external_payment_id');
        }
        if ($this->request->getPost('notes') !== null) {
            $data['notes'] = $this->request->getPost('notes');
        }
        
        if (!empty($data)) {
            $this->userSubscriptionModel->update($id, $data);
        }
        
        $updated = $this->userSubscriptionModel->getWithDetails($id);
        
        return $this->respond([
            'success' => true,
            'message' => 'Suscripción actualizada exitosamente',
            'data' => [
                'id' => $updated['id'],
                'status' => $updated['status'],
                'nextBillingDate' => $updated['next_billing_date']
            ]
        ]);
    }
    
    /**
     * Cancelar suscripción
     * POST /api/subscriptions/:id/cancel
     */
    public function cancel($id = null)
    {
        $subscription = $this->userSubscriptionModel->find($id);
        
        if (!$subscription) {
            return $this->fail('Suscripción no encontrada', 404);
        }
        
        $this->userSubscriptionModel->cancelSubscription($id);
        
        return $this->respond([
            'success' => true,
            'message' => 'Suscripción cancelada exitosamente'
        ]);
    }
    
    /**
     * Pausar suscripción
     * POST /api/subscriptions/:id/pause
     */
    public function pause($id = null)
    {
        $subscription = $this->userSubscriptionModel->find($id);
        
        if (!$subscription) {
            return $this->fail('Suscripción no encontrada', 404);
        }
        
        $this->userSubscriptionModel->pauseSubscription($id);
        
        return $this->respond([
            'success' => true,
            'message' => 'Suscripción pausada exitosamente'
        ]);
    }
    
    /**
     * Reactivar suscripción
     * POST /api/subscriptions/:id/reactivate
     */
    public function reactivate($id = null)
    {
        $subscription = $this->userSubscriptionModel->find($id);
        
        if (!$subscription) {
            return $this->fail('Suscripción no encontrada', 404);
        }
        
        $this->userSubscriptionModel->reactivateSubscription($id);
        
        return $this->respond([
            'success' => true,
            'message' => 'Suscripción reactivada exitosamente'
        ]);
    }
    
    /**
     * Obtener suscripción activa del usuario actual (cliente)
     * GET /api/subscriptions/my-subscription
     */
    public function mySubscription()
    {
        $userId = $this->request->userId ?? null;
        
        if (!$userId) {
            return $this->fail('Usuario no identificado', 401);
        }
        
        $subscription = $this->userSubscriptionModel->getActiveByUser($userId);
        
        if (!$subscription) {
            return $this->respond([
                'success' => true,
                'data' => null,
                'message' => 'No tiene suscripción activa'
            ]);
        }
        
        $formatted = [
            'id' => $subscription['id'],
            'planName' => $subscription['plan_name'],
            'price' => (float)$subscription['price'],
            'frequency' => $subscription['frequency'],
            'visitsPerMonth' => $subscription['visits_per_month'],
            'status' => $subscription['status'],
            'startDate' => $subscription['start_date'],
            'nextBillingDate' => $subscription['next_billing_date'],
            'autoRenew' => (bool)$subscription['auto_renew']
        ];
        
        return $this->respond([
            'success' => true,
            'data' => $formatted
        ]);
    }
}
