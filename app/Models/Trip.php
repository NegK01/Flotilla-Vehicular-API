<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes;

    protected $table = 'trips';

    protected $fillable = [
        'vehicle_request_id',
        'driver_id',
        'vehicle_id',
        'travel_route_id',
        'departure_at',
        'return_at',
        'departure_mileage',
        'return_mileage',
        'observations',
    ];

    protected $casts = [
        'vehicle_request_id' => 'integer',
        'driver_id' => 'integer',
        'vehicle_id' => 'integer',
        'travel_route_id' => 'integer',
        'departure_at' => 'datetime',
        'return_at' => 'datetime',
        'departure_mileage' => 'integer',
        'return_mileage' => 'integer',
        'observations' => 'string',
    ];

    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function travelRoute(): BelongsTo
    {
        return $this->belongsTo(TravelRoute::class);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
