<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Exceptions\MPApiException;
use App\Models\SubscriptionModel;
use App\Models\UserSubscriptionModel;
use App\Models\UserModel;

class PaymentController extends BaseController
{
    use ResponseTrait;

    protected $subscriptionModel;
    protected $userSubscriptionModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->userSubscriptionModel = new UserSubscriptionModel();

        // Verificar que el SDK de Mercado Pago esté disponible
        if (!class_exists('MercadoPago\MercadoPagoConfig')) {
            log_message('error', 'MercadoPago SDK no está disponible. Verificar instalación de composer.');
            throw new \RuntimeException('MercadoPago SDK no está disponible. Ejecutar: composer install');
        }

        // Configurar credenciales de Mercado Pago
        // CodeIgniter 4 carga las variables de entorno automáticamente
        $accessToken = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? getenv('MERCADOPAGO_ACCESS_TOKEN') ?? null;
        
        if (!$accessToken) {
            log_message('error', 'MERCADOPAGO_ACCESS_TOKEN no está configurado en las variables de entorno');
            // No lanzar excepción aquí para evitar problemas en el constructor
        } else {
            MercadoPagoConfig::setAccessToken($accessToken);
            // Configurar entorno de desarrollo
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);
            log_message('debug', 'MercadoPago configurado con access token: ' . substr($accessToken, 0, 10) . '...');
        }
    }

    private function mpGetMe(): array
    {
        try {
            $ch = curl_init('https://api.mercadopago.com/users/me');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . MercadoPagoConfig::getAccessToken()],
                CURLOPT_TIMEOUT => 10,
            ]);
            $resp = curl_exec($ch);
            curl_close($ch);
            $me = json_decode($resp ?: '{}', true);
            return is_array($me) ? $me : [];
        } catch (\Throwable $t) {
            return [];
        }
    }

    private function isTestAccount(array $me): bool
    {
        $email = strtolower((string) ($me['email'] ?? ''));
        $nickname = strtoupper((string) ($me['nickname'] ?? ''));
        return ($email && str_ends_with($email, '@testuser.com')) || ($nickname && str_starts_with($nickname, 'TEST'));
    }

    /**
     * Respuesta de error con el mismo shape que espera el frontend (src/services/api.js).
     */
    private function jsonError(string $message, int $status = 400, array $errors = null)
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return $this->response->setStatusCode($status)->setJSON($payload);
    }

    /**
     * Crear preferencia de pago para una suscripción
     * POST /api/payment/create-preference
     * Body: { "plan_id": 1 }
     */
    public function createPreference()
    {
        $rules = [
            'plan_id' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // Obtener userId del filtro de autenticación o de la sesión
        $userId = $this->request->userId ?? session()->get('user_id');
        if (!$userId) {
            return $this->failUnauthorized('Usuario no autenticado');
        }

        // Obtener información del usuario para el pagador
        $userEmail = $this->request->userEmail ?? session()->get('user_email');
        $userName = $this->request->userName ?? session()->get('user_name');
        
        // Si no tenemos el email en la sesión, obtenerlo de la base de datos
        if (!$userEmail || !$userName) {
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            if ($user) {
                $userEmail = $userEmail ?? $user['email'];
                $userName = $userName ?? $user['name'];
            }
        }

        $planId = $this->request->getVar('plan_id');
        $plan = $this->subscriptionModel->find($planId);

        if (!$plan) {
            return $this->failNotFound('Plan no encontrado');
        }

        try {
            // Inicializar cliente de preferencias
            $client = new PreferenceClient();

            // URLs de retorno
            $baseUrl = "http://localhost:5173"; // URL del Frontend
            
            // Crear request array para la preferencia
            // Nota: unit_price debe ser un número (Mercado Pago espera centavos, pero en ARS usamos pesos directamente)
            $unitPrice = (float) $plan['price'];
            
            // Separar nombre y apellido si es posible
            $nameParts = explode(' ', $userName ?? 'Usuario', 2);
            $firstName = $nameParts[0] ?: 'Usuario';
            $lastName = $nameParts[1] ?? 'Test';
            $payerEmail = $userEmail ?? 'test@example.com';
            
            // Validar que tengamos email válido
            if (empty($payerEmail) || !filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
                $payerEmail = 'test@example.com';
            }
            
            $request = [
                "items" => [
                    [
                        "title" => "Suscripción: " . $plan['name'],
                        "quantity" => 1,
                        "unit_price" => $unitPrice
                    ]
                ],
                "back_urls" => [
                    "success" => "{$baseUrl}/dashboard/pagos/exito",
                    "failure" => "{$baseUrl}/dashboard/pagos/fallo",
                    "pending" => "{$baseUrl}/dashboard/pagos/pendiente"
                ],
                "payer" => [
                    "name" => $firstName,
                    "surname" => $lastName,
                    "email" => $payerEmail
                ],
                "external_reference" => sprintf("%d|%d|%d", $userId, $planId, time()),
                "binary_mode" => false,
                "statement_descriptor" => "CESPED365",
                // Configurar métodos de pago - NO excluir ningún método para permitir todas las tarjetas
                "payment_methods" => [
                    "excluded_payment_methods" => [],
                    "excluded_payment_types" => []
                ]
            ];
            
            // Log para debugging
            log_message('info', '=== CREANDO PREFERENCIA MERCADO PAGO ===');
            log_message('info', 'User ID: ' . $userId);
            log_message('info', 'User Email (sesión): ' . ($this->request->userEmail ?? 'N/A'));
            log_message('info', 'User Email (final): ' . $payerEmail);
            log_message('info', 'User Name (sesión): ' . ($this->request->userName ?? 'N/A'));
            log_message('info', 'User Name (final): ' . $userName);
            log_message('info', 'Plan ID: ' . $planId . ', Price: ' . $unitPrice);
            log_message('info', 'Payer Info: ' . json_encode([
                'name' => $firstName,
                'surname' => $lastName,
                'email' => $payerEmail
            ]));
            log_message('info', 'Request completo: ' . json_encode($request, JSON_PRETTY_PRINT));

            // Crear preferencia
            $preference = $client->create($request);

            // Determinar si estamos en modo sandbox (token de prueba)
            $isSandbox = strpos(MercadoPagoConfig::getAccessToken(), 'TEST-') === 0;
            
            // Obtener el init_point correcto según el modo
            if ($isSandbox) {
                // En sandbox, usar sandbox_init_point si está disponible, sino init_point
                $initPoint = $preference->sandbox_init_point ?? $preference->init_point ?? null;
            } else {
                // En producción, usar init_point
                $initPoint = $preference->init_point ?? null;
            }
            
            // Si aún no hay init_point, construir la URL manualmente
            if (!$initPoint && isset($preference->id)) {
                if ($isSandbox) {
                    $initPoint = "https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id=" . $preference->id;
                } else {
                    $initPoint = "https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=" . $preference->id;
                }
            }
            
            // Log para debugging
            log_message('debug', 'Preferencia creada - ID: ' . ($preference->id ?? 'N/A') . ', Modo: ' . ($isSandbox ? 'SANDBOX' : 'PRODUCCIÓN'));
            log_message('debug', 'Init Point: ' . ($initPoint ?? 'N/A'));
            log_message('debug', 'Sandbox Init Point: ' . ($preference->sandbox_init_point ?? 'N/A'));

            return $this->respond([
                'success' => true,
                'preference_id' => $preference->id ?? null,
                'init_point' => $initPoint,
                'sandbox_init_point' => $preference->sandbox_init_point ?? $initPoint
            ]);

        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse ? $apiResponse->getStatusCode() : 'N/A';
            $content = $apiResponse ? $apiResponse->getContent() : null;
            
            $errorMessage = $e->getMessage();
            if ($content && isset($content['message'])) {
                $errorMessage = $content['message'];
            }
            
            log_message('error', "Error MercadoPago API - Status: {$statusCode}, Message: {$errorMessage}");
            if ($content) {
                log_message('error', "Content: " . json_encode($content));
            }
            
            // Mensaje más amigable para el usuario
            if ($statusCode == 403) {
                return $this->fail('Error de autenticación con Mercado Pago. Verifique las credenciales configuradas.', 403);
            }
            
            return $this->failServerError('Error al crear preferencia de MP: ' . $errorMessage);
        } catch (\Exception $e) {
            log_message('error', 'Error general: ' . $e->getMessage());
            log_message('error', "File: " . $e->getFile() . ", Line: " . $e->getLine());
            return $this->failServerError('Error al crear preferencia de MP: ' . $e->getMessage());
        }
    }

    /**
     * Crear suscripción recurrente (Preapproval) en Mercado Pago
     * POST /api/payment/create-subscription
     * Body: { "plan_id": 1 }
     */
    public function createSubscription()
    {
        $rules = [
            'plan_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Datos inválidos', 400, $this->validator->getErrors());
        }

        $userId = $this->request->userId ?? session()->get('user_id');
        if (!$userId) {
            return $this->jsonError('Usuario no autenticado', 401);
        }

        // Email del pagador (comprador): requerido por Preapproval
        // En sandbox, conviene usar el EMAIL del comprador de prueba de MP.
        $userEmail = $this->request->userEmail ?? session()->get('user_email');
        if (!$userEmail || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            // Intentar obtenerlo de DB
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            $userEmail = $user['email'] ?? null;
        }

        if (!$userEmail || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('Email del usuario inválido o no disponible para crear suscripción', 400);
        }

        $planId = (int) $this->request->getVar('plan_id');
        $plan = $this->subscriptionModel->find($planId);
        if (!$plan) {
            return $this->jsonError('Plan no encontrado', 404);
        }

        // Mapear frecuencia interna a auto_recurring (Mercado Pago)
        $freqMonths = 1;
        $frequency = $plan['frequency'] ?? 'mensual';
        switch ($frequency) {
            case 'trimestral':
                $freqMonths = 3;
                break;
            case 'semestral':
                $freqMonths = 6;
                break;
            case 'anual':
                $freqMonths = 12;
                break;
            case 'mensual':
            default:
                $freqMonths = 1;
                break;
        }

        $amount = (float) ($plan['price'] ?? 0);
        if ($amount <= 0) {
            return $this->jsonError('El plan tiene un precio inválido para suscripción', 400);
        }

        try {
            $client = new PreApprovalClient();

            // "Sandbox" real:
            // - tokens TEST-... (sandbox clásico) -> checkout en sandbox.mercadopago.com.ar
            // - o credenciales APP_USR... pero asociadas a una cuenta de prueba (panel MP lo llama "credenciales de producción de cuenta de prueba")
            //   -> checkout suele ser en www.mercadopago.com.ar pero operando como test user
            $me = $this->mpGetMe();
            $isSandboxToken = (strpos(MercadoPagoConfig::getAccessToken(), 'TEST-') === 0);
            $isTestAccount = $this->isTestAccount($me);
            $isSandbox = $isSandboxToken || $isTestAccount;

            // Permitir sobreescribir payer_email en sandbox (para usar comprador de prueba)
            // 1) Por .env (recomendado)
            $sandboxPayerEmail = $_ENV['MERCADOPAGO_SANDBOX_PAYER_EMAIL'] ?? getenv('MERCADOPAGO_SANDBOX_PAYER_EMAIL') ?? null;
            // 1.05) Alternativa: user_id del comprador test (se convierte a test_user_<id>@testuser.com)
            $sandboxPayerUserId = $_ENV['MERCADOPAGO_SANDBOX_PAYER_USER_ID'] ?? getenv('MERCADOPAGO_SANDBOX_PAYER_USER_ID') ?? null;
            // 1.1) Alternativa: username del test user (se convierte a email @testuser.com)
            $sandboxPayerUsername = $_ENV['MERCADOPAGO_SANDBOX_PAYER_USERNAME'] ?? getenv('MERCADOPAGO_SANDBOX_PAYER_USERNAME') ?? null;
            // 2) Por request (solo útil en dev)
            $overridePayerEmail = $this->request->getVar('payer_email');

            $payerEmail = $userEmail;
            if ($isSandbox) {
                if ($sandboxPayerEmail && filter_var($sandboxPayerEmail, FILTER_VALIDATE_EMAIL)) {
                    $payerEmail = $sandboxPayerEmail;
                } elseif ($sandboxPayerUserId && ctype_digit((string) $sandboxPayerUserId)) {
                    $candidate = 'test_user_' . (string) $sandboxPayerUserId . '@testuser.com';
                    if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                        $payerEmail = $candidate;
                    }
                } elseif ($sandboxPayerUsername && is_string($sandboxPayerUsername) && strlen(trim($sandboxPayerUsername)) > 0) {
                    // Mercado Pago test users suelen venir como nickname "TESTUSER<digits>"
                    // y el email suele ser "test_user_<digits>@testuser.com" (ver users/me).
                    $u = trim($sandboxPayerUsername);
                    if (preg_match('/^TESTUSER(\d+)$/i', $u, $m)) {
                        $candidate = 'test_user_' . $m[1] . '@testuser.com';
                    } else {
                        // fallback anterior
                        $candidate = $u . '@testuser.com';
                    }
                    if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                        $payerEmail = $candidate;
                    }
                } elseif ($overridePayerEmail && filter_var($overridePayerEmail, FILTER_VALIDATE_EMAIL)) {
                    $payerEmail = $overridePayerEmail;
                }
            }

            // Guard rail: Mercado Pago no permite mezclar comprador test (@testuser.com) con vendedor real.
            // Con nuestras credenciales TEST actuales, el vendedor sigue siendo tu cuenta real.
            // Si el payerEmail es testuser.com, devolvemos un error claro antes de llamar a MP.
            if ($isSandbox && is_string($payerEmail) && str_ends_with(strtolower($payerEmail), '@testuser.com')) {
                $collectorEmail = $me['email'] ?? null;
                $collectorNickname = $me['nickname'] ?? null;
                $collectorIsTest = $this->isTestAccount($me);

                if (!$collectorIsTest) {
                    return $this->jsonError(
                        'Sandbox Preapproval: no se puede mezclar vendedor real (' . ($collectorEmail ?: ($collectorNickname ?: 'N/A')) . ') con comprador de prueba (@testuser.com). ' .
                        'Para que funcione, usá credenciales productivas (APP_USR/Access Token production) y un comprador real, ' .
                        'o conseguí un Access Token asociado a un VENDEDOR de prueba.',
                        400
                    );
                }
            }

            // URL pública (https) para retorno desde Mercado Pago (NO puede ser localhost)
            $publicBaseUrl = $_ENV['MERCADOPAGO_PUBLIC_BASE_URL'] ?? getenv('MERCADOPAGO_PUBLIC_BASE_URL') ?? null;
            if (!$publicBaseUrl) {
                return $this->jsonError('Falta configurar MERCADOPAGO_PUBLIC_BASE_URL (tu URL pública, ej: https://xxxx.ngrok-free.dev)', 400);
            }
            $publicBaseUrl = rtrim($publicBaseUrl, '/');
            if (stripos($publicBaseUrl, 'http://') === 0) {
                return $this->jsonError('MERCADOPAGO_PUBLIC_BASE_URL debe ser https (Mercado Pago rechaza http/localhost)', 400);
            }

            // Frontend (puede ser localhost)
            $frontendBaseUrl = $_ENV['FRONTEND_BASE_URL'] ?? getenv('FRONTEND_BASE_URL') ?? "http://localhost:5173";
            $frontendBaseUrl = rtrim($frontendBaseUrl, '/');
            $externalRef = sprintf("%d|%d|%d", $userId, $planId, time());

            // start_date debe ser ISO 8601 en UTC (Mercado Pago es estricto con el formato)
            // Usamos milisegundos y sufijo Z.
            $startDate = gmdate('Y-m-d\\TH:i:s.000\\Z', time() + 300);

            // Mercado Pago vuelve a esta URL (debe ser pública)
            $backUrl = "{$publicBaseUrl}/api/payment/preapproval-return";

            $request = [
                "reason" => "Suscripción: " . ($plan['name'] ?? 'Plan'),
                "external_reference" => $externalRef,
                "payer_email" => $payerEmail,
                "back_url" => $backUrl,
                "auto_recurring" => [
                    "frequency" => $freqMonths,
                    "frequency_type" => "months",
                    "transaction_amount" => $amount,
                    "currency_id" => "ARS",
                    "start_date" => $startDate,
                ],
            ];

            log_message('info', '=== CREANDO SUSCRIPCION (PREAPPROVAL) MP ===');
            log_message('info', 'User ID: ' . $userId . ' UserEmail: ' . $userEmail . ' PayerEmail: ' . $payerEmail . ' Plan: ' . $planId);
            log_message('info', 'Request preapproval: ' . json_encode($request, JSON_PRETTY_PRINT));

            $preapproval = $client->create($request);

            $initPointRaw = $preapproval->init_point ?? null;
            $initPoint = null;

            // A veces el create() devuelve init_point vacío; reconsultar por ID para obtenerlo
            if (!$initPointRaw && !empty($preapproval->id)) {
                try {
                    $fresh = $client->get((string) $preapproval->id);
                    $initPointRaw = $fresh->init_point ?? $initPointRaw;
                } catch (\Exception $e) {
                    // ignorar, seguimos con fallback si hace falta
                    log_message('warning', 'No se pudo reconsultar preapproval para init_point: ' . $e->getMessage());
                }
            }

            // IMPORTANTE: evitar mezcla de ambientes.
            // Solo forzamos sandbox.mercadopago.com.ar cuando el token es TEST-.
            // Para "cuentas de prueba" (token APP_USR de test user), dejamos el dominio www que devuelve MP.
            if (!empty($preapproval->id)) {
                if ($isSandboxToken) {
                    $initPoint = "https://sandbox.mercadopago.com.ar/subscriptions/checkout?preapproval_id=" . $preapproval->id;
                } else {
                    $initPoint = $initPointRaw ?: ("https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_id=" . $preapproval->id);
                }
            } else {
                $initPoint = $initPointRaw;
            }

            log_message('info', 'Preapproval creado. ID: ' . ($preapproval->id ?? 'N/A') . ' InitPoint: ' . ($initPoint ?? 'N/A'));

            return $this->respond([
                'success' => true,
                'preapproval_id' => $preapproval->id ?? null,
                'init_point' => $initPoint,
                'init_point_raw' => $initPointRaw,
                // para debug/UX, devolvemos a dónde volverá MP
                'back_url' => $backUrl,
                'frontend_url' => "{$frontendBaseUrl}/dashboard/pagos/exito",
            ]);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse ? $apiResponse->getStatusCode() : 'N/A';
            $content = $apiResponse ? $apiResponse->getContent() : null;
            log_message('error', "Error MP Preapproval - Status: {$statusCode} Content: " . json_encode($content));
            // Propagar mensaje útil (ej: back_url inválida)
            $msg = $content['message'] ?? 'Error al crear suscripción en Mercado Pago';
            $http = is_numeric($statusCode) ? (int) $statusCode : 500;
            return $this->jsonError($msg, $http >= 400 && $http <= 599 ? $http : 500);
        } catch (\Exception $e) {
            log_message('error', 'Error creando preapproval: ' . $e->getMessage());
            log_message('error', "File: " . $e->getFile() . ", Line: " . $e->getLine());
            return $this->jsonError('Error al crear suscripción en Mercado Pago: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancelar suscripción activa del usuario (Mercado Pago Preapproval).
     * POST /api/payment/cancel-subscription
     */
    public function cancelSubscription()
    {
        $userId = $this->request->userId ?? session()->get('user_id');
        if (!$userId) {
            return $this->jsonError('Usuario no autenticado', 401);
        }

        // Buscar suscripción activa del usuario
        $sub = $this->userSubscriptionModel->getActiveByUser($userId);
        if (!$sub) {
            return $this->jsonError('No tenés una suscripción activa para cancelar', 404);
        }

        if (($sub['payment_method'] ?? '') !== 'mercadopago') {
            return $this->jsonError('La suscripción activa no es de Mercado Pago', 400);
        }

        $preapprovalId = (string) ($sub['external_payment_id'] ?? '');
        if (!$preapprovalId) {
            return $this->jsonError('No se encontró el ID externo de la suscripción (preapproval_id)', 400);
        }

        try {
            $client = new PreApprovalClient();
            // Cancelar en Mercado Pago
            $updated = $client->update($preapprovalId, ['status' => 'cancelled']);

            // Reflejar en DB
            $this->userSubscriptionModel->cancelSubscription($sub['id']);

            // Reflejar estado en users (para panel admin)
            try {
                $userModel = new UserModel();
                $userModel->update($userId, ['estado' => 'Cancelada']);
            } catch (\Throwable $t) {
                log_message('warning', 'No se pudo actualizar users.estado a Cancelada para user ' . $userId . ': ' . $t->getMessage());
            }

            log_message('info', "Suscripción cancelada por usuario {$userId}. Preapproval={$preapprovalId}");

            return $this->respond([
                'success' => true,
                'message' => 'Suscripción cancelada exitosamente',
                'data' => [
                    'preapproval_id' => $preapprovalId,
                    'status' => (string) ($updated->status ?? 'cancelled'),
                ],
            ]);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse ? $apiResponse->getStatusCode() : 'N/A';
            $content = $apiResponse ? $apiResponse->getContent() : null;
            log_message('error', "Error MP cancelSubscription - Status: {$statusCode} Content: " . json_encode($content));
            $msg = $content['message'] ?? 'Error al cancelar la suscripción en Mercado Pago';
            $http = is_numeric($statusCode) ? (int) $statusCode : 500;
            return $this->jsonError($msg, $http >= 400 && $http <= 599 ? $http : 500);
        } catch (\Throwable $t) {
            log_message('error', 'Error cancelando suscripción: ' . $t->getMessage());
            return $this->jsonError('Error al cancelar suscripción: ' . $t->getMessage(), 500);
        }
    }

    /**
     * Retorno público desde Mercado Pago (Preapproval).
     * MP vuelve a esta URL (debe ser pública) y nosotros redirigimos al frontend.
     * GET /api/payment/preapproval-return
     */
    public function preapprovalReturn()
    {
        $frontendBaseUrl = $_ENV['FRONTEND_BASE_URL'] ?? getenv('FRONTEND_BASE_URL') ?? "http://localhost:5173";
        $frontendBaseUrl = rtrim($frontendBaseUrl, '/');

        // MP suele mandar preapproval_id por querystring
        $preapprovalId = $this->request->getVar('preapproval_id') ?? $this->request->getVar('id');
        $status = $this->request->getVar('status') ?? 'ok';

        // Intentar activar/inactivar la suscripción acá también (por si el webhook tarda o no llega)
        // Esto mejora UX: al volver al sitio, el usuario ya ve su suscripción activa.
        if (!empty($preapprovalId)) {
            try {
                $client = new PreApprovalClient();
                $preapproval = $client->get((string) $preapprovalId);
                $paStatus = strtolower((string) ($preapproval->status ?? ''));

                if ($preapproval && !empty($preapproval->external_reference)) {
                    $parts = explode('|', (string) $preapproval->external_reference);
                    if (count($parts) >= 2) {
                        $userId = $parts[0];
                        $planId = $parts[1];
                        $nextPayment = $preapproval->next_payment_date ?? null;

                        if (in_array($paStatus, ['authorized', 'active'], true)) {
                            $this->activateSubscriptionFromPreapproval($userId, $planId, $preapproval, $nextPayment);
                        } elseif (in_array($paStatus, ['cancelled', 'paused'], true)) {
                            $this->markSubscriptionInactiveFromPreapproval($preapproval);
                        }
                    }
                }

                log_message('info', "preapprovalReturn OK preapproval_id={$preapprovalId} status={$paStatus}");
            } catch (MPApiException $e) {
                $apiResponse = $e->getApiResponse();
                $statusCode = $apiResponse ? $apiResponse->getStatusCode() : 'N/A';
                $content = $apiResponse ? $apiResponse->getContent() : null;
                log_message('error', "preapprovalReturn: error MP get preapproval_id={$preapprovalId} Status={$statusCode} Content=" . json_encode($content));
            } catch (\Throwable $t) {
                log_message('error', "preapprovalReturn: error general preapproval_id={$preapprovalId} " . $t->getMessage());
            }
        } else {
            log_message('warning', 'preapprovalReturn llamado sin preapproval_id');
        }

        $target = "{$frontendBaseUrl}/dashboard/pagos/exito";
        $qs = http_build_query([
            'preapproval_id' => $preapprovalId,
            'status' => $status,
        ]);

        $redirectUrl = $target . ($qs ? ('?' . $qs) : '');

        // Redirección explícita (evita que CI “normalice” al baseURL:8080)
        return $this->response
            ->setStatusCode(302)
            ->setHeader('Location', $redirectUrl);
    }

    /**
     * Webhook para recibir notificaciones de Mercado Pago
     * POST/GET /api/payment/webhook
     * Siempre responde 200 (MP reintenta si recibe error).
     */
    public function webhook()
    {
        try {
            $payload = [];
            try {
                $payload = $this->request->getJSON(true) ?? [];
            } catch (\Throwable $e) {
                log_message('warning', 'Webhook MP: error parseando JSON - ' . $e->getMessage());
            }

            $type = $this->request->getVar('type') ?? ($payload['type'] ?? null);
            $topic = $this->request->getVar('topic') ?? ($payload['topic'] ?? null);
            $entity = $this->request->getVar('entity') ?? ($payload['entity'] ?? null);

            $id =
                $this->request->getVar('data.id')
                ?? $this->request->getVar('id')
                ?? ($payload['data']['id'] ?? null)
                ?? ($payload['id'] ?? null);

            log_message('info', "Webhook MP recibido: Topic: {$topic} Type: {$type} Entity: {$entity} ID: {$id}");

            // Test del panel MP o sin ID → 200 OK
            if (empty($id) || (string) $id === '123456') {
                return $this->respond(['status' => 'OK'], 200);
            }

            if ($topic == 'payment' || $type == 'payment') {
                try {
                    $client = new PaymentClient();
                    $payment = $client->get($id);
                    if ($payment && $payment->status == 'approved') {
                        $externalRef = $payment->external_reference;
                        $parts = explode('|', $externalRef ?? '');
                        if (count($parts) >= 2) {
                            $this->activateSubscription($parts[0], $parts[1], $payment);
                        }
                    }
                } catch (MPApiException $e) {
                    $apiResponse = $e->getApiResponse();
                    $statusCode = $apiResponse ? $apiResponse->getStatusCode() : 'N/A';
                    log_message('error', 'Webhook MP payment: ' . $e->getMessage() . ' Status: ' . $statusCode);
                    return $this->respond(['status' => 'OK'], 200);
                } catch (\Exception $e) {
                    log_message('error', 'Webhook MP payment: ' . $e->getMessage());
                    return $this->respond(['status' => 'OK'], 200);
                }
            }

            // Webhook de suscripciones (Preapproval) - MP envía type "subscription_preapproval" o entity "preapproval"
            $isPreapproval = in_array($topic, ['preapproval'], true)
                || in_array($type, ['preapproval', 'subscription_preapproval'], true)
                || $entity === 'preapproval';
            if ($isPreapproval) {
                try {
                    $client = new PreApprovalClient();
                    $preapproval = $client->get((string) $id);
                    if ($preapproval && !empty($preapproval->external_reference)) {
                        $parts = explode('|', (string) $preapproval->external_reference);
                        if (count($parts) >= 2) {
                            $userId = $parts[0];
                            $planId = $parts[1];
                            $status = strtolower((string) ($preapproval->status ?? ''));
                            $nextPayment = $preapproval->next_payment_date ?? null;
                            if (in_array($status, ['authorized', 'active'], true)) {
                                $this->activateSubscriptionFromPreapproval($userId, $planId, $preapproval, $nextPayment);
                            } elseif (in_array($status, ['cancelled', 'paused'], true)) {
                                $this->markSubscriptionInactiveFromPreapproval($preapproval);
                            }
                        }
                    }
                } catch (MPApiException $e) {
                    $apiResponse = $e->getApiResponse();
                    $statusCode = $apiResponse ? $apiResponse->getStatusCode() : 'N/A';
                    log_message('error', 'Webhook MP preapproval: ' . $e->getMessage() . ' Status: ' . $statusCode);
                    return $this->respond(['status' => 'OK'], 200);
                } catch (\Exception $e) {
                    log_message('error', 'Webhook MP preapproval: ' . $e->getMessage());
                    return $this->respond(['status' => 'OK'], 200);
                }
            }

            return $this->respond(['status' => 'OK'], 200);
        } catch (\Throwable $e) {
            log_message('error', 'Webhook MP error inesperado: ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            return $this->respond(['status' => 'OK'], 200);
        }
    }

    private function activateSubscriptionFromPreapproval($userId, $planId, $preapproval, $nextPaymentDate = null)
    {
        // Desactivar anteriores
        $existing = $this->userSubscriptionModel
            ->where('user_id', $userId)
            ->where('status', 'activa')
            ->findAll();

        foreach ($existing as $sub) {
            $this->userSubscriptionModel->update($sub['id'], ['status' => 'cancelada']);
        }

        $startDate = date('Y-m-d');
        $nextBillingDate = null;
        if (!empty($nextPaymentDate)) {
            // next_payment_date viene ISO, guardamos YYYY-MM-DD
            $nextBillingDate = substr((string) $nextPaymentDate, 0, 10);
        }

        if (!$nextBillingDate) {
            // Fallback según frecuencia
            $plan = $this->subscriptionModel->find($planId);
            $nextBillingDate = date('Y-m-d', strtotime('+1 month'));
            if ($plan && ($plan['frequency'] ?? '') === 'trimestral') {
                $nextBillingDate = date('Y-m-d', strtotime('+3 months'));
            } elseif ($plan && ($plan['frequency'] ?? '') === 'semestral') {
                $nextBillingDate = date('Y-m-d', strtotime('+6 months'));
            } elseif ($plan && ($plan['frequency'] ?? '') === 'anual') {
                $nextBillingDate = date('Y-m-d', strtotime('+1 year'));
            }
        }

        $data = [
            'user_id' => $userId,
            'subscription_id' => $planId,
            'status' => 'activa',
            'start_date' => $startDate,
            'next_billing_date' => $nextBillingDate,
            'auto_renew' => 1,
            'payment_method' => 'mercadopago',
            // Guardamos el preapproval_id
            'external_payment_id' => (string) ($preapproval->id ?? ''),
            'notes' => 'Suscripción autorizada vía Mercado Pago. Preapproval ID: ' . ($preapproval->id ?? 'N/A'),
        ];

        $this->userSubscriptionModel->insert($data);
        // Reflejar estado en tabla users para el panel admin (ClientesController lee users.estado)
        try {
            $userModel = new UserModel();
            $userModel->update($userId, ['estado' => 'Activo']);
        } catch (\Throwable $t) {
            // No bloquear por esto; solo loguear
            log_message('warning', 'No se pudo actualizar users.estado a Activo para user ' . $userId . ': ' . $t->getMessage());
        }
        log_message('info', "Suscripción (preapproval) activada para Usuario {$userId}, Plan {$planId}");
    }

    private function markSubscriptionInactiveFromPreapproval($preapproval)
    {
        $preapprovalId = (string) ($preapproval->id ?? '');
        if (!$preapprovalId) return;

        // Marcar como cancelada/pausada según status
        $status = strtolower((string) ($preapproval->status ?? 'cancelled'));
        $newStatus = ($status === 'paused') ? 'pausada' : 'cancelada';

        $subs = $this->userSubscriptionModel
            ->where('payment_method', 'mercadopago')
            ->where('external_payment_id', $preapprovalId)
            ->findAll();

        foreach ($subs as $sub) {
            $this->userSubscriptionModel->update($sub['id'], ['status' => $newStatus]);
        }
        // También reflejar estado en users (Cancelada si canceló, Pausada si pausó)
        try {
            if (!empty($subs[0]['user_id'])) {
                $userId = $subs[0]['user_id'];
                $userModel = new UserModel();
                $userEstado = ($status === 'paused') ? 'Pausada' : 'Cancelada';
                $userModel->update($userId, ['estado' => $userEstado]);
            }
        } catch (\Throwable $t) {
            log_message('warning', 'No se pudo actualizar users.estado para preapproval ' . $preapprovalId . ': ' . $t->getMessage());
        }
        log_message('info', "Suscripción marcada {$newStatus} por webhook preapproval {$preapprovalId}");
    }

    private function activateSubscription($userId, $planId, $payment)
    {
        // Verificar si ya tiene suscripción activa para no duplicar
        // Ojo: Podría ser una renovación.
        // Por simplicidad: Desactivar anteriores y crear nueva.
        
        // 1. Buscar suscripciones activas del usuario
        $existing = $this->userSubscriptionModel
            ->where('user_id', $userId)
            ->where('status', 'activa')
            ->findAll();

        foreach ($existing as $sub) {
            // Cancelar o marcar como finalizada la anterior
            $this->userSubscriptionModel->update($sub['id'], ['status' => 'cancelada']);
        }

        // 2. Calcular fechas
        $plan = $this->subscriptionModel->find($planId);
        $startDate = date('Y-m-d');
        $nextBillingDate = date('Y-m-d', strtotime('+1 month')); // Default mensual
        
        if ($plan['frequency'] == 'trimestral') {
            $nextBillingDate = date('Y-m-d', strtotime('+3 months'));
        } elseif ($plan['frequency'] == 'anual') {
            $nextBillingDate = date('Y-m-d', strtotime('+1 year'));
        }

        // 3. Crear nueva suscripción
        $data = [
            'user_id' => $userId,
            'subscription_id' => $planId,
            'status' => 'activa',
            'start_date' => $startDate,
            'next_billing_date' => $nextBillingDate,
            'auto_renew' => 1,
            'payment_method' => 'mercadopago',
            'external_payment_id' => (string) $payment->id,
            'notes' => 'Pago aprobado vía Mercado Pago. Payment ID: ' . $payment->id
        ];

        $this->userSubscriptionModel->insert($data);
        log_message('info', "Suscripción activada para Usuario $userId, Plan $planId");
    }
}
