<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 1. Cek apakah user admin ada
echo "=== CEK USER DI DATABASE ===\n";
$user = User::where('email', 'admin@padi.com')->first();

if ($user) {
    echo "✓ User ditemukan!\n";
    echo "  - Name: " . $user->name . "\n";
    echo "  - Email: " . $user->email . "\n";
    echo "  - Role: " . $user->role . "\n";
    echo "  - Password Hash: " . substr($user->password, 0, 50) . "...\n";
    
    // 2. Cek apakah password bisa diverifikasi
    echo "\n=== CEK PASSWORD ===\n";
    if (Hash::check('password123', $user->password)) {
        echo "✓ Password 'password123' BENAR!\n";
    } else {
        echo "✗ Password 'password123' SALAH!\n";
    }
} else {
    echo "✗ User tidak ditemukan!\n";
    echo "\nDaftar user di database:\n";
    $allUsers = User::all();
    foreach ($allUsers as $u) {
        echo "  - {$u->email} ({$u->name})\n";
    }
}
