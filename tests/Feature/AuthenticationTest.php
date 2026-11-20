<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed([
            SettingSeeder::class,
            UserSeeder::class,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_customer_area(): void
    {
        $this->get(route('customer.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_login_and_open_the_customer_dashboard(): void
    {
        $this->post(route('login.store'), [
            'login' => 'raihan',
            'password' => 'pelanggan123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticated();
        $this->get(route('customer.dashboard'))->assertOk();
    }

    public function test_customer_cannot_access_the_admin_area(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_and_pimpinan_can_open_their_own_dashboards(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $pimpinan = User::query()->where('username', 'pimpinan')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($pimpinan)->get(route('pimpinan.dashboard'))->assertOk();
    }

    public function test_registration_stores_an_encrypted_copy_of_the_password_for_admin_support(): void
    {
        $this->post(route('register.store'), [
            'nama' => 'Pelanggan Baru',
            'username' => 'pelangganbaru',
            'email' => 'pelangganbaru@example.com',
            'no_hp' => '081299998888',
            'alamat' => 'Jl. Uji No. 1, Kota Metro',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('customer.dashboard'));

        $user = User::query()->where('username', 'pelangganbaru')->firstOrFail();

        $this->assertSame('rahasia123', $user->password_terlihat);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_authenticated_user_can_open_login_page_to_switch_accounts(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $this->actingAs($customer)
            ->get(route('login'))
            ->assertOk()
            ->assertSee('Perangkat Ini Sedang Aktif')
            ->assertSee($customer->nama);
    }

    public function test_user_can_switch_account_instantly_via_switch_route(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $this->actingAs($customer)
            ->post(route('switch-account'), ['role' => 'admin'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame('admin', auth()->user()->username);

        $this->post(route('switch-account'), ['role' => 'pimpinan'])
            ->assertRedirect(route('pimpinan.dashboard'));

        $this->assertSame('pimpinan', auth()->user()->username);
    }

    public function test_login_as_another_user_switches_session_seamlessly(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $this->actingAs($customer);
        $this->assertSame('raihan', auth()->user()->username);

        $this->post(route('login.store'), [
            'login' => 'admin',
            'password' => 'admin123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertSame('admin', auth()->user()->username);
    }
}
