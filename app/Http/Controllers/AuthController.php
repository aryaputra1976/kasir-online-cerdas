<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->defaultRedirectPath(Auth::user()));
        }

        return view('login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Email atau password tidak sesuai.',
                ]);
        }

        $this->clearLoginThrottle($request);

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Akun Anda sedang nonaktif. Hubungi Owner.',
                ]);
        }

        return redirect()->intended($this->defaultRedirectPath($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil keluar dari aplikasi.');
    }

    private function defaultRedirectPath(?User $user): string
    {
        if ($user?->hasRole(User::ROLE_KASIR)) {
            return route('pos.index');
        }

        return route('dashboard');
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower((string) $request->input('email')) . '|' . $request->ip();
    }

    private function clearLoginThrottle(Request $request): void
    {
        $key = $this->throttleKey($request);

        RateLimiter::clear($key);
        RateLimiter::clear(md5('login' . $key));
    }
}
