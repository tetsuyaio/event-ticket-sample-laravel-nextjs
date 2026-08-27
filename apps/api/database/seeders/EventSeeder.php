<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', config('app.admin.email'))->firstOrFail();

        Event::factory()
            ->count(10)
            ->published()
            ->for($admin, 'creator')
            ->create();
    }
}
