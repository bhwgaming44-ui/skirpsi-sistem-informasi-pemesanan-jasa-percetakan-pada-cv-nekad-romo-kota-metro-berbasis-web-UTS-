<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentationController as AdminDocumentationController;
use App\Http\Controllers\Admin\FinishingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\DesignDownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Pimpinan\DashboardController as PimpinanDashboardController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavedProductController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/katalog/filter', [CatalogController::class, 'filter'])->name('catalog.filter');
Route::get('/katalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/lacak', [TrackingController::class, 'index'])->name('tracking.index');
Route::get('/lacak/{kode}', [TrackingController::class, 'show'])->name('tracking.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
});

Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::post('/keranjang/pilih-banyak', [CartController::class, 'bulkStore'])->name('cart.bulk');
Route::delete('/keranjang/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/keranjang', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/produk-tersimpan', [SavedProductController::class, 'index'])->name('saved.index');
Route::post('/produk-tersimpan/{product}', [SavedProductController::class, 'toggle'])->name('saved.toggle');

Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

Route::middleware('auth')->group(function (): void {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/draft', [CheckoutController::class, 'saveDraft'])->name('checkout.draft');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pembayaran/{order}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/pembayaran/{order}/snap-token', [PaymentController::class, 'snapToken'])->name('payments.snap-token');
    Route::post('/pembayaran/{order}/simulasi', [PaymentController::class, 'simulate'])->name('payments.simulate');
    Route::get('/pembayaran/{order}/selesai', [PaymentController::class, 'finish'])->name('payments.finish');
    Route::get('/pembayaran/{order}/status', [PaymentController::class, 'status'])->name('payments.status');

    Route::get('/pesanan/{order}/invoice', [CustomerOrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/pesanan/{order}/desain/{detail}', DesignDownloadController::class)->name('orders.design');

    Route::prefix('akun')->name('customer.')->group(function (): void {
        Route::get('/', CustomerDashboardController::class)->name('dashboard');
        Route::get('/pesanan', [CustomerOrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
        Route::post('/pesanan/{order}/batal', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    });

    Route::post('/foto-profil', [ProfilePhotoController::class, 'update'])->name('profile-photo.update');
    Route::delete('/foto-profil', [ProfilePhotoController::class, 'destroy'])->name('profile-photo.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/notifikasi', NotificationController::class)->name('notifications');
    Route::get('/antrean-produksi', [AdminOrderController::class, 'board'])->name('production.board');

    Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/pesanan/{order}/lanjut', [AdminOrderController::class, 'advance'])->name('orders.advance');
    Route::get('/pesanan/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

    Route::resource('produk', ProductController::class)
        ->except(['show'])
        ->parameters(['produk' => 'product'])
        ->names('products');
    Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/finishing', [FinishingController::class, 'index'])->name('finishings.index');
    Route::post('/finishing', [FinishingController::class, 'store'])->name('finishings.store');
    Route::put('/finishing/{finishing}', [FinishingController::class, 'update'])->name('finishings.update');
    Route::delete('/finishing/{finishing}', [FinishingController::class, 'destroy'])->name('finishings.destroy');

    Route::get('/pelanggan', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/pelanggan/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');

    Route::get('/pengguna', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/pengguna/{user}/lihat-password', [AdminUserController::class, 'revealPassword'])->name('users.password.reveal');
    Route::post('/pengguna/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.password.reset');

    Route::get('/dokumentasi-sistem', AdminDocumentationController::class)->name('documentation');

    Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::put('/pembayaran/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');
    Route::post('/pembayaran/{payment}/aktifkan', [AdminPaymentController::class, 'activate'])->name('payments.activate');
    Route::post('/pembayaran/{payment}/test', [AdminPaymentController::class, 'test'])->name('payments.test');

    Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/pengaturan/toko', [SettingController::class, 'updateStore'])->name('settings.store');
    Route::put('/pengaturan/payment', [SettingController::class, 'updatePayment'])->name('settings.payment');
});

Route::middleware(['auth', 'role:pimpinan'])->prefix('pimpinan')->name('pimpinan.')->group(function (): void {
    Route::get('/', PimpinanDashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'role:admin,pimpinan'])->group(function (): void {
    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/laporan/cetak', [ReportController::class, 'print'])->name('reports.print');
});

Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json(['message' => 'Halaman tidak ditemukan.'], 404);
    }

    return response()->view('errors.404', ['status' => 404], 404);
});
