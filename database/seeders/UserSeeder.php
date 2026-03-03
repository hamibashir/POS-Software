<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Seed admin and cashier accounts.
     */
    public function run(): void
    {
        // Admin account
        User::updateOrCreate(
            ['email' => 'admin@hardwarepro.com'],
            [
                'name'      => 'Store Admin',
                'email'     => 'admin@hardwarepro.com',
                'password'  => Hash::make('admin1234'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        // Cashier account
        User::updateOrCreate(
            ['email' => 'cashier@hardwarepro.com'],
            [
                'name'      => 'Store Cashier',
                'email'     => 'cashier@hardwarepro.com',
                'password'  => Hash::make('cashier1234'),
                'role'      => 'cashier',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Users seeded: admin@hardwarepro.com / cashier@hardwarepro.com');
    }
}
