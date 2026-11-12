<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finishing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FinishingController extends Controller
{
    public function index(): View
    {
        return view('admin.finishings.index', [
            'finishings' => Finishing::query()
                ->withCount('produk')
                ->orderBy('id_finishing')
                ->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Finishing::query()->create($this->validated($request));

        return back()->with('success', 'Opsi finishing berhasil ditambahkan.');
    }

    public function update(Request $request, Finishing $finishing): RedirectResponse
    {
        $finishing->update($this->validated($request));

        return back()->with('success', 'Opsi finishing berhasil diperbarui.');
    }

    public function destroy(Finishing $finishing): RedirectResponse
    {
        if ($finishing->produk()->exists()) {
            return back()->with('error', 'Finishing masih digunakan oleh produk. Lepaskan dari produk terlebih dahulu.');
        }

        $finishing->delete();

        return back()->with('success', 'Opsi finishing berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'nama_finishing' => ['required', 'string', 'max:100'],
            'biaya_default' => ['required', 'numeric', 'min:0', 'max:100000000'],
        ]);
    }
}
