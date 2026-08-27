<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('app.admin.email')],
            [
                'name' => config('app.admin.name'),
                'password' => Hash::make(config('app.admin.password')),
                'role' => UserRole::Admin,
            ],
        );
    }
}
