<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedConfiguredUser(
            env('ADMIN_EMAIL'),
            env('ADMIN_NAME', 'Administrator'),
            env('ADMIN_PASSWORD'),
            'admin',
        );

        $this->seedConfiguredUser(
            env('SEED_USER_EMAIL'),
            env('SEED_USER_NAME', 'Tamu'),
            env('SEED_USER_PASSWORD'),
            'tamu',
        );
    }

    private function seedConfiguredUser(?string $email, string $name, ?string $password, string $role): void
    {
        if (!$email && !$password) {
            return;
        }

        if (!$email || !$password || strlen($password) < 8 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Seed credentials for {$role} require a valid email and a password of at least 8 characters.");
        }

        User::firstOrCreate(
            ['email' => strtolower(trim($email))],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'peran' => $role,
            ],
        );
    }
}