<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@fantasyvolei.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
                'role' => 0,
            ]
        );

        User::updateOrCreate(
            ['email' => 'usuario@fantasyvolei.com'],
            [
                'name' => 'Usuario Teste',
                'password' => Hash::make('12345678'),
                'role' => 1,
            ]
        );
    }
}
