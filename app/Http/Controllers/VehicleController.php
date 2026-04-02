<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
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
            'trashed' => ['nullable', 'in:only,with'],
        ]);

        $query = Vehicle::latest()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed());

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
        //
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
        //
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
        //
        $vehicle->update($request->validated());

        return response()->json([
            'message' => 'Vehiculo actualizado correctamente.',
            'data' => $vehicle->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        //
        $vehicle->delete();

        return response()->json([
            'message' => 'Vehiculo desactivado correctamente.',
            'data' => $vehicle->fresh(),
        ], 200);
    }

    public function restore($id)
    {
        //
        $vehicle = Vehicle::onlyTrashed()->find($id);

        if (!$vehicle) {
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
