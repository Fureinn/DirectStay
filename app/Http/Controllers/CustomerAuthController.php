<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationCodeMailable;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    /**
     * Show customer registration form.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('customer.bookings');
        }

        return view('customer.auth.register');
    }

    /**
     * Handle customer registration and dispatch 6-digit verification code.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $code = sprintf('%06d', mt_rand(100000, 999999));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'guest',
            'email_verified_at' => null,
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(15),
        ]);

        try {
            Mail::to($user->email)->send(new EmailVerificationCodeMailable($user, $code));
        } catch (\Throwable $e) {
            report($e);
        }

        session(['pending_verification_user_id' => $user->id]);

        return redirect()->route('customer.verify.show')
            ->with('success', 'A 6-digit verification code has been dispatched to your email address.');
    }

    /**
     * Show 6-digit code verification prompt.
     */
    public function showVerify(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('customer.bookings');
        }

        $userId = session('pending_verification_user_id');
        if (! $userId) {
            return redirect()->route('customer.login')
                ->with('info', 'Your verification session expired. Please sign in or register.');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('customer.register');
        }

        return view('customer.auth.verify_code', [
            'email' => $user->email,
        ]);
    }

    /**
     * Verify the 6-digit code entered by user.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('pending_verification_user_id');
        if (! $userId) {
            return redirect()->route('customer.login')
                ->with('info', 'Your verification session expired. Please sign in to verify.');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('customer.register');
        }

        if ($user->verification_code !== trim($request->input('code'))) {
            return back()->withErrors([
                'code' => 'The 6-digit code entered is incorrect. Please check your email and try again.',
            ]);
        }

        if ($user->verification_code_expires_at && $user->verification_code_expires_at->isPast()) {
            return back()->withErrors([
                'code' => 'This verification code has expired. Please click "Resend Code" to receive a new one.',
            ]);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        session()->forget('pending_verification_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.bookings')
            ->with('success', 'Email verified successfully! Welcome to DirectStay, '.$user->name.'.');
    }

    /**
     * Resend verification code.
     */
    public function resendVerificationCode(Request $request): RedirectResponse
    {
        $userId = session('pending_verification_user_id');
        if (! $userId) {
            return redirect()->route('customer.login')
                ->with('info', 'Your session expired. Please sign in to receive a new code.');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('customer.register');
        }

        $code = sprintf('%06d', mt_rand(100000, 999999));
        $user->update([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(15),
        ]);

        try {
            Mail::to($user->email)->send(new EmailVerificationCodeMailable($user, $code));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'A fresh 6-digit verification code has been dispatched to '.$user->email.'.');
    }

    /**
     * Show customer login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('customer.bookings');
        }

        return view('customer.auth.login');
    }

    /**
     * Handle customer login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (! $user->isHost() && $user->email_verified_at === null) {
                // If not verified yet, ensure active verification code and prompt verification
                if (! $user->verification_code || ($user->verification_code_expires_at && $user->verification_code_expires_at->isPast())) {
                    $code = sprintf('%06d', mt_rand(100000, 999999));
                    $user->update([
                        'verification_code' => $code,
                        'verification_code_expires_at' => now()->addMinutes(15),
                    ]);

                    try {
                        Mail::to($user->email)->send(new EmailVerificationCodeMailable($user, $code));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }

                session(['pending_verification_user_id' => $user->id]);
                Auth::logout();

                return redirect()->route('customer.verify.show')
                    ->with('info', 'Please enter the 6-digit verification code sent to '.$user->email.' to activate your account.');
            }

            if ($user->isHost()) {
                return redirect()->intended(route('host.dashboard'));
            }

            return redirect()->intended(route('customer.bookings'))
                ->with('success', 'Logged in successfully. Welcome back, '.$user->name.'!');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    /**
     * Log out customer.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('units.index')->with('success', 'Logged out successfully.');
    }
}
