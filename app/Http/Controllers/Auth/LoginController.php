<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    // Tampilkan form login
    public function showLoginForm()
    {
        // Ambil email dan password dari cookie jika ada
        $email = request()->cookie('remember_me_email', '');
        $password = request()->cookie('remember_me_password', '');

        // Kirim email dan password ke view login
        return view('auth.login', ['email' => $email, 'password' => $password]);
    }

    // Proses login
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember'); // Mengecek apakah checkbox "Remember Me" dicentang

        Log::info('Login attempt', ['email' => $request->email]); // Log attempt

        // Cek kredensial dan autentikasi
        if (Auth::attempt($credentials, $remember)) {
            Log::info('Login successful', ['email' => $request->email]); // Log success
            // Simpan email dan password dalam cookie jika "Remember Me" dicentang
            if ($remember) {
                $response = redirect()->route($this->determineRedirectRoute());
                $response->cookie('remember_me_email', $request->email, 60 * 24 * 7); // Simpan selama 1 minggu
                $response->cookie('remember_me_password', $request->password, 60 * 24 * 7); // Simpan selama 1 minggu
                return $response;
            }

            // Jika login berhasil, redirect ke halaman sesuai role
            return redirect()->route($this->determineRedirectRoute());
        }

        Log::warning('Login failed', ['email' => $request->email]); // Log failure

        // Jika login gagal, kembalikan error
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    // Menentukan rute redirect berdasarkan peran pengguna
    private function determineRedirectRoute()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return 'admin.dashboard';
        } elseif ($user->role === 'sdm') {
            return 'sdm.dashboard';
        } elseif ($user->role === 'marketing') {
            return 'marketing.dashboard';
        }
        return 'login';
    }

    // Proses logout
    public function logout(Request $request)
    {
        // Hapus cookie saat logout
        $response = redirect('/login');
        $response->cookie('remember_me_email', '', -1);
        $response->cookie('remember_me_password', '', -1);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $response;
    }
}
