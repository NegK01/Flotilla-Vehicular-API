<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use SoftDeletes;

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
