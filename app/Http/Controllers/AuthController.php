<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private const DEACTIVATED_MESSAGE = 'Your account is deactivated by the admin. Please contact the admin to reactivate your account.';

    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->requiresEmailVerification() && ! Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

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

            if (Auth::user()->requiresEmailVerification() && ! Auth::user()->hasVerifiedEmail()) {
                Auth::user()->sendEmailVerificationNotification();

                return redirect()->route('verification.notice');
            }

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

    public function showVerificationNotice()
    {
        if (! Auth::user()->requiresEmailVerification() || Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('terms.show');
        }

        return view('auth.verify-email');
    }

    public function resendVerificationEmail(Request $request)
    {
        if (! $request->user()->requiresEmailVerification() || $request->user()->hasVerifiedEmail()) {
            return redirect()->route('terms.show');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->route('terms.show')->with('success', 'Your email address has been verified successfully.');
    }
}
