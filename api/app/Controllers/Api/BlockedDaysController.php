<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BlockedDayModel;

class BlockedDaysController extends ResourceController
{
    protected $format = 'json';
    protected $blockedDayModel;

    public function __construct()
    {
        $this->blockedDayModel = new BlockedDayModel();
    }

    /**
     * Listar días bloqueados en un rango.
     * GET blocked-days?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function index()
    {
        $from = $this->request->getGet('from');
        $to = $this->request->getGet('to');

        if (!$from || !$to) {
            return $this->respond([
                'success' => false,
                'message' => 'Parámetros from y to (YYYY-MM-DD) son requeridos',
            ], 400);
        }

        $rows = $this->blockedDayModel
            ->where('blocked_date >=', $from)
            ->where('blocked_date <=', $to)
            ->orderBy('blocked_date', 'ASC')
            ->findAll();

        return $this->respond([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * Crear un día bloqueado.
     * POST blocked-days
     * Body: { "blocked_date": "YYYY-MM-DD", "description": "Feriado" }
     */
    public function create()
    {
        $date = $this->request->getJSON(true)['blocked_date'] ?? $this->request->getPost('blocked_date');
        $description = $this->request->getJSON(true)['description'] ?? $this->request->getPost('description') ?? '';

        if (!$date) {
            return $this->respond([
                'success' => false,
                'message' => 'blocked_date (YYYY-MM-DD) es requerido',
            ], 400);
        }

        $date = date('Y-m-d', strtotime($date));

        $existing = $this->blockedDayModel->where('blocked_date', $date)->first();
        if ($existing) {
            return $this->respond([
                'success' => false,
                'message' => 'Ese día ya está bloqueado',
            ], 409);
        }

        $id = $this->blockedDayModel->insert([
            'blocked_date' => $date,
            'description' => $description,
        ]);

        if (!$id) {
            return $this->respond([
                'success' => false,
                'message' => 'Error al guardar el día bloqueado',
            ], 500);
        }

        $row = $this->blockedDayModel->find($id);
        return $this->respond([
            'success' => true,
            'data' => $row,
        ], 201);
    }

    /**
     * Eliminar un día bloqueado.
     * DELETE blocked-days/(:num)
     */
    public function delete($id = null)
    {
        $row = $this->blockedDayModel->find($id);
        if (!$row) {
            return $this->failNotFound('Día bloqueado no encontrado');
        }
        $this->blockedDayModel->delete($id);
        return $this->respond([
            'success' => true,
            'message' => 'Día desbloqueado',
        ]);
    }
}
