<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const ROLES = [
        'admin' => 'Administrator',
        'pimpinan' => 'Pimpinan',
        'pelanggan' => 'Pelanggan',
    ];

    public function index(Request $request): View
    {
        $query = User::query()
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'pimpinan' THEN 2 ELSE 3 END")
            ->orderBy('nama');

        if ($request->filled('q')) {
            $search = $request->string('q')->trim()->toString();

            $query->where(function ($builder) use ($search): void {
                $builder->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%')
                    ->orWhere('no_hp', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('role') && array_key_exists($request->string('role')->toString(), self::ROLES)) {
            $query->where('role', $request->string('role')->toString());
        }

        return view('admin.users.index', [
            'users' => $query->paginate(12)->withQueryString(),
            'roles' => self::ROLES,
        ]);
    }

    public function revealPassword(User $user): JsonResponse
    {
        try {
            $password = $user->password_terlihat;
        } catch (DecryptException) {
            $password = null;
        }

        if (blank($password)) {
            return response()->json([
                'message' => 'Password akun ini belum tersimpan. Buat password baru untuk menampilkannya.',
            ], 404);
        }

        return response()
            ->json(['password' => $password])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $password = Str::password(12, true, true, false);

        $user->password = $password;
        $user->password_terlihat = $password;
        $user->save();

        return back()
            ->with('success', 'Password untuk @'.$user->username.' berhasil dibuat ulang.')
            ->with('generated_password', $password)
            ->with('generated_username', $user->username);
    }
}
