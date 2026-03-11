<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'      => 'Administrateur',
            'email'     => 'admin@techshop.bj',
            'password'  => Hash::make('admin123'),
            'role'      => UserRole::ADMIN,
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Vendeur Test',
            'email'     => 'vendeur@techshop.bj',
            'password'  => Hash::make('vendeur123'),
            'role'      => UserRole::VENDEUR,
            'is_active' => true,
        ]);
    }
}
