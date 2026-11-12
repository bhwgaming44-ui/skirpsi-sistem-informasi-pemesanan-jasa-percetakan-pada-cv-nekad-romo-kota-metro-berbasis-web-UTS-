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
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'min:4', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'min:9', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $validated['role'] = 'pelanggan';

        $user = User::query()->create($validated);

        Auth::login($user);
        $request->session()->regenerate();

        app(CartService::class)->mergeGuestCartIntoUser($user);
        app(SavedProductService::class)->mergeSessionIntoUser($user);

        return redirect()
            ->route('customer.dashboard')
            ->with('success', 'Akun berhasil dibuat. Selamat datang di Nekad Romo Print, '.$user->nama.'!');
    }
}
