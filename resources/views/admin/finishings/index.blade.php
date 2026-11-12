@extends('layouts.dashboard')

@section('title', 'Master Finishing')

@section('content')
    <div class="space-y-5" x-data="{
        open: false,
        editId: null,
        form: { nama_finishing: '', biaya_default: 0 },
        create() {
            this.editId = null;
            this.form = { nama_finishing: '', biaya_default: 0 };
            this.open = true;
        },
        edit(id, nama, biaya) {
            this.editId = id;
            this.form = { nama_finishing: nama, biaya_default: biaya };
            this.open = true;
        },
    }">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Master Finishing</h2>
                <p class="mt-0.5 text-sm text-slate-500">Opsi finishing tambahan beserta biaya default per satuan.</p>
            </div>
            <button type="button" @click="create()" class="btn-primary btn-sm">
                <x-icon name="plus" class="h-4 w-4" /> Tambah finishing
            </button>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <x-icon name="scissors" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total opsi</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">{{ $finishings->total() }} finishing</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-icon name="tag" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Biaya terendah</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">
                        Rp {{ number_format((int) ($finishings->min('biaya_default') ?? 0), 0, ',', '.') }}
                    </p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-icon name="layers" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Biaya tertinggi</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">
                        Rp {{ number_format((int) ($finishings->max('biaya_default') ?? 0), 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            @if ($finishings->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="scissors" title="Belum ada finishing" message="Tambahkan opsi finishing seperti laminasi, mata ayam, atau jilid.">
                        <button type="button" @click="create()" class="btn-primary btn-sm mt-1">
                            <x-icon name="plus" class="h-4 w-4" /> Tambah finishing
                        </button>
                    </x-empty-state>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-modern min-w-[620px]">
                        <thead>
                            <tr>
                                <th>Nama Finishing</th>
                                <th>Biaya Default</th>
                                <th class="text-center">Dipakai Produk</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($finishings as $finishing)
                                <tr>
                                    <td>
                                        <p class="text-sm font-bold text-slate-800">{{ $finishing->nama_finishing }}</p>
                                        <p class="text-[10px] text-slate-400">#{{ $finishing->id_finishing }}</p>
                                    </td>
                                    <td>
                                        <span class="badge bg-brand-50 text-brand-700">
                                            Rp {{ number_format((float) $finishing->biaya_default, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $finishing->produk_count > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $finishing->produk_count }} produk
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                    @click="edit({{ $finishing->id_finishing }}, @js($finishing->nama_finishing), @js((float) $finishing->biaya_default))"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Edit">
                                                <x-icon name="edit" class="h-4 w-4" />
                                            </button>
                                            <form method="POST" action="{{ route('admin.finishings.destroy', $finishing) }}"
                                                  data-confirm="Finishing {{ $finishing->nama_finishing }} akan dihapus permanen."
                                                  data-confirm-button="Ya, hapus" data-confirm-title="Hapus finishing?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($finishings->hasPages())
            <div>{{ $finishings->links() }}</div>
        @endif

        {{-- Modal --}}
        <div x-cloak x-show="open" class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-ink-950/60 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open" x-transition.scale.origin.center
                 class="relative w-full max-w-md overflow-hidden rounded-3xl border border-white/60 bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900" x-text="editId ? 'Edit finishing' : 'Tambah finishing'">Tambah finishing</h3>
                        <p class="text-[11px] text-slate-400">Biaya dihitung per satuan pesanan.</p>
                    </div>
                    <button type="button" @click="open = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-white hover:text-slate-700">
                        <x-icon name="close" class="h-4.5 w-4.5" />
                    </button>
                </div>

                <form method="POST" :action="editId ? '{{ url('admin/finishing') }}/' + editId : '{{ route('admin.finishings.store') }}'">
                    @csrf
                    <input type="hidden" name="_method" :value="editId ? 'PUT' : 'POST'">

                    <div class="space-y-4 p-6">
                        <div>
                            <label for="nama_finishing" class="label">Nama Finishing</label>
                            <input id="nama_finishing" name="nama_finishing" type="text" x-model="form.nama_finishing" required maxlength="100"
                                   placeholder="Contoh: Laminasi Doff" class="input">
                        </div>

                        <div>
                            <label for="biaya_default" class="label">Biaya Default (Rp)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input id="biaya_default" name="biaya_default" type="number" min="0" step="500" x-model.number="form.biaya_default" required
                                       class="input !pl-10">
                            </div>
                            <p class="mt-1.5 text-[11px] text-slate-400">Biaya ini akan dikalikan jumlah pesanan pelanggan.</p>
                        </div>

                        <div class="rounded-xl border border-brand-100 bg-brand-50/60 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-brand-700">Simulasi</p>
                            <p class="mt-0.5 text-xs text-brand-700/80">
                                Pesanan 100 pcs → tambahan
                                <span class="font-bold" x-text="rupiah((Number(form.biaya_default) || 0) * 100)"></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                        <button type="button" @click="open = false" class="btn-white btn-sm">Batal</button>
                        <button type="submit" class="btn-primary btn-sm">
                            <x-icon name="check" class="h-4 w-4" />
                            <span x-text="editId ? 'Simpan perubahan' : 'Tambah finishing'">Tambah finishing</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
