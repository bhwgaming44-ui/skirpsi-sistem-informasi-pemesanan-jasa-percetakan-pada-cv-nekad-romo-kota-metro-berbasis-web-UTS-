<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    private array $problems = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_every_page_renders_without_server_errors(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $pimpinan = User::query()->where('username', 'pimpinan')->firstOrFail();

        $product = Product::query()->firstOrFail();
        $order = Order::query()->where('id_user', $customer->id_user)->firstOrFail();
        $detail = OrderItem::query()->where('id_pesanan', $order->id_pesanan)->firstOrFail();
        $otherCustomer = User::query()->where('username', 'siti')->firstOrFail();

        $this->check('guest', null, [
            'home' => route('home'),
            'catalog.index' => route('catalog.index'),
            'catalog.filter' => route('catalog.filter'),
            'catalog.show' => route('catalog.show', $product),
            'cart.index' => route('cart.index'),
            'tracking.index' => route('tracking.index'),
            'tracking.show' => route('tracking.show', $order->kode_pesanan),
            'login' => route('login'),
            'register' => route('register'),
        ], [200, 302]);

        $this->check('customer', $customer, [
            'customer.dashboard' => route('customer.dashboard'),
            'customer.orders.index' => route('customer.orders.index'),
            'customer.orders.show' => route('customer.orders.show', $order),
            'customer.profile.edit' => route('customer.profile.edit'),
            'saved.index' => route('saved.index'),
            'payments.show' => $order->paymentUrl(),
            'orders.invoice' => route('orders.invoice', $order),
            'orders.design' => route('orders.design', [$order, $detail]),
        ], [200, 302]);

        $this->check('customer', $customer, [
            'checkout.index' => route('checkout.index'),
        ], [200, 302]);

        $this->check('admin', $admin, [
            'admin.dashboard' => route('admin.dashboard'),
            'admin.production.board' => route('admin.production.board'),
            'admin.documentation' => route('admin.documentation'),
            'admin.finishings.index' => route('admin.finishings.index'),
            'admin.categories.index' => route('admin.categories.index'),
            'admin.notifications' => route('admin.notifications'),
            'admin.customers.index' => route('admin.customers.index'),
            'admin.customers.show' => route('admin.customers.show', $otherCustomer),
            'admin.payments.index' => route('admin.payments.index'),
            'admin.settings.index' => route('admin.settings.index'),
            'admin.users.index' => route('admin.users.index'),
            'admin.orders.index' => route('admin.orders.index'),
            'admin.orders.show' => route('admin.orders.show', $order),
            'admin.orders.invoice' => route('admin.orders.invoice', $order),
            'admin.products.index' => route('admin.products.index'),
            'admin.products.create' => route('admin.products.create'),
            'admin.products.edit' => route('admin.products.edit', $product),
            'reports.index' => route('reports.index'),
            'reports.print' => route('reports.print'),
            'reports.export' => route('reports.export'),
        ]);

        $this->check('pimpinan', $pimpinan, [
            'pimpinan.dashboard' => route('pimpinan.dashboard'),
            'reports.index' => route('reports.index'),
        ]);

        $this->assertSame([], $this->problems, PHP_EOL.implode(PHP_EOL, $this->problems));
    }

    /**
     * @param  array<string, string>  $routes
     * @param  array<int, int>  $allowed
     */
    private function check(string $role, ?User $user, array $routes, array $allowed = [200]): void
    {
        foreach ($routes as $name => $url) {
            $request = $user === null ? $this : $this->actingAs($user);

            try {
                $response = $request->get($url);
                $status = $response->getStatusCode();
            } catch (\Throwable $e) {
                $this->problems[] = "[{$role}] {$name} :: EXCEPTION ".get_class($e).': '.$e->getMessage();

                continue;
            }

            if (! in_array($status, $allowed, true)) {
                $this->problems[] = "[{$role}] {$name} ({$url}) :: HTTP {$status}";
            }
        }
    }
}
