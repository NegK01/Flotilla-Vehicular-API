<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use SoftDeletes;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_OUT_OF_SERVICE = 'out_of_service';

    protected $table = 'vehicles';

    protected $fillable = [
        'plate',
        'brand',
        'model',
        'year',
        'vehicle_type',
        'capacity',
        'fuel_type',
        'image_path',
        'status',
        'current_mileage',
    ];

    protected $casts = [
        'plate' => 'string',
        'brand' => 'string',
        'model' => 'string',
        'year' => 'integer',
        'vehicle_type' => 'string',
        'capacity' => 'integer',
        'fuel_type' => 'string',
        'image_path' => 'string',
        'status' => 'string',
        'current_mileage' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }
}
