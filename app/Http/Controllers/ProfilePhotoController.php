<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GoogleDriveImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfilePhotoController extends Controller
{
    public function __construct(private readonly GoogleDriveImageService $drive) {}

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'drive_link' => ['nullable', 'string', 'max:2048'],
        ], [
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 4 MB.',
        ]);

        $user = $request->user();
        $uploaded = $request->file('foto');
        $driveLink = trim((string) ($validated['drive_link'] ?? ''));

        if (! $uploaded instanceof UploadedFile && $driveLink === '') {
            return back()->withErrors([
                'foto' => 'Pilih foto dari laptop atau tempel link Google Drive terlebih dahulu.',
            ]);
        }

        try {
            $filename = $uploaded instanceof UploadedFile
                ? $this->storeUploadedFile($uploaded, $user)
                : $this->storeDriveFile($driveLink, $user);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'foto' => 'Foto profil gagal disimpan. Silakan coba lagi.',
            ]);
        }

        $this->deleteStoredAvatar($user);
        $user->update(['foto_profil' => $filename]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->deleteStoredAvatar($user);
        $user->update(['foto_profil' => null]);

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }

    private function storeUploadedFile(UploadedFile $file, User $user): string
    {
        $extension = strtolower($file->extension() ?: 'jpg');

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $filename = $this->avatarFilename($user, $extension);
        $directory = public_path('images/avatars');

        File::ensureDirectoryExists($directory);
        $file->move($directory, $filename);

        return $filename;
    }

    private function storeDriveFile(string $driveLink, User $user): string
    {
        $image = $this->drive->download($driveLink);
        $directory = public_path('images/avatars');

        File::ensureDirectoryExists($directory);

        $filename = $this->avatarFilename($user, $image['extension']);

        File::put($directory.DIRECTORY_SEPARATOR.$filename, $image['contents']);

        return $filename;
    }

    private function avatarFilename(User $user, string $extension): string
    {
        return 'avatar-'.$user->id_user.'-'.Str::lower(Str::random(12)).'.'.$extension;
    }

    private function deleteStoredAvatar(User $user): void
    {
        $current = $user->foto_profil;

        if (blank($current) || str_starts_with($current, 'http')) {
            return;
        }

        $path = public_path('images/avatars/'.basename($current));

        if (is_file($path)) {
            unlink($path);
        }
    }
}
