<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('customer.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'min:4', 'max:50', Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id_user, 'id_user')],
            'no_hp' => ['required', 'string', 'min:9', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'password_lama' => ['nullable', 'string'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (filled($validated['password'] ?? null)) {
            if (! Hash::check($validated['password_lama'] ?? '', $user->password)) {
                return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.'])->withInput();
            }

            // Password baru akan otomatis di-hash oleh cast model User.
        } else {
            unset($validated['password']);
        }

        unset($validated['password_lama']);

        $user->update($validated);

        return redirect()
            ->route('customer.profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
