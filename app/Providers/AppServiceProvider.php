<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Services\CartService;
use App\Services\SavedProductService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureUrls();

        View::composer([
            'layouts.app',
            'layouts.dashboard',
            'layouts.auth',
            'admin.orders.show',
            'catalog.show',
            'customer.orders.show',
            'payment.show',
            'tracking.index',
        ], function ($view): void {
            $view->with([
                'storeSetting' => StoreSetting::current(),
                'cartCount' => app(CartService::class)->count(),
            ]);
        });

        View::composer('layouts.dashboard', function ($view): void {
            $queueCount = 0;
            $newOrderCount = 0;

            if (auth()->check() && auth()->user()->isAdmin()) {
                $queueCount = Order::query()
                    ->whereIn('status_pesanan', [
                        Order::STATUS_ANTREAN,
                        Order::STATUS_CETAK,
                        Order::STATUS_SELESAI_CETAK,
                    ])
                    ->count();

                $newOrderCount = Order::query()
                    ->where('status_pesanan', Order::STATUS_ANTREAN)
                    ->count();
            }

            $view->with([
                'sidebarQueueCount' => $queueCount,
                'newOrderCount' => $newOrderCount,
            ]);
        });

        View::composer(['layouts.app', 'layouts.dashboard'], function ($view): void {
            $view->with('savedCount', app(SavedProductService::class)->count());
        });
    }

    /**
     * Force HTTPS for every generated URL when the application runs in production.
     *
     * Falls back to the Vercel system domain when APP_URL is still pointing to localhost,
     * so CSS/JS assets are not blocked as mixed content.
     */
    private function configureUrls(): void
    {
        if (! $this->app->environment('production')) {
            return;
        }

        URL::forceScheme('https');

        $configuredHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $vercelHost = rtrim((string) (env('VERCEL_PROJECT_PRODUCTION_URL') ?: env('VERCEL_URL')), '/');

        if ($vercelHost !== '' && ! str_starts_with($vercelHost, 'http')
            && in_array($configuredHost, [null, 'localhost', '127.0.0.1'], true)) {
            URL::forceRootUrl('https://'.$vercelHost);
        }
    }
}
