<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private const DEACTIVATED_MESSAGE = 'Your account is deactivated by the admin. Please contact the admin to reactivate your account.';

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('terms.show');
        }

        if (session('guest_terms_accepted')) {
            return redirect()->route('guest.dashboard');
        }

        if (session('is_guest')) {
            return redirect()->route('guest.terms');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            return back()
                ->withInput($request->only('email'))
                ->with('deactivated_message', self::DEACTIVATED_MESSAGE)
                ->withErrors([
                    'email' => self::DEACTIVATED_MESSAGE,
                ]);
        }

        $request->session()->forget([
            'is_guest',
            'guest_terms_accepted',
            'agreed_to_terms',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // Send them to the terms page; middleware will handle routing
            return redirect()->route('terms.show');
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget([
            'is_guest',
            'guest_terms_accepted',
            'agreed_to_terms',
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
