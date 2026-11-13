<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nama', 'email', 'no_hp', 'alamat', 'foto_profil', 'username', 'password', 'password_terlihat', 'role'])]
#[Hidden(['password', 'password_terlihat', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'password_terlihat' => 'encrypted',
        ];
    }

    public function pesanan(): HasMany
    {
        return $this->hasMany(Order::class, 'id_user', 'id_user');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(OrderHistory::class, 'id_user', 'id_user');
    }

    public function produkTersimpan(): HasMany
    {
        return $this->hasMany(SavedProduct::class, 'id_user', 'id_user');
    }

    #[Scope]
    protected function pelanggan(Builder $query): Builder
    {
        return $query->where('role', 'pelanggan');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isPelanggan(): bool
    {
        return $this->role === 'pelanggan';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'pimpinan'], true);
    }

    public function fotoProfilUrl(): ?string
    {
        if (blank($this->foto_profil)) {
            return null;
        }

        if (str_starts_with($this->foto_profil, 'http')) {
            return $this->foto_profil;
        }

        return asset('images/avatars/'.basename($this->foto_profil));
    }

    public function hasStoredPassword(): bool
    {
        try {
            return filled($this->password_terlihat);
        } catch (DecryptException) {
            return false;
        }
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'admin' => route('admin.dashboard'),
            'pimpinan' => route('pimpinan.dashboard'),
            default => route('customer.dashboard'),
        };
    }
}
