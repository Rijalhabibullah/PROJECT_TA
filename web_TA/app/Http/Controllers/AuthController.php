<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // 1. Tampilkan Halaman Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 2. Proses Login
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Cek ke Database
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard'); // Redirect ke dashboard jika sukses
        }

        // Jika Gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // 3. Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // 4. Mobile Login
    public function mobileLogin(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:4'],
        ]);

        $username = trim($validated['username']);
        $password = $validated['password'];
        $user = User::where('name', $username)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login mobile berhasil',
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    // 5. Mobile Register
    public function mobileRegister(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:4'],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        $username = trim($validated['username']);
        $password = $validated['password'];

        if (User::where('name', $username)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Username sudah digunakan',
            ], 409);
        }

        $baseSlug = Str::slug($username, '.');
        if ($baseSlug === '') {
            $baseSlug = 'user';
        }

        $email = $baseSlug . '@agripadi.local';
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = $baseSlug . $counter . '@agripadi.local';
            $counter++;
        }

        $user = User::create([
            'name' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }
}