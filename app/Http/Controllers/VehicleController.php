<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     * Soporta filtro por status y por disponibilidad en rango de fecha/hora (RF-05.2).
     * El filtro de rango usa fn_is_vehicle_available() a nivel de consulta.
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => [
                'nullable',
                'string',
                'in:' . implode(',', [
                    Vehicle::STATUS_AVAILABLE,
                    Vehicle::STATUS_RESERVED,
                    Vehicle::STATUS_MAINTENANCE,
                    Vehicle::STATUS_OUT_OF_SERVICE,
                ]),
            ],
            'trashed'  => ['nullable', 'in:only,with'],
            // Filtro por disponibilidad real en rango (RF-05.2)
            'start_at' => ['nullable', 'date', 'required_with:end_at'],
            'end_at'   => ['nullable', 'date', 'after:start_at', 'required_with:start_at'],
        ]);

        $query = Vehicle::latest()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed());

        // Si se pasa rango, filtrar por disponibilidad real usando fn_is_vehicle_available
        if ($request->filled('start_at') && $request->filled('end_at')) {
            $startAt = $request->input('start_at');
            $endAt   = $request->input('end_at');

            $query->whereRaw(
                "fn_is_vehicle_available(id, ?::timestamp, ?::timestamp) = TRUE",
                [$startAt, $endAt]
            );
        }

        $vehicles = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de vehiculos seleccionados:',
            'data' => $vehicles,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request)
    {
        $vehicle = Vehicle::create($request->validated());

        return response()->json([
            'message' => 'Vehiculo creado correctamente.',
            'data' => $vehicle,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        return response()->json([
            'message' => 'Vehiculo seleccionado:',
            'data' => $vehicle,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $vehicle->update($request->validated());

        return response()->json([
            'message' => 'Vehiculo actualizado correctamente.',
            'data' => $vehicle->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage (borrado lógico).
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json([
            'message' => 'Vehiculo desactivado correctamente.',
        ], 200);
    }

    public function restore(Vehicle $vehicle)
    {
        if (!$vehicle->trashed()) {
            return response()->json([
                'message' => 'No se pudo reactivar el vehiculo.',
            ], 404);
        }

        $vehicle->restore();

        return response()->json([
            'message' => 'Vehiculo reactivado correctamente.',
            'data' => $vehicle->fresh(),
        ], 200);
    }
}
