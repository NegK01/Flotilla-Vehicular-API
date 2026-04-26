<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\DriverHistoryRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Reporte 3 
    // Muestra solicitudes y viajes del chofer para consulta administrativa
    public function driverHistory(DriverHistoryRequest $request, User $driver)
    {
        $validated = $request->validated();
        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end   = Carbon::parse($validated['end_date'])->endOfDay();

        $requestsHistory = DB::table('vehicle_requests as vr')
            ->select(
                'vr.id as request_id',
                'vr.request_type',
                'vr.status as request_status',
                'vr.start_at',
                'vr.end_at',
                'vr.observation',
                'driver.full_name as driver_name',
                'v.plate as vehicle_plate',
                'v.brand as vehicle_brand',
                'v.model as vehicle_model',
                'v.image_path as vehicle_image',
                'reviewer.full_name as reviewed_by_name',
                'vr.reviewed_at'
            )
            ->join('users as driver', 'vr.driver_id', '=', 'driver.id')
            ->join('vehicles as v', 'vr.vehicle_id', '=', 'v.id')
            ->leftJoin('users as reviewer', 'vr.reviewed_by', '=', 'reviewer.id')
            ->where('vr.driver_id', $driver->id)
            ->whereNull('vr.deleted_at')
            ->where(function (Builder $query) use ($start, $end) {
                $query
                    ->whereBetween('vr.start_at', [$start, $end])
                    ->orWhereBetween('vr.end_at', [$start, $end])
                    ->orWhere(function (Builder $nestedQuery) use ($start, $end) {
                        $nestedQuery
                            ->where('vr.start_at', '<', $start)
                            ->where('vr.end_at', '>', $end);
                    });
            })
            ->orderByDesc('vr.start_at')
            ->get();

        $tripsHistory = DB::table('trips as t')
            ->select(
                't.id as trip_id',
                't.departure_at',
                't.return_at',
                't.departure_mileage',
                't.return_mileage',
                DB::raw('fn_calculate_km_driven(t.departure_mileage, t.return_mileage) AS km_driven'),
                't.observations',
                'driver.full_name as driver_name',
                'v.plate as vehicle_plate',
                'v.brand as vehicle_brand',
                'v.model as vehicle_model',
                'v.image_path as vehicle_image',
                'tr.name as route_name',
                'tr.start_point as route_start_point',
                'tr.end_point as route_end_point'
            )
            ->join('users as driver', 't.driver_id', '=', 'driver.id')
            ->join('vehicles as v', 't.vehicle_id', '=', 'v.id')
            ->leftJoin('travel_routes as tr', 't.travel_route_id', '=', 'tr.id')
            ->where('t.driver_id', $driver->id)
            ->whereNull('t.deleted_at')
            ->where(function (Builder $query) use ($start, $end) {
                $query
                    ->whereBetween('t.departure_at', [$start, $end])
                    ->orWhereBetween('t.return_at', [$start, $end])
                    ->orWhere(function (Builder $nestedQuery) use ($start, $end) {
                        $nestedQuery
                            ->where('t.departure_at', '<', $start)
                            ->where(function (Builder $openTripQuery) use ($end) {
                                $openTripQuery
                                    ->whereNull('t.return_at')
                                    ->orWhere('t.return_at', '>', $end);
                            });
                    });
            })
            ->orderByDesc('t.departure_at')
            ->get();

        return response()->json([
            'message' => 'Reporte de historial del chofer generado correctamente.',
            'data' => [
                'driver' => [
                    'id'        => $driver->id,
                    'full_name' => $driver->full_name,
                    'email'     => $driver->email,
                    'phone'     => $driver->phone,
                ],
                'filters' => [
                    'start_date' => $start->toDateTimeString(),
                    'end_date'   => $end->toDateTimeString(),
                ],
                'vehicle_requests' => $requestsHistory,
                'trips'            => $tripsHistory,
            ],
        ], 200);
    }
}
