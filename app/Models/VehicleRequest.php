<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleRequest extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_DRIVER_REQUEST = 'driver_request';
    public const TYPE_DIRECT_ASSIGNMENT = 'direct_assignment';

    protected $table = 'vehicle_requests';

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'start_at',
        'end_at',
        'status',
        'observation',
        'approved_by',
        'approved_at',
        'request_type',
    ];

    protected $casts = [
        'driver_id' => 'integer',
        'vehicle_id' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'status' => 'string',
        'observation' => 'string',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'request_type' => 'string',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
