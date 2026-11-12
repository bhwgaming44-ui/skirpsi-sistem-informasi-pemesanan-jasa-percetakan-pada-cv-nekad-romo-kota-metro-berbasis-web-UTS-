<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->pelanggan()
            ->withCount('pesanan')
            ->withSum(['pesanan as total_belanja' => fn ($builder) => $builder->where('status_pembayaran', 'Lunas')], 'grand_total')
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $search = $request->string('q')->trim()->toString();
            $query->where(function ($builder) use ($search): void {
                $builder->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%')
                    ->orWhere('no_hp', 'like', '%'.$search.'%');
            });
        }

        return view('admin.customers.index', [
            'customers' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function show(User $customer): View
    {
        abort_unless($customer->isPelanggan(), 404);

        $orders = Order::query()
            ->where('id_user', $customer->id_user)
            ->latest('tgl_pesan')
            ->paginate(10);

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $orders,
            'totalSpent' => (int) Order::query()
                ->where('id_user', $customer->id_user)
                ->where('status_pembayaran', 'Lunas')
                ->sum('grand_total'),
            'totalOrders' => Order::query()->where('id_user', $customer->id_user)->count(),
        ]);
    }
}
