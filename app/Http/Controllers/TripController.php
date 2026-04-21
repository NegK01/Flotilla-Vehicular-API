<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Models\VehicleRequest;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'trashed' => ['nullable', 'in:only,with'],
        ]);

        $user = $request->user();

        $query = Trip::with([
            'driver:id,full_name',
            'vehicle:id,plate,brand,model,year,vehicle_type',
            'travelRoute:id,name,start_point,end_point',
        ])
            ->latest()
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed())
            ->when((int) $user->role_id === 3,   fn($q) => $q->where('driver_id', $user->id));

        $trips = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de viajes seleccionados:',
            'data' => $trips,
        ], 200);
    }

    /**
     * Para crear el viaje, la solicitud asociada debe estar en estado aprobado
     * Fix: driver_id y vehicle_id se derivan de la solicitud, no del payload
     */
    public function store(StoreTripRequest $request)
    {
        $validated = $request->validated();

        $vehicleRequest = VehicleRequest::find($validated['vehicle_request_id']);

        if ($vehicleRequest->status !== VehicleRequest::STATUS_APPROVED) {
            return response()->json([
                'message' => 'Solo se puede registrar viajes con solicitudes aprobadas.',
            ], 422);
        }

        // Driver y vehicle se toman de la solicitud aprobada — no del payload
        $validated['driver_id']  = $vehicleRequest->driver_id;
        $validated['vehicle_id'] = $vehicleRequest->vehicle_id;

        $trip = Trip::create($validated);

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
     * Al actualizar el vehicle_request_id volveremos a derivar los campos
     * Fix: driver_id y vehicle_id se derivan de la solicitud, no del payload
     */
    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $validated = $request->validated();

        // Si se cambia la vehicle_request_id, re-derivar driver_id y vehicle_id
        if (isset($validated['vehicle_request_id'])) {
            $vehicleRequest = VehicleRequest::find($validated['vehicle_request_id']);

            if ($vehicleRequest->status !== VehicleRequest::STATUS_APPROVED) {
                return response()->json([
                    'message' => 'Solo se puede asociar solicitudes aprobadas al viaje.',
                ], 422);
            }

            $validated['driver_id']  = $vehicleRequest->driver_id;
            $validated['vehicle_id'] = $vehicleRequest->vehicle_id;
        }

        $trip->update($validated);

        return response()->json([
            'message' => 'Viaje actualizado correctamente.',
            'data' => $trip->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage (borrado lógico).
     */
    public function destroy(Trip $trip)
    {
        $trip->delete();

        return response()->json([
            'message' => 'Viaje desactivado correctamente.',
        ], 200);
    }

    public function restore(Trip $trip)
    {
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
