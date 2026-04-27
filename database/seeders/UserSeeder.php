<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    // Crea el usuario administrador inicial del programa 
    public function run(): void
    {
        //
        User::firstOrCreate(
            [
                'email' => 'correo@gmail.com', // campo único para buscar
            ],
            [
                'full_name' => 'Administrador',
                'phone' => '0000-0000',
                'role_id' => 1,
                'password' => Hash::make('password1234'),
            ]
        );
    }
}
