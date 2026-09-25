<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ridhisidhi.test'],
            [
                'name' => 'Ridhi Sidhi Administrator',
                'password' => bcrypt('Admin@12345'),
                'is_admin' => true,
                'phone' => null,
                'status' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
