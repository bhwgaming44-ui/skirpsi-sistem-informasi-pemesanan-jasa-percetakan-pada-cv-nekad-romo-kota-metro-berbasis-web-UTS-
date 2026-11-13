<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AppUrlSanitizationTest extends TestCase
{
    public function test_app_url_dibersihkan_dari_baris_baru(): void
    {
        config(['app.url' => "https://contoh.vercel.app\n"]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('https://contoh.vercel.app', config('app.url'));
    }

    public function test_header_location_tidak_mengandung_baris_baru(): void
    {
        Route::get('/uji-redirect', fn () => redirect("https://contoh.vercel.app/masuk\n"));

        $response = $this->get('/uji-redirect');

        $response->assertRedirect('https://contoh.vercel.app/masuk');
        $this->assertStringNotContainsString("\n", (string) $response->headers->get('Location'));
    }
}
