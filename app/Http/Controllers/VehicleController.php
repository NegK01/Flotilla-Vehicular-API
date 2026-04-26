<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\IndexRequest;
use App\Http\Requests\Vehicle\StoreRequest;
use App\Http\Requests\Vehicle\UpdateRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     * Soporta filtro por status y por disponibilidad en rango de fecha/hora (RF-05.2).
     * El filtro de rango usa fn_is_vehicle_available() a nivel de consulta.
     */
    public function index(IndexRequest $request)
    {
        $query = Vehicle::latest()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed());

        // Si se pasa rango, filtrar por disponibilidad real usando fn_is_vehicle_available
        if ($request->start_at && $request->end_at) {
            $startAt = $request->input('start_at');
            $endAt   = $request->input('end_at');

            $query->whereRaw(
                "fn_is_vehicle_available(id, ?::timestamp, ?::timestamp) = TRUE",
                [$startAt, $endAt]
            );
        }

        $vehicles = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de vehículos seleccionados:',
            'data' => $vehicles,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('images/vehicles', 'public');
        }

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'message' => 'Vehículo creado correctamente.',
            'data' => $vehicle,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        return response()->json([
            'message' => 'Vehículo seleccionado:',
            'data' => $vehicle,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Vehicle $vehicle)
    {
        $validated = $request->validated();

        if ($request->hasFile('image_path')) {
            // Eliminar imagen anterior si existe
            if ($vehicle->image_path) {
                Storage::disk('public')->delete($vehicle->image_path);
            }
            $validated['image_path'] = $request->file('image_path')->store('images/vehicles', 'public');
        }

        $vehicle->update($validated);

        return response()->json([
            'message' => 'Vehículo actualizado correctamente.',
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
            'message' => 'Vehículo desactivado correctamente.',
        ], 200);
    }

    public function restore(Vehicle $vehicle)
    {
        if (!$vehicle->trashed()) {
            return response()->json([
                'message' => 'No se pudo reactivar el vehículo.',
            ], 404);
        }

        // Prevenir duplicidad de placa si ya existe un vehículo activo con la misma
        if (Vehicle::where('plate', $vehicle->plate)->exists()) {
            return response()->json([
                'message' => 'No se puede reactivar el vehículo porque la placa ya está registrada en otro vehículo activo.',
            ], 409);
        }

        $vehicle->restore();

        return response()->json([
            'message' => 'Vehículo reactivado correctamente.',
            'data' => $vehicle->fresh(),
        ], 200);
    }
}
