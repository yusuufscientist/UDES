<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect based on role - explicitly check role
            if ($user->role === 'technician') {
                return redirect()->route('technician.dashboard');
            }

            if ($user->role === 'admin') {
                return redirect()->route('dashboard');
            }

            return redirect()->route('dashboard');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Get redirect path based on user role
     */
    protected function redirectPath($user): string
    {
        if ($user->isAdmin()) {
            return route('dashboard');
        }

        if ($user->isTechnician()) {
            return route('technician.dashboard');
        }

        return route('dashboard');
    }
}
