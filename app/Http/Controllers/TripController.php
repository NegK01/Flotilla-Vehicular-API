<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $request->validate([
            'trashed' => ['nullable', 'in:only,with'],
        ]);

        $query = Trip::with([
            'driver:id,full_name',
            'vehicle:id,plate,brand,model,year,vehicle_type',
            'travelRoute:id,name,start_point,end_point',
        ])
            ->latest()
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed());

        $trips = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de viajes seleccionados:',
            'data' => $trips,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTripRequest $request)
    {
        //
        $trip = Trip::create($request->validated());

        return response()->json([
            'message' => 'Viaje creado correctamente.',
            'data' => $trip,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip)
    {
        //
        $trip->load([
            'driver:id,full_name',
            'vehicle:id,plate,brand,model,year,vehicle_type',
            'travelRoute:id,name,start_point,end_point',
        ]);

        return response()->json([
            'message' => 'Viaje seleccionado:',
            'data' => $trip,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTripRequest $request, Trip $trip)
    {
        //
        $trip->update($request->validated());

        return response()->json([
            'message' => 'Viaje actualizado correctamente.',
            'data' => $trip->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip)
    {
        //
        $trip->delete();

        return response()->json([
            'message' => 'Viaje desactivado correctamente.',
            'data' => $trip->fresh(),
        ], 200);
    }

    public function restore(Trip $trip)
    {
        //
        if (!$trip->trashed()) {
            return response()->json([
                'message' => 'No se pudo reactivar el viaje.',
            ], 404);
        }

        $trip->restore();

        return response()->json([
            'message' => 'Viaje reactivado correctamente.',
            'data' => $trip->fresh(),
        ], 200);
    }
}
