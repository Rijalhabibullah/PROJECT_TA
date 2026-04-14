<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus user lama (opsional, untuk mencegah duplikat jika tidak fresh)
        // User::truncate(); 

        User::create([
            'name' => 'Admin Padi',
            'email' => 'admin@padi.com', // <--- Pastikan ini benar
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }
}