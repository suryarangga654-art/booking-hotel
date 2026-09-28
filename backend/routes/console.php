<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create', function () {
    $name = trim((string) $this->ask('Admin name'));
    $email = strtolower(trim((string) $this->ask('Admin email')));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Name and a valid email are required.');
        return 1;
    }

    if (User::where('email', $email)->exists()) {
        $this->error('That email address is already registered.');
        return 1;
    }

    $password = (string) $this->secret('Admin password (minimum 8 characters)');
    $confirmation = (string) $this->secret('Confirm password');
    if (strlen($password) < 8 || $password !== $confirmation) {
        $this->error('Password must be at least 8 characters and match confirmation.');
        return 1;
    }

    User::create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'peran' => 'admin',
    ]);

    $this->info("Admin account created for {$email}.");
    return 0;
})->purpose('Create an administrator account securely');
