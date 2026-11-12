<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request): View
    {
        $order = null;
        $code = trim($request->string('kode')->toString());

        if ($code !== '') {
            $order = Order::query()
                ->with(['detail.finishing', 'riwayat'])
                ->where('kode_pesanan', $code)
                ->first();

            if ($order === null) {
                $request->session()->flash('error', 'Kode pesanan "'.$code.'" tidak ditemukan. Periksa kembali kode dari invoice Anda.');
            }
        }

        return view('tracking.index', [
            'order' => $order,
            'code' => $code,
        ]);
    }

    public function show(string $kode): View
    {
        $order = Order::query()
            ->with(['detail.finishing', 'riwayat'])
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        return view('tracking.show', [
            'order' => $order,
        ]);
    }
}
