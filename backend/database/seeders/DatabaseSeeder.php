<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. MEMBUAT DATA USER (ADMIN & CUSTOMER CONTOH)
        User::create([
            'name' => 'Administrator Hotel',
            'email' => 'admin@hotel.com',
            'password' => Hash::make('password'), // Password otomatis di-hash aman
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Resepsionis Hotel',
            'email' => 'resepsionis@hotel.com',
            'password' => Hash::make('password'),
            'role' => 'resepsionis',
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
            'password' => Hash::make('user123'),
            'role' => 'tamu',
        ]);
    }
}