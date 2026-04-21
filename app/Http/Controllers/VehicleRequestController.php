<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectVehicleRequestRequest;
use App\Http\Requests\DirectAssignmentRequest;
use App\Http\Requests\StoreVehicleRequestRequest;
use App\Http\Requests\UpdateVehicleRequestRequest;
use App\Models\VehicleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'request_type' => [
                'nullable',
                'string',
                'in:' . implode(',', [
                    VehicleRequest::TYPE_DRIVER_REQUEST,
                    VehicleRequest::TYPE_DIRECT_ASSIGNMENT,
                ]),
            ],
            'status' => [
                'nullable',
                'string',
                'in:' . implode(',', [
                    VehicleRequest::STATUS_PENDING,
                    VehicleRequest::STATUS_APPROVED,
                    VehicleRequest::STATUS_REJECTED,
                    VehicleRequest::STATUS_CANCELLED,
                ]),
            ],
            'trashed' => ['nullable', 'in:only,with'],
        ]);

        $user = $request->user();

        $query = VehicleRequest::with([
            'driver:id,full_name',
            'vehicle:id,plate,brand,model,year,vehicle_type',
        ])
            ->latest()
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed())
            ->when($request->request_type,       fn($q) => $q->where('request_type', $request->request_type))
            ->when($request->status,             fn($q) => $q->where('status', $request->status))
            ->when($user->role_id === 3,         fn($q) => $q->where('driver_id', $user->id));

        $vehicleRequests = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de solicitudes seleccionadas:',
            'data' => $vehicleRequests,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     * Fix IDOR: para choferes, driver_id se fuerza desde la sesión.
     */
    public function store(StoreVehicleRequestRequest $request)
    {
        $validated = $request->validated();

        // El driver_id siempre será el id del chofer logueado 
        if ($request->user()->role_id === 3) {
            $validated['driver_id'] = $request->user()->id;
        }

        $validated['status']       = VehicleRequest::STATUS_PENDING;
        $validated['request_type'] = VehicleRequest::TYPE_DRIVER_REQUEST;

        $vehicleRequest = VehicleRequest::create($validated);

        return response()->json([
            'message' => 'Solicitud creada correctamente.',
            'data' => $vehicleRequest,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleRequest $vehicleRequest)
    {
        $vehicleRequest->load([
            'driver:id,full_name',
            'vehicle:id,plate,brand,model,year,vehicle_type',
        ]);

        return response()->json([
            'message' => 'Solicitud seleccionada:',
            'data' => $vehicleRequest,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequestRequest $request, VehicleRequest $vehicleRequest)
    {
        $vehicleRequest->update($request->validated());

        return response()->json([
            'message' => 'Solicitud actualizada correctamente.',
            'data' => $vehicleRequest->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleRequest $vehicleRequest)
    {
        $vehicleRequest->delete();

        return response()->json([
            'message' => 'Solicitud desactivada correctamente.',
        ], 200);
    }

    public function restore(VehicleRequest $vehicleRequest)
    {
        if (!$vehicleRequest->trashed()) {
            return response()->json([
                'message' => 'No se pudo reactivar la solicitud.',
            ], 404);
        }

        $vehicleRequest->restore();

        return response()->json([
            'message' => 'Solicitud reactivada correctamente.',
            'data' => $vehicleRequest->fresh(),
        ], 200);
    }

    /**
     * Aprueba una solicitud pendiente usando el procedure SQL
     * El procedure valida solapamiento y mantenimiento abierto
     * PATCH /vehicleRequests/{vehicleRequest}/approve
     */
    public function approve(Request $request, VehicleRequest $vehicleRequest)
    {
        try {
            DB::statement('CALL p_approve_vehicle_request(?, ?)', [
                $vehicleRequest->id,
                $request->user()->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No se pudo aprobar la solicitud.',
                'error'   => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Solicitud aprobada correctamente.',
            'data'    => $vehicleRequest->fresh()->load([
                'driver:id,full_name',
                'vehicle:id,plate,brand,model,year,vehicle_type',
            ]),
        ], 200);
    }

    /**
     * Rechaza una solicitud pendiente
     * PATCH /vehicleRequests/{vehicleRequest}/reject
     */
    public function reject(RejectVehicleRequestRequest $request, VehicleRequest $vehicleRequest)
    {
        if ($vehicleRequest->status !== VehicleRequest::STATUS_PENDING) {
            return response()->json([
                'message' => 'Solo se pueden rechazar solicitudes en estado pendiente.',
            ], 422);
        }

        $validated = $request->validated();

        $vehicleRequest->update([
            'status'      => VehicleRequest::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'observation' => $validated['observation'] ?? null,
        ]);

        return response()->json([
            'message' => 'Solicitud rechazada correctamente.',
            'data'    => $vehicleRequest->fresh()->load([
                'driver:id,full_name',
                'vehicle:id,plate,brand,model,year,vehicle_type',
            ]),
        ], 200);
    }

    /**
     * Cancelación por chofer, solo sus propias solicitudes, cambia estado a cancelled
     * El trigger 2 libera el vehículo automáticamente si no hay otras cosas vigentes
     * PATCH /vehicleRequests/{vehicleRequest}/cancel
     */
    public function cancel(Request $request, VehicleRequest $vehicleRequest)
    {
        // Solo el chofer puede cancelar solicitudes
        if ($request->user()->role_id !== 3) {
            return response()->json([
                'message' => 'Solo el chofer puede cancelar solicitudes.',
            ], 403);
        }

        // Solo puede cancelar sus propias solicitudes
        if ($vehicleRequest->driver_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No autorizado para cancelar esta solicitud.',
            ], 403);
        }

        if (!in_array($vehicleRequest->status, [
            VehicleRequest::STATUS_PENDING,
            VehicleRequest::STATUS_APPROVED,
        ], true)) {
            return response()->json([
                'message' => 'Solo se pueden cancelar solicitudes en estado pendiente o aprobado.',
            ], 422);
        }

        // El trigger 2 (fn_release_vehicle_on_cancellation) maneja la liberación del vehículo
        $vehicleRequest->update([
            'status' => VehicleRequest::STATUS_CANCELLED,
        ]);

        return response()->json([
            'message' => 'Solicitud cancelada correctamente.',
            'data'    => $vehicleRequest->fresh()->load([
                'driver:id,full_name',
                'vehicle:id,plate,brand,model,year,vehicle_type',
            ]),
        ], 200);
    }

    /**
     * Asignación directa por operador usando el procedure SQL
     * El procedure valida solapamiento y mantenimiento. La solicitud nace como approved
     * POST /vehicleRequests/directAssignment
     */
    public function directAssignment(DirectAssignmentRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::statement('CALL p_direct_assignment(?, ?, ?, ?, ?, ?)', [
                $validated['vehicle_id'],
                $validated['driver_id'],
                $validated['start_at'],
                $validated['end_at'],
                $request->user()->id,
                $validated['observation'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No se pudo realizar la asignación directa.',
                'error'   => $e->getMessage(),
            ], 422);
        }

        // Recuperar la solicitud recién creada por el procedure
        $vehicleRequest = VehicleRequest::where('vehicle_id', $validated['vehicle_id'])
            ->where('driver_id', $validated['driver_id'])
            ->where('request_type', VehicleRequest::TYPE_DIRECT_ASSIGNMENT)
            ->latest()
            ->first();

        return response()->json([
            'message' => 'Asignación directa realizada correctamente.',
            'data'    => $vehicleRequest?->load([
                'driver:id,full_name',
                'vehicle:id,plate,brand,model,year,vehicle_type',
            ]),
        ], 201);
    }
}
