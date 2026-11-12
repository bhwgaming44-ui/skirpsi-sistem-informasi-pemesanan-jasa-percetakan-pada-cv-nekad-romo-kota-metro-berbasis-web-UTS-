<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DesignDownloadController extends Controller
{
    public function __invoke(Order $order, OrderItem $detail): RedirectResponse|StreamedResponse
    {
        $user = auth()->user();

        abort_unless($detail->id_pesanan === $order->id_pesanan, 404);
        abort_unless($user->isStaff() || $order->id_user === $user->id_user, 403);

        if (blank($detail->file_desain) || ! Storage::disk('local')->exists($detail->file_desain)) {
            return back()->with('error', 'File desain untuk item ini tidak tersedia.');
        }

        return Storage::disk('local')->download($detail->file_desain, $detail->nama_file_desain ?? 'desain-pesanan');
    }
}
