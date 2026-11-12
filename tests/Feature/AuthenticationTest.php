<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
