<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use SoftDeletes;

    public const TYPE_PREVENTIVE = 'preventive';
    public const TYPE_CORRECTIVE = 'corrective';

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $table = 'maintenances';

    protected $fillable = [
        'vehicle_id',
        'type',
        'start_at',
        'closed_at',
        'description',
        'cost',
        'status',
    ];

    protected $casts = [
        'vehicle_id' => 'integer',
        'type' => 'string',
        'start_at' => 'datetime',
        'closed_at' => 'datetime',
        'description' => 'string',
        'cost' => 'decimal:2',
        'status' => 'string',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
