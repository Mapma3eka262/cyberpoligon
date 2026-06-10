<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем администратора
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'name' => 'Администратор',
            'surname' => 'Системы',
            'email' => 'admin@ctf-platform.local',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Администратор создан: логин - admin, пароль - admin');
    }
}