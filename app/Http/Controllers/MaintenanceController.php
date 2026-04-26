<?php

namespace App\Http\Controllers;

use App\Http\Requests\Maintenance\IndexRequest;
use App\Http\Requests\Maintenance\StoreRequest;
use App\Http\Requests\Maintenance\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\Maintenance;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexRequest $request)
    {
        $validated = $request->validated();

        $query = Maintenance::with([
            'vehicle:id,plate,brand,model,year,vehicle_type,image_path',
        ])
            ->latest()
            ->when($request->vehicle_id, fn($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->type,   fn($q) => $q->where('type',   $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed());

        $maintenance = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de mantenimientos seleccionados:',
            'data' => $maintenance,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $maintenance = Maintenance::create($request->validated());

        return response()->json([
            'message' => 'Mantenimiento creado correctamente.',
            'data' => $maintenance,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Maintenance $maintenance)
    {
        return response()->json([
            'message' => 'Mantenimiento seleccionado:',
            'data' => $maintenance->load('vehicle:id,plate,brand,model,year,image_path'),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Maintenance $maintenance)
    {
        $maintenance->update($request->validated());

        return response()->json([
            'message' => 'Mantenimiento actualizado correctamente.',
            'data' => $maintenance->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();

        return response()->json([
            'message' => 'Mantenimiento desactivado correctamente.',
        ], 200);
    }

    public function restore(Maintenance $maintenance)
    {
        if (!$maintenance->trashed()) {
            return response()->json([
                'message' => 'No se pudo reactivar el mantenimiento.',
            ], 404);
        }

        $maintenance->restore();

        return response()->json([
            'message' => 'Mantenimiento reactivado correctamente.',
            'data' => $maintenance->fresh(),
        ], 200);
    }
}
