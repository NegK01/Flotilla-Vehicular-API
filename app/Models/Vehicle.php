<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    use SoftDeletes;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_OUT_OF_SERVICE = 'out_of_service';

    protected $table = 'vehicles';

    protected $appends = [
        'image_url',
    ];

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

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => 
            ($this->image_path && Storage::disk('public')->exists($this->image_path)) 
                ? Storage::disk('public')->url($this->image_path) 
                : asset('images/placeholder.png')
        );
    }

    public function vehicleRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
