<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Sample User',
                'password' => Hash::make('password'),
                'role' => UserRole::User,
            ],
        );
    }
}
