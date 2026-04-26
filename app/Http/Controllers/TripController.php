<?php

namespace App\Http\Controllers;

use App\Http\Requests\Trip\IndexRequest;
use App\Http\Requests\Trip\StoreRequest;
use App\Http\Requests\Trip\UpdateRequest;
use App\Models\Trip;
use App\Models\VehicleRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexRequest $request)
    {
        $validated = $request->validated();

        $user = $request->user();

        $query = Trip::with([
            'driver:id,full_name',
            'vehicle:id,plate,brand,model,year,vehicle_type,image_path',
            'travelRoute:id,name,start_point,end_point',
        ])
            ->latest()
            ->when($request->vehicle_id, fn($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->start_date && $request->end_date, fn($q) => $q->whereBetween('departure_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]))
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed())
            ->when($user->role_id === 3,   fn($q) => $q->where('driver_id', $user->id));

        $trips = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de viajes seleccionados:',
            'data' => $trips,
        ], 200);
    }

    /**
     * Para crear el viaje, la solicitud asociada debe estar en estado aprobado
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $vehicleRequest = VehicleRequest::with('vehicle')->find($validated['vehicle_request_id']);

        // Driver, vehicle y kilometraje de salida se derivan de la solicitud — no del payload
        $validated['driver_id']         = $vehicleRequest->driver_id;
        $validated['vehicle_id']        = $vehicleRequest->vehicle_id;
        $validated['departure_mileage'] = $vehicleRequest->vehicle->current_mileage;

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
            'vehicle:id,plate,brand,model,year,vehicle_type,image_path',
            'travelRoute:id,name,start_point,end_point',
        ]);

        return response()->json([
            'message' => 'Viaje seleccionado:',
            'data' => $trip,
        ], 200);
    }

    /**
     * Permite correcciones operativas y el cierre formal del viaje (return_at + return_mileage).
     * vehicle_request_id es inmutable - driver_id y vehicle_id no se re-derivan, en caso de ocuparlo,
     * se debe de eliminar el viaje y crear uno nuevo
     */
    public function update(UpdateRequest $request, Trip $trip)
    {
        $trip->update($request->validated());

        return response()->json([
            'message' => 'Viaje actualizado correctamente.',
            'data'    => $trip->fresh(),
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
