<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use App\Services\SavedProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Jika perangkat sedang login dengan akun lain, logout dulu agar berganti akun secara bersih
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (! Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Kombinasi username/email dan password tidak cocok.',
            ]);
        }

        $request->session()->regenerate();

        app(CartService::class)->mergeGuestCartIntoUser($request->user());
        app(SavedProductService::class)->mergeSessionIntoUser($request->user());

        return redirect()
            ->intended($request->user()->dashboardRoute())
            ->with('success', 'Selamat datang kembali, '.$request->user()->nama.'!');
    }

    /**
     * Beralih peran akun instan (1-Klik) untuk keperluan demo sidang / testing penguji.
     */
    public function switchAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,pimpinan,pelanggan'],
        ]);

        $user = match ($validated['role']) {
            'admin' => User::query()->where('username', 'admin')->first()
                ?? User::query()->where('role', 'admin')->first(),
            'pimpinan' => User::query()->where('username', 'pimpinan')->first()
                ?? User::query()->where('role', 'pimpinan')->first(),
            default => User::query()->where('username', 'raihan')->first()
                ?? User::query()->where('role', 'pelanggan')->first(),
        };

        if (! $user) {
            return back()->with('error', 'Akun demo untuk peran tersebut belum tersedia.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        app(CartService::class)->mergeGuestCartIntoUser($user);
        app(SavedProductService::class)->mergeSessionIntoUser($user);

        return redirect()
            ->to($user->dashboardRoute())
            ->with('success', 'Beralih ke akun '.$user->nama.' ('.ucfirst($user->role).') berhasil.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda berhasil keluar dari akun.');
    }
}
