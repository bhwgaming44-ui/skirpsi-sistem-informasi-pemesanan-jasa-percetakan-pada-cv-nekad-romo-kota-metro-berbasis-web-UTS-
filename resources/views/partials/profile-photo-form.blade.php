@php
    $currentPhoto = $user->fotoProfilUrl();
    $initialTab = old('drive_link') || $errors->has('drive_link') ? 'drive' : 'laptop';
    $uid = 'profile-photo-'.\Illuminate\Support\Str::random(8);
@endphp

<div class="space-y-4" x-data="{
        tab: '{{ $initialTab }}',
        preview: null,
        fileName: '',
        async pickFile(event) {
            const file = event.target.files[0];

            if (!file) {
                return;
            }

            this.fileName = file.name;

            if (file.size <= 2 * 1024 * 1024) {
                this.preview = URL.createObjectURL(file);

                return;
            }

            try {
                const bitmap = await createImageBitmap(file);
                const maxSide = 1200;
                const scale = Math.min(1, maxSide / Math.max(bitmap.width, bitmap.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(bitmap.width * scale);
                canvas.height = Math.round(bitmap.height * scale);
                const context = canvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);

                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));

                if (!blob) {
                    this.preview = URL.createObjectURL(file);

                    return;
                }

                const compressed = new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });

                if (typeof DataTransfer !== 'undefined') {
                    const transfer = new DataTransfer();
                    transfer.items.add(compressed);
                    event.target.files = transfer.files;
                }

                this.fileName = compressed.name;
                this.preview = URL.createObjectURL(compressed);
            } catch (error) {
                this.preview = URL.createObjectURL(file);
            }
        },
    }">
    <div class="flex items-start gap-4">
        <div class="relative shrink-0">
            <template x-if="preview">
                <img :src="preview" alt="Pratinjau foto profil" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-brand-100">
            </template>
            <template x-if="!preview">
                <div>
                    <x-user-avatar :user="$user" size="h-20 w-20" text="text-2xl" />
                </div>
            </template>
            <span class="absolute -bottom-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg ring-2 ring-white">
                <x-icon name="camera" class="h-3.5 w-3.5" />
            </span>
        </div>

        <div class="min-w-0 pt-1">
            <h3 class="font-display text-base font-bold text-slate-900">Foto Profil</h3>
            <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                Unggah dari album laptop atau ambil langsung dari Google Drive. Format JPG, PNG, atau WEBP maksimal 4 MB.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1">
        <button type="button" @click="tab = 'laptop'; $refs.driveInput.value = ''"
                class="flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition"
                :class="tab === 'laptop' ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
            <x-icon name="image" class="h-4 w-4" /> Dari Laptop
        </button>
        <button type="button" @click="tab = 'drive'; $refs.fileInput.value = ''; preview = null; fileName = ''"
                class="flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition"
                :class="tab === 'drive' ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
            <x-icon name="cloud" class="h-4 w-4" /> Google Drive
        </button>
    </div>

    <form method="POST" action="{{ route('profile-photo.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div x-show="tab === 'laptop'" x-cloak>
            <label for="{{ $uid }}-file"
                   class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 px-4 py-6 text-center transition hover:border-brand-300 hover:bg-brand-50/50">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-brand-600 shadow-sm">
                    <x-icon name="upload" class="h-5 w-5" />
                </span>
                <span class="text-xs font-bold text-slate-700" x-text="fileName || 'Pilih foto dari album laptop'"></span>
                <span class="text-[10px] text-slate-400">Klik untuk membuka file explorer</span>
                <input id="{{ $uid }}-file" x-ref="fileInput" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="sr-only"
                       @change="pickFile($event)">
            </label>
            <x-form-error name="foto" />
        </div>

        <div x-show="tab === 'drive'" x-cloak class="space-y-2">
            <label for="{{ $uid }}-drive" class="label">Link Google Drive</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="link" class="h-4 w-4" />
                </span>
                <input id="{{ $uid }}-drive" x-ref="driveInput" name="drive_link" type="text" inputmode="url" value="{{ old('drive_link') }}"
                       placeholder="https://drive.google.com/file/d/.../view?usp=sharing"
                       class="input !pl-10 @error('drive_link') !border-rose-300 !ring-rose-100 @enderror">
            </div>
            <p class="text-[10px] leading-relaxed text-slate-400">
                Buka file di Google Drive &rarr; <span class="font-semibold text-slate-500">Bagikan</span> &rarr; pilih
                <span class="font-semibold text-slate-500">Anyone with the link</span>, lalu salin link ke kolom ini.
            </p>
            <x-form-error name="drive_link" />
        </div>

        <button type="submit" class="btn-primary w-full">
            <x-icon name="check" class="h-4.5 w-4.5" /> Simpan Foto Profil
        </button>
    </form>

    @if ($currentPhoto)
        <form method="POST" action="{{ route('profile-photo.destroy') }}"
              onsubmit="return confirm('Hapus foto profil saat ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex w-full items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                <x-icon name="trash" class="h-4 w-4" /> Hapus foto profil
            </button>
        </form>
    @endif
</div>
