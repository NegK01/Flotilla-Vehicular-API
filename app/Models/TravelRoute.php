<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelRoute extends Model
{
    use SoftDeletes;

    protected $table = 'travel_routes';

    protected $fillable = [
        'name',
        'start_point',
        'end_point',
        'estimated_distance',
        'description',
    ];

    protected $casts = [
        'name' => 'string',
        'start_point' => 'string',
        'end_point' => 'string',
        'estimated_distance' => 'decimal:2',
        'description' => 'string',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
