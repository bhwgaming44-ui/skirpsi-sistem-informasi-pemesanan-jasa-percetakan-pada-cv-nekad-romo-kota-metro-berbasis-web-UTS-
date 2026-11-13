<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_user_can_upload_profile_photo_from_local_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('profile-photo.update'), [
            'foto' => UploadedFile::fake()->image('avatar.jpg', 320, 320),
        ]);

        $response->assertRedirect()->assertSessionHas('success');

        $user->refresh();

        $this->assertNotNull($user->foto_profil);
        $this->assertFileExists(public_path('images/avatars/'.$user->foto_profil));

        $this->deleteAvatar($user);
    }

    public function test_user_can_import_profile_photo_from_google_drive_share_link(): void
    {
        Http::fake([
            'drive.usercontent.google.com/*' => Http::response($this->pngBytes(), 200, ['Content-Type' => 'image/png']),
            'drive.google.com/*' => Http::response($this->pngBytes(), 200, ['Content-Type' => 'image/png']),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('profile-photo.update'), [
            'drive_link' => 'https://drive.google.com/file/d/1A2b3C4d5E6f7G8h9I0j/view?usp=sharing',
        ]);

        $response->assertRedirect()->assertSessionHas('success');

        $user->refresh();

        $this->assertNotNull($user->foto_profil);
        $this->assertStringEndsWith('.png', $user->foto_profil);
        $this->assertFileExists(public_path('images/avatars/'.$user->foto_profil));

        $this->deleteAvatar($user);
    }

    public function test_photo_upload_requires_a_file_or_drive_link(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile-photo.update'), [])
            ->assertSessionHasErrors('foto');
    }

    public function test_non_google_drive_link_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile-photo.update'), [
                'drive_link' => 'https://example.com/foto.jpg',
            ])
            ->assertSessionHasErrors('drive_link');
    }

    public function test_user_can_delete_profile_photo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile-photo.update'), [
            'foto' => UploadedFile::fake()->image('avatar.png', 200, 200),
        ]);

        $user->refresh();
        $path = public_path('images/avatars/'.$user->foto_profil);

        $this->assertFileExists($path);

        $this->actingAs($user)
            ->delete(route('profile-photo.destroy'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull($user->fresh()->foto_profil);
        $this->assertFileDoesNotExist($path);
    }

    private function deleteAvatar(User $user): void
    {
        $path = public_path('images/avatars/'.basename((string) $user->foto_profil));

        if (is_file($path)) {
            unlink($path);
        }
    }

    private function pngBytes(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Zl1sAAAAASUVORK5CYII=');
    }
}
