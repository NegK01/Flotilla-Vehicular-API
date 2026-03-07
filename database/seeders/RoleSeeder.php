<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    // En lugar de hacer un insert directamente, el FirstOrCreate verifica si ya existe algun primer dato con ese valor
    // Si existe, se omite
    // Si no existe, se crea
    public function run(): void
    {
        $roles = ['Admin', 'Operator', 'Driver'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
