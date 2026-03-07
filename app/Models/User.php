<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

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
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Agregar las demas relaciones cuando ya existan

    public function getRouteKeyName()
    {
        return 'id';
    }
}
