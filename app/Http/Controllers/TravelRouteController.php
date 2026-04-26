<?php

namespace App\Http\Controllers;

use App\Http\Requests\TravelRoute\IndexRequest;
use App\Http\Requests\TravelRoute\StoreRequest;
use App\Http\Requests\TravelRoute\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\TravelRoute;

class TravelRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexRequest $request)
    {
        $validated = $request->validated();

        $query = TravelRoute::latest()
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed());

        $travelRoutes = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de rutas seleccionadas:',
            'data' => $travelRoutes,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $travelRoute = TravelRoute::create($request->validated());

        return response()->json([
            'message' => 'Ruta creada correctamente.',
            'data' => $travelRoute,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TravelRoute $travelRoute)
    {
        return response()->json([
            'message' => 'Ruta seleccionada:',
            'data' => $travelRoute,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, TravelRoute $travelRoute)
    {
        $travelRoute->update($request->validated());

        return response()->json([
            'message' => 'Ruta actualizada correctamente.',
            'data' => $travelRoute,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TravelRoute $travelRoute)
    {
        $travelRoute->delete();

        return response()->json([
            'message' => 'Ruta desactivada correctamente.',
        ], 200);
    }

    public function restore(TravelRoute $travelRoute)
    {
        if (!$travelRoute->trashed()) {
            return response()->json([
                'message' => 'No se pudo reactivar la ruta.',
            ], 404);
        }

        $travelRoute->restore();

        return response()->json([
            'message' => 'Ruta reactivada correctamente.',
            'data' => $travelRoute,
        ], 200);
    }
}
