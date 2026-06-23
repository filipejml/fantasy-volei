<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@fantasyvolei.com',
            'password' => Hash::make('12345678'),
            'role' => 0,
        ]);

        User::create([
            'name' => 'Usuário Teste',
            'email' => 'usuario@fantasyvolei.com',
            'password' => Hash::make('12345678'),
            'role' => 1,
        ]);
    }
}