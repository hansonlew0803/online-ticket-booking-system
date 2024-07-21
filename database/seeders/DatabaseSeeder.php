<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Booking;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Seed users
        User::factory(10)->create();

        // Seed events
        Event::factory(5)->create();

        // Seed bookings
        Booking::factory(20)->create();
    }
}
