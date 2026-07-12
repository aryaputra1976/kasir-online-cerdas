<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                    'email' => 'Akun Anda sedang nonaktif. Hubungi owner atau admin.',
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
}
