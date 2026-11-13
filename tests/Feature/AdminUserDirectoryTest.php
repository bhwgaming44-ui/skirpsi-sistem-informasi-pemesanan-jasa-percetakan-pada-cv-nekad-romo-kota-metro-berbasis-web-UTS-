<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(SettingSeeder::class);
    }

    public function test_admin_can_see_all_users_and_reveal_a_stored_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'adminrahasia',
            'password_terlihat' => 'adminrahasia',
        ]);

        $customer = User::factory()->create([
            'nama' => 'Pelanggan Uji',
            'username' => 'pelangganuji',
            'password' => 'target12345',
            'password_terlihat' => 'target12345',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Pengguna')
            ->assertSee($customer->username)
            ->assertSee('Pelanggan Uji');

        $this->actingAs($admin)
            ->postJson(route('admin.users.password.reveal', $customer))
            ->assertOk()
            ->assertJson(['password' => 'target12345']);
    }

    public function test_customer_cannot_open_the_user_directory(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_generate_a_new_password_for_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create([
            'password' => 'passwordlama',
            'password_terlihat' => null,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.password.reset', $customer));

        $response->assertRedirect()->assertSessionHas('generated_password');

        $generatedPassword = $response->getSession()->get('generated_password');
        $customer->refresh();

        $this->assertNotEmpty($generatedPassword);
        $this->assertTrue(Hash::check($generatedPassword, $customer->password));
        $this->assertSame($generatedPassword, $customer->password_terlihat);
    }

    public function test_reveal_returns_not_found_when_password_is_not_stored(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['password_terlihat' => null]);

        $this->actingAs($admin)
            ->postJson(route('admin.users.password.reveal', $customer))
            ->assertNotFound();
    }
}
