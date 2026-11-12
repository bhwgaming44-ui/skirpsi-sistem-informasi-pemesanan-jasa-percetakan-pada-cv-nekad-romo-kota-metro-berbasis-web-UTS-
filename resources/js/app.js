import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import ApexCharts from 'apexcharts';
import confetti from 'canvas-confetti';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;
window.Swal = Swal;
window.ApexCharts = ApexCharts;
window.confetti = confetti;

const rupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(Number(value) || 0);

const compactNumber = (value) => new Intl.NumberFormat('id-ID', {
    notation: 'compact',
    maximumFractionDigits: 1,
}).format(Number(value) || 0);

const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

window.rupiah = rupiah;

window.updateSavedCount = (count) => {
    const total = Number(count) || 0;

    document.querySelectorAll('[data-saved-badge]').forEach((badge) => {
        badge.textContent = String(total);
        badge.classList.toggle('hidden', total <= 0);
    });

    document.querySelectorAll('[data-saved-total]').forEach((element) => {
        element.textContent = String(total);
    });
};

document.addEventListener('alpine:init', () => {
    Alpine.data('priceCalculator', (config) => ({
        materialId: String(config.materialId),
        panjang: Number(config.panjang),
        lebar: Number(config.lebar),
        jumlah: Number(config.jumlah),
        finishingIds: (config.finishingIds ?? []).map(String),
        tipeDesain: config.tipeDesain ?? 'Punya Desain Sendiri',
        materials: config.materials,
        finishings: config.finishings,
        designFee: Number(config.designFee),
        satuan: config.satuan,
        minOrder: Number(config.minOrder),

        get material() {
            return this.materials.find((item) => String(item.id) === String(this.materialId)) ?? this.materials[0];
        },
        get hargaSatuan() {
            return Number(this.material?.harga ?? 0);
        },
        get luas() {
            return Math.max(Number(this.panjang) * Number(this.lebar), 0);
        },
        get hargaDasar() {
            return this.satuan === 'meter'
                ? Math.round(this.hargaSatuan * Math.max(this.luas, 0.25) * this.jumlah)
                : Math.round(this.hargaSatuan * this.jumlah);
        },
        get biayaFinishing() {
            return this.finishings
                .filter((item) => this.finishingIds.map(String).includes(String(item.id)))
                .reduce((total, item) => total + Number(item.biaya), 0) * this.jumlah;
        },
        get biayaDesain() {
            return this.tipeDesain === 'Jasa Desain Nekad Romo' ? this.designFee : 0;
        },
        get total() {
            return this.hargaDasar + this.biayaFinishing + this.biayaDesain;
        },
        get volume() {
            return this.satuan === 'meter' ? this.luas * this.jumlah : this.jumlah;
        },
        format(value) {
            return rupiah(value);
        },
        labelSatuan() {
            return this.satuan === 'meter'
                ? `${this.luas.toFixed(2)} m² × ${this.jumlah} pcs`
                : `${this.jumlah} ${this.satuan}`;
        },
    }));

    Alpine.data('clock', () => ({
        time: '',
        date: '',
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        },
        tick() {
            const now = new Date();
            this.time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        },
    }));

    Alpine.data('catalogFilter', (config) => ({
        endpoint: config.endpoint,
        search: config.search ?? '',
        sort: config.sort ?? 'terbaru',
        category: config.category ? String(config.category) : '',
        total: Number(config.total ?? 0),
        allIds: String(config.ids ?? '')
            .split(',')
            .filter((id) => id !== '')
            .map(Number),
        selectMode: false,
        selected: [],
        requestId: 0,
        controller: null,
        pagePath: window.location.pathname,

        get hasFilters() {
            return this.search.trim() !== '' || this.sort !== 'terbaru' || this.category !== '';
        },

        get allSelected() {
            return this.allIds.length > 0
                && this.allIds.every((id) => this.selected.includes(Number(id)));
        },

        load(page = 1, scroll = false) {
            this.requestId += 1;
            const requestId = this.requestId;

            this.controller?.abort();
            this.controller = new AbortController();

            const params = new URLSearchParams();
            const search = this.search.trim();

            if (search !== '') {
                params.set('q', search);
            }

            if (this.category !== '') {
                params.set('kategori', this.category);
            }

            if (this.sort !== 'terbaru') {
                params.set('sort', this.sort);
            }

            if (page > 1) {
                params.set('page', String(page));
            }

            fetch(`${this.endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                signal: this.controller.signal,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Respons filter tidak valid.');
                    }

                    return response.json();
                })
                .then((data) => {
                    if (requestId !== this.requestId) {
                        return;
                    }

                    this.total = Number(data.total ?? 0);

                    if (Array.isArray(data.ids)) {
                        this.allIds = data.ids.map(Number);
                        this.selected = this.selected.filter((id) => this.allIds.includes(id));
                    }

                    this.$refs.results.innerHTML = data.html;

                    this.$nextTick(() => {
                        this.$refs.results.querySelectorAll('.reveal').forEach((element) => {
                            element.style.transition = 'none';
                            element.classList.add('is-visible');
                        });
                    });

                    if (typeof data.url === 'string' && window.history?.replaceState) {
                        window.history.replaceState({}, '', data.url);
                    }

                    if (scroll) {
                        this.$refs.results.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                })
                .catch(() => {
                    // Abaikan kegagalan agar hasil terakhir tetap tampil tanpa indikator loading.
                });
        },

        setCategory(value) {
            const next = String(value);

            if (this.category === next) {
                return;
            }

            this.category = next;
            this.load(1);
        },

        clearSearch() {
            this.search = '';
            this.load(1);
            this.$nextTick(() => this.$refs.search?.focus());
        },

        resetFilters() {
            this.search = '';
            this.sort = 'terbaru';
            this.category = '';
            this.load(1);
        },

        toggleSelectMode() {
            this.selectMode = ! this.selectMode;

            if (! this.selectMode) {
                this.selected = [];
            }
        },

        isSelected(id) {
            return this.selected.includes(Number(id));
        },

        toggleSelected(id) {
            const value = Number(id);
            const index = this.selected.indexOf(value);

            if (index === -1) {
                this.selected.push(value);

                return;
            }

            this.selected.splice(index, 1);
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selected = [];

                return;
            }

            this.selected = this.allIds.map(Number);
        },

        onResultsClick(event) {
            const link = event.target.closest('a[href]');

            if (!link || !this.$refs.results.contains(link)) {
                return;
            }

            if (link.hasAttribute('data-reset-filter')) {
                event.preventDefault();
                this.resetFilters();

                return;
            }

            let url;

            try {
                url = new URL(link.href, window.location.origin);
            } catch (error) {
                return;
            }

            if (url.origin !== window.location.origin
                || url.pathname !== this.pagePath
                || !url.searchParams.has('page')) {
                return;
            }

            event.preventDefault();
            this.load(Number(url.searchParams.get('page')) || 1, true);
        },
    }));

    Alpine.data('checkoutDraft', (config) => ({
        metode: config.metode ?? 'Ambil di Toko',
        alamat: config.alamat ?? '',
        catatan: config.catatan ?? '',
        biayaKirim: Number(config.biayaKirim ?? 0),
        subtotal: Number(config.subtotal ?? 0),
        endpoint: config.endpoint ?? '',
        status: config.hasDraft ? 'saved' : 'idle',

        async save() {
            if (this.endpoint === '') {
                return;
            }

            this.status = 'saving';

            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        metode_pengambilan: this.metode,
                        alamat_pengiriman: this.alamat,
                        catatan: this.catatan,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Gagal menyimpan draft checkout.');
                }

                this.status = 'saved';
            } catch (error) {
                this.status = 'error';
            }
        },
    }));

    Alpine.data('savedProductButton', () => ({
        saved: false,
        busy: false,
        url: '',

        init() {
            this.saved = this.$el.dataset.saved === 'true';
            this.url = this.$el.dataset.url ?? '';
        },

        async toggle() {
            if (this.busy || this.url === '') {
                return;
            }

            this.busy = true;

            try {
                const response = await fetch(this.url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memperbarui produk tersimpan.');
                }

                const data = await response.json();

                this.saved = Boolean(data.saved);
                window.updateSavedCount?.(data.count);
                window.dispatchEvent(new CustomEvent('saved-products-changed', { detail: data }));
                window.showToast?.(data.saved ? 'success' : 'info', data.message ?? 'Produk tersimpan diperbarui.');
            } catch (error) {
                window.showToast?.('error', 'Gagal memperbarui produk tersimpan. Coba lagi.');
            } finally {
                this.busy = false;
            }
        },
    }));

    Alpine.data('savedProductsPage', (initialCount) => ({
        count: Number(initialCount) || 0,

        handle(event) {
            const detail = event.detail ?? {};

            if (detail.saved) {
                this.count = Number(detail.count) || this.count;

                return;
            }

            const item = this.$el.querySelector(`[data-saved-item="${detail.product_id}"]`);

            if (item) {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.97)';
                setTimeout(() => item.remove(), 200);
            }

            this.count = Number(detail.count) || 0;

            if (this.count <= 0) {
                setTimeout(() => window.location.reload(), 280);
            }
        },
    }));
});

window.showToast = (icon, title) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 3800,
        timerProgressBar: true,
        customClass: { popup: 'swal-toast-neo' },
        showClass: { popup: 'swal-pop-in' },
        hideClass: { popup: 'swal-pop-out' },
    });
};

window.confirmAction = async (options = {}) => {
    const result = await Swal.fire({
        icon: options.icon ?? 'warning',
        title: options.title ?? 'Yakin ingin melanjutkan?',
        html: options.html ?? '',
        text: options.text ?? '',
        showCancelButton: true,
        confirmButtonText: options.confirmText ?? 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: { popup: 'swal-neo' },
        showClass: { popup: 'swal-pop-in' },
        hideClass: { popup: 'swal-pop-out' },
    });

    return result.isConfirmed;
};

const buildChart = (element) => {
    const config = JSON.parse(element.dataset.chart ?? '{}');
    const currency = config.currency !== false;
    const type = config.type ?? 'area';
    const labels = config.labels ?? [];
    const values = config.values ?? [];
    const height = Number(config.height ?? 300);
    const palette = config.colors ?? ['#6d5df6', '#22d3ee', '#f59e0b', '#10b981', '#f43f5e', '#8b5cf6', '#0ea5e9', '#84cc16'];
    const valueFormatter = (value) => currency ? rupiah(value) : new Intl.NumberFormat('id-ID').format(value);

    const base = {
        chart: {
            height,
            fontFamily: 'Plus Jakarta Sans Variable, sans-serif',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 750 },
            parentHeightOffset: 0,
        },
        colors: palette,
        dataLabels: { enabled: false },
        legend: {
            show: config.legend ?? type === 'donut',
            position: 'bottom',
            horizontalAlign: 'left',
            fontSize: '12px',
            markers: { size: 5 },
            itemMargin: { horizontal: 10, vertical: 4 },
        },
        tooltip: {
            theme: 'light',
            y: { formatter: valueFormatter },
        },
        grid: {
            borderColor: '#eef1f8',
            strokeDashArray: 4,
            padding: { left: 8, right: 8 },
        },
    };

    if (type === 'donut') {
        return new ApexCharts(element, {
            ...base,
            chart: { ...base.chart, type: 'donut' },
            series: values,
            labels,
            stroke: { width: 0 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '74%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: config.totalLabel ?? 'Total',
                                fontSize: '13px',
                                color: '#64748b',
                                formatter: () => currency ? compactNumber(values.reduce((a, b) => a + Number(b), 0)) : values.reduce((a, b) => a + Number(b), 0),
                            },
                            value: {
                                fontFamily: 'Sora Variable, sans-serif',
                                fontSize: '22px',
                                fontWeight: 700,
                                color: '#15152b',
                                formatter: (value) => currency ? compactNumber(value) : value,
                            },
                        },
                    },
                },
            },
        });
    }

    if (type === 'bar' && config.horizontal) {
        return new ApexCharts(element, {
            ...base,
            chart: { ...base.chart, type: 'bar' },
            series: [{ name: config.name ?? 'Nilai', data: values }],
            xaxis: {
                categories: labels,
                labels: {
                    style: { colors: '#94a3b8', fontSize: '11px' },
                    formatter: (value) => currency ? compactNumber(value) : value,
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { colors: '#475569', fontSize: '11px' },
                    maxWidth: 170,
                },
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 8,
                    barHeight: '55%',
                    distributed: true,
                },
            },
            legend: { show: false },
            tooltip: { ...base.tooltip, y: { formatter: valueFormatter } },
        });
    }

    if (type === 'bar') {
        return new ApexCharts(element, {
            ...base,
            chart: { ...base.chart, type: 'bar' },
            series: [{ name: config.name ?? 'Nilai', data: values }],
            xaxis: {
                categories: labels,
                labels: { style: { colors: '#94a3b8', fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { colors: '#94a3b8', fontSize: '11px' },
                    formatter: (value) => currency ? compactNumber(value) : value,
                },
            },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '45%',
                    distributed: config.distributed ?? false,
                },
            },
            legend: { show: false },
        });
    }

    return new ApexCharts(element, {
        ...base,
        chart: { ...base.chart, type },
        series: [{ name: config.name ?? 'Pendapatan', data: values }],
        stroke: {
            curve: 'smooth',
            width: type === 'area' ? 3 : 0,
        },
        fill: type === 'area' ? {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.03,
                stops: [0, 95, 100],
            },
        } : { opacity: 1 },
        xaxis: {
            categories: labels,
            labels: {
                style: { colors: '#94a3b8', fontSize: '11px' },
                rotate: -35,
                rotateAlways: labels.length > 12,
                hideOverlappingLabels: true,
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
            tooltip: { enabled: false },
        },
        yaxis: {
            labels: {
                style: { colors: '#94a3b8', fontSize: '11px' },
                formatter: (value) => currency ? compactNumber(value) : value,
            },
        },
        markers: {
            size: values.length > 20 ? 0 : 3,
            strokeWidth: 2,
            hover: { size: 5 },
        },
    });
};

const initCharts = () => {
    document.querySelectorAll('[data-chart]').forEach((element) => {
        if (element.dataset.chartReady === '1') {
            return;
        }

        element.dataset.chartReady = '1';

        try {
            buildChart(element).render();
        } catch (error) {
            console.warn('Gagal merender chart', error);
        }
    });
};

const initReveal = () => {
    const elements = document.querySelectorAll('.reveal');

    if (!('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    elements.forEach((element, index) => {
        element.style.transitionDelay = `${Math.min(index % 4, 3) * 80}ms`;
        observer.observe(element);
    });
};

const animateCounters = () => {
    document.querySelectorAll('[data-counter]').forEach((element) => {
        if (element.dataset.counterReady === '1') {
            return;
        }

        element.dataset.counterReady = '1';
        const target = Number(element.dataset.counter);
        const format = element.dataset.format ?? 'number';
        const duration = 1100;
        const start = performance.now();

        const frame = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = target * eased;
            const text = format === 'currency'
                ? rupiah(current)
                : format === 'percent'
                    ? `${Math.round(current)}%`
                    : new Intl.NumberFormat('id-ID').format(Math.round(current));

            element.textContent = text;

            if (progress < 1) {
                requestAnimationFrame(frame);
            }
        };

        requestAnimationFrame(frame);
    });
};

const initFlash = () => {
    const success = document.querySelector('meta[name="flash-success"]')?.content;
    const error = document.querySelector('meta[name="flash-error"]')?.content;
    const warning = document.querySelector('meta[name="flash-warning"]')?.content;
    const info = document.querySelector('meta[name="flash-info"]')?.content;

    if (success) {
        showToast('success', success);
    }

    if (error) {
        showToast('error', error);
    }

    if (warning) {
        showToast('warning', warning);
    }

    if (info) {
        showToast('info', info);
    }
};

const initConfirmations = () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            if (form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();

            const confirmed = await confirmAction({
                title: form.dataset.confirmTitle ?? 'Konfirmasi tindakan',
                text: form.dataset.confirm ?? 'Tindakan ini tidak dapat dibatalkan.',
                confirmText: form.dataset.confirmButton ?? 'Ya, lanjutkan',
                icon: form.dataset.confirmIcon ?? 'warning',
            });

            if (confirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    });
};

const initWelcome = () => {
    const panel = document.querySelector('[data-welcome]');

    if (!panel) {
        return;
    }

    const key = `welcome:${panel.dataset.welcome}`;

    if (sessionStorage.getItem(key) === '1') {
        return;
    }

    sessionStorage.setItem(key, '1');

    setTimeout(() => {
        Swal.fire({
            title: panel.dataset.welcomeTitle ?? 'Selamat datang kembali!',
            html: panel.innerHTML,
            width: 560,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'swal-neo swal-welcome' },
            showClass: { popup: 'swal-pop-in' },
            hideClass: { popup: 'swal-pop-out' },
            didOpen: () => {
                confetti({
                    particleCount: 90,
                    spread: 80,
                    origin: { y: 0.28 },
                    colors: ['#6d5df6', '#22d3ee', '#fbbf24', '#f472b6'],
                    disableForReducedMotion: true,
                });
            },
        });
    }, 450);
};

const initPaymentCelebration = () => {
    const trigger = document.querySelector('[data-payment-success]');

    if (!trigger) {
        return;
    }

    const key = `paid:${trigger.dataset.orderCode}`;

    if (sessionStorage.getItem(key) === '1') {
        return;
    }

    sessionStorage.setItem(key, '1');

    setTimeout(() => {
        confetti({
            particleCount: 160,
            spread: 100,
            origin: { y: 0.35 },
            colors: ['#6d5df6', '#22d3ee', '#34d399', '#fbbf24'],
            disableForReducedMotion: true,
        });

        Swal.fire({
            icon: 'success',
            title: 'Pembayaran Berhasil! 🎉',
            html: `
                <p class="text-slate-500">Pesanan <b>${escapeHtml(trigger.dataset.orderCode)}</b> sudah lunas dan otomatis masuk antrean produksi.</p>
                <div class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    Estimasi selesai: ${escapeHtml(trigger.dataset.estimate ?? '-')}
                </div>
            `,
            confirmButtonText: 'Lihat Invoice',
            showCancelButton: true,
            cancelButtonText: 'Nanti saja',
            reverseButtons: true,
            customClass: { popup: 'swal-neo' },
            showClass: { popup: 'swal-pop-in' },
            hideClass: { popup: 'swal-pop-out' },
        }).then((result) => {
            if (result.isConfirmed && trigger.dataset.invoiceUrl) {
                window.location.href = trigger.dataset.invoiceUrl;
            }
        });
    }, 500);
};

const initPaymentPolling = () => {
    const holder = document.querySelector('[data-payment-poll]');

    if (!holder) {
        return;
    }

    setInterval(async () => {
        try {
            const response = await fetch(holder.dataset.paymentPoll, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (data.paid) {
                window.location.reload();
            }
        } catch (error) {
            // Diamkan jika koneksi sedang tidak stabil.
        }
    }, 9000);
};

const initAdminNotifications = () => {
    const holder = document.querySelector('[data-admin-notify]');

    if (!holder) {
        return;
    }

    let lastCount = Number(sessionStorage.getItem('admin-order-count') ?? holder.dataset.count ?? 0);
    sessionStorage.setItem('admin-order-count', String(lastCount));

    const poll = async () => {
        try {
            const response = await fetch(holder.dataset.adminNotify, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (Number(data.count) > lastCount) {
                const list = (data.latest ?? []).map((order) => `
                    <a href="${escapeHtml(order.url)}" class="mt-2 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left transition hover:border-brand-300 hover:bg-brand-50">
                        <span>
                            <span class="block text-sm font-bold text-slate-800">${escapeHtml(order.kode)}</span>
                            <span class="block text-xs text-slate-500">${escapeHtml(order.nama)}</span>
                        </span>
                        <span class="text-sm font-bold text-brand-600">${rupiah(order.total)}</span>
                    </a>
                `).join('');

                Swal.fire({
                    icon: 'info',
                    title: 'Pesanan baru masuk! 🛎️',
                    html: `<p class="text-slate-500">Ada pembayaran terverifikasi dan siap masuk antrean cetak.</p><div class="mt-3">${list}</div>`,
                    confirmButtonText: 'Buka Antrean',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup',
                    reverseButtons: true,
                    customClass: { popup: 'swal-neo' },
                    showClass: { popup: 'swal-pop-in' },
                    hideClass: { popup: 'swal-pop-out' },
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = holder.dataset.productionUrl;
                    }
                });

                confetti({
                    particleCount: 70,
                    spread: 70,
                    origin: { y: 0.2 },
                    colors: ['#6d5df6', '#22d3ee', '#fbbf24'],
                    disableForReducedMotion: true,
                });
            }

            lastCount = Number(data.count);
            sessionStorage.setItem('admin-order-count', String(lastCount));
        } catch (error) {
            // Abaikan error polling agar UI tetap tenang.
        }
    };

    setInterval(poll, 20000);
};

const initCopyButtons = () => {
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
                showToast('success', 'Berhasil disalin ke clipboard.');
            } catch (error) {
                showToast('error', 'Browser menolak akses clipboard.');
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initFlash();
    initCharts();
    initReveal();
    animateCounters();
    initConfirmations();
    initWelcome();
    initPaymentCelebration();
    initPaymentPolling();
    initAdminNotifications();
    initCopyButtons();
});

Alpine.start();
