<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    // UserController.php

    public function register(Request $request)
    {
        // Validasi input pengguna (email, password, dll.)
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            // tambahkan validasi lainnya di sini sesuai kebutuhan
        ]);

        // Membuat pengguna baru
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            // tambahkan kolom lainnya sesuai kebutuhan
        ]);

        // Menetapkan peran default ke pengguna baru
        $user->roles()->attach(2); // 2 adalah ID dari peran 'user'

        // Lanjutkan dengan logika redirect atau respons setelah registrasi
    }

}
