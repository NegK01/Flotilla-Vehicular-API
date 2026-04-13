<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'role_id',
        'password',
    ];

    protected $casts = [
        'full_name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'role_id' => 'integer',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function vehicleRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class, 'driver_id');
    }

    public function reviewedVehicleRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class, 'reviewed_by');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
