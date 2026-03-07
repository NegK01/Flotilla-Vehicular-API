<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Laravel ya infiere el nombre de la tabla a partir del nombre del modelo, pero no importa, lo declaramos de todos modos por consistencia 
    protected $table = 'roles';
    // Eloquent asume por defecto que la PK es un valor entero y autoincremental llamado "id"

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'string'
    ];

    public function users() // plural porque un rol puede tener muchos usuarios
    {
        return $this->hasMany(User::class);
    }

    public function getRouteKeyName()
    {
        return 'id';
        // Laravel usa la PK como RouteKey por defecto, pero lo dejaremos tambien por consistencia 
    }
}
