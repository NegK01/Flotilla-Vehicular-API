<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequestRequest;
use App\Http\Requests\UpdateVehicleRequestRequest;
use Illuminate\Http\Request;
use App\Models\VehicleRequest;

class VehicleRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $request->validate([
            'type' => [
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

        $query = VehicleRequest::with([
            'driver:id,full_name',
            'vehicle:id,plate,brand,model,year,vehicle_type',
        ])
            ->latest()
            ->when($request->type,   fn($q) => $q->where('type',   $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed());

        $vehicleRequests = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de solicitudes seleccionadas:',
            'data' => $vehicleRequests,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequestRequest $request)
    {
        //
        $vehicleRequest = VehicleRequest::create($request->validated());

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
        //
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
        //
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
        //
        $vehicleRequest->delete();

        return response()->json([
            'message' => 'Solicitud desactivada correctamente.',
            'data' => $vehicleRequest->fresh(),
        ], 200);
    }

    public function restore($id)
    {
        //
        $trip = VehicleRequest::onlyTrashed()->find($id);

        if (!$trip) {
            return response()->json([
                'message' => 'No se pudo reactivar la solicitud.',
            ], 404);
        }

        $trip->restore();

        return response()->json([
            'message' => 'Solicitud reactivada correctamente.',
            'data' => $trip->fresh(),
        ], 200);
    }
}
