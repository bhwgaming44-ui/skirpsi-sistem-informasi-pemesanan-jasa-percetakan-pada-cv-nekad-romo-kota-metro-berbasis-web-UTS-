@props(['user'])

<div x-data="{
        photoOpen: @js(! request()->routeIs('customer.profile.edit') && ($errors->has('foto') || $errors->has('drive_link'))),
        openPhoto() {
            this.photoOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closePhoto() {
            this.photoOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
    }"
     @open-profile-photo.window="openPhoto()"
     @keydown.escape.window="closePhoto()"
     x-init="if (photoOpen) document.body.classList.add('overflow-hidden')">
    <div x-cloak x-show="photoOpen" class="fixed inset-0 z-[70] flex items-end justify-center p-0 sm:items-center sm:p-4" role="dialog" aria-modal="true" aria-label="Ubah foto profil">
        <div x-show="photoOpen" x-transition.opacity @click="closePhoto()"
             class="absolute inset-0 bg-ink-950/60 backdrop-blur-sm"></div>

        <div x-show="photoOpen" x-transition.scale.origin.bottom
             class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:max-w-lg sm:rounded-3xl">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-brand-500">Akun Saya</p>
                    <h2 class="font-display text-lg font-extrabold text-slate-900">Ubah Foto Profil</h2>
                </div>
                <button type="button" @click="closePhoto()"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                        aria-label="Tutup">
                    <x-icon name="close" class="h-4.5 w-4.5" />
                </button>
            </div>

            @include('partials.profile-photo-form', ['user' => $user])
        </div>
    </div>
</div>
