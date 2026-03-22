<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // Laravel ya infiere el nombre de la tabla a partir del nombre del modelo, pero no importa, lo declaramos de todos modos por consistencia 
    protected $table = 'roles';
    // Eloquent asume por defecto que la PK es un valor entero y autoincremental llamado "id"

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'string',
    ];

    // [: HasMany] Tipado fuerte: se especifica que dato se esta devolviendo
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'id'; // ya se intuye que es la PK pero tambien lo dejamos por consistencia 
    }
}
