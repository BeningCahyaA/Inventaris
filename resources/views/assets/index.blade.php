<x-app-layout>
    <x-slot name="title">Aset</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aset') }}
        </h2>
    </x-slot>

    <div class="asset-page" :class="{ 'asset-has-selection': selectedIds.length > 0 }" x-data="{
        showAssetModal: false,
        showEditScanner: false,
        showFilters: {{ (request('status') || request('category') || request('date_from') || request('date_to') || request('warranty_state') || request('loan_state')) ? 'true' : 'false' }},
        searchQuery: '{{ request('search') }}',
        filters: {
            status: '{{ request('status') }}',
            category: '{{ request('category') }}',
            date_from: '{{ request('date_from') }}',
            date_to: '{{ request('date_to') }}',
            warranty_state: '{{ request('warranty_state') }}',
            loan_state: '{{ request('loan_state') }}',
        },
        selectedIds: [],
        asset: {},
        _editTag: null,
        _editUrl: null,
        _scanStatus: 'Scan barcode aset untuk verifikasi sebelum edit.',

        init() { this.initBulkCheckboxes(); },

        buildUrl() {
            let url = new URL(window.location.href);
            const p = { search: this.searchQuery, status: this.filters.status, category: this.filters.category, date_from: this.filters.date_from, date_to: this.filters.date_to, warranty_state: this.filters.warranty_state, loan_state: this.filters.loan_state };
            Object.entries(p).forEach(([k, v]) => { if (v) url.searchParams.set(k, v); else url.searchParams.delete(k); });
            url.searchParams.delete('page');
            return url;
        },
        performSearch() { const url = this.buildUrl(); window.history.pushState({}, '', url); this.fetchTable(url.toString()); },
        fetchTable(url) {
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => { this.$refs.tableContainer.innerHTML = html; this.initBulkCheckboxes(); })
                .catch(e => console.error('Error:', e));
        },
        resetFilters() { this.filters = { status: '', category: '', date_from: '', date_to: '', warranty_state: '', loan_state: '' }; this.performSearch(); },
        handlePaginationClick(event) {
            const link = event.target.closest('.pagination a');
            if (!link) return;
            event.preventDefault();
            const pageUrl = link.href;
            window.history.pushState({}, '', pageUrl);
            fetch(pageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => { this.$refs.tableContainer.innerHTML = html; this.$refs.tableContainer.scrollIntoView({ behavior: 'smooth' }); this.initBulkCheckboxes(); });
        },
        exportCsv() {
            const url = new URL('{{ route('assets.export-csv') }}', window.location.origin);
            if (this.searchQuery) url.searchParams.set('search', this.searchQuery);
            if (this.filters.status) url.searchParams.set('status', this.filters.status);
            if (this.filters.category) url.searchParams.set('category', this.filters.category);
            if (this.filters.date_from) url.searchParams.set('date_from', this.filters.date_from);
            if (this.filters.date_to) url.searchParams.set('date_to', this.filters.date_to);
            window.location.href = url.toString();
        },
        initBulkCheckboxes() {
            const container = this.$refs.tableContainer;
            if (!container) return;
            const cbs = container.querySelectorAll('.asset-checkbox');
            const sa = container.querySelector('#select-all-page');
            cbs.forEach(cb => {
                if (this.selectedIds.includes(cb.value)) cb.checked = true;
                cb.addEventListener('change', () => {
                    if (cb.checked) { if (!this.selectedIds.includes(cb.value)) this.selectedIds.push(cb.value); }
                    else { this.selectedIds = this.selectedIds.filter(id => id !== cb.value); }
                });
            });
            if (sa) {
                sa.addEventListener('change', () => {
                    cbs.forEach(cb => {
                        cb.checked = sa.checked;
                        if (sa.checked) { if (!this.selectedIds.includes(cb.value)) this.selectedIds.push(cb.value); }
                        else { this.selectedIds = this.selectedIds.filter(id => id !== cb.value); }
                    });
                });
            }
        },
        clearSelection() {
            this.selectedIds = [];
            const c = this.$refs.tableContainer;
            if (c) { c.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = false); const sa = c.querySelector('#select-all-page'); if (sa) sa.checked = false; }
        },
        confirmBulkDelete(event) {
            const form = event.target;
            Swal.fire({ title: 'Hapus Massal?', text: 'Anda akan menghapus ' + this.selectedIds.length + ' aset yang dipilih. Tindakan ini memerlukan persetujuan Super Admin.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#9ca3af', confirmButtonText: 'Hapus', cancelButtonText: 'Batal', reverseButtons: true, focusCancel: true }).then((result) => { if (result.isConfirmed) form.submit(); });
        },

        {{-- ===== Edit Scanner (Barcode) ===== --}}
        openEditScanner(expectedTag, editUrl) {
            this._editTag = expectedTag; this._editUrl = editUrl;
            this._scanStatus = 'Scan barcode aset untuk verifikasi sebelum edit.';
            this.showEditScanner = true;
            this.$nextTick(() => this.startEditScanner());
        },
        startEditScanner() {
            this._scanStatus = 'Membuka kamera...';
            BarcodeCam.start(this.$refs.editVideo, (text) => this.onEditScanSuccess(text))
                .then(() => { this._scanStatus = 'Arahkan kamera ke barcode...'; })
                .catch((e) => { console.error('Scanner error:', e); this._scanStatus = '❌ ' + BarcodeCam.errorMessage(e); });
        },
        stopEditScanner() {
            BarcodeCam.stop();
            this._scanStatus = 'Scan barcode aset untuk verifikasi sebelum edit.';
        },
        onEditScanSuccess(text) {
            const code = String(text || '').trim();
            if (code === String(this._editTag || '').trim()) {
                this._scanStatus = '✅ Barcode cocok! Mengalihkan...';
                const target = this._editUrl;
                this.stopEditScanner();
                this.showEditScanner = false;
                window.location.href = target;
            } else {
                BarcodeCam.pauseFor(2000);
                this._scanStatus = '❌ Barcode tidak cocok! Pastikan Anda scan aset yang benar.';
                setTimeout(() => { if (this.showEditScanner) this._scanStatus = 'Arahkan kamera ke barcode...'; }, 2000);
            }
        },
        closeEditScanner() { this.stopEditScanner(); this.showEditScanner = false; this._editTag = null; this._editUrl = null; },
    }" @keydown.window.escape="showAssetModal = false; closeEditScanner(); showFilters = false">
        <div class="max-w-7xl mx-auto">

<div class="mb-5"><h1 class="text-2xl font-bold text-gray-900">Inventaris aset</h1><p class="mt-2 text-sm text-gray-600">Cari aset, periksa status, dan pilih Detail untuk melihat informasi lengkap. Centang aset untuk tindakan massal.</p></div><div class="bg-white p-4 sm:p-5 rounded-xl mb-5 border border-gray-200">
                {{-- Row 1: Search + Action Buttons --}}
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                    <div class="flex-1 flex items-center gap-2 relative">
                        {{-- Funnel Filter Button & Dropdown --}}
                        <div class="relative">
                            <button type="button" @click.stop="showFilters = !showFilters"
                                class="inline-flex items-center justify-center p-2.5 border rounded-lg transition shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 shrink-0"
                                :class="showFilters ? 'bg-purple-50 border-purple-200 text-purple-600' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-purple-600'"
                                title="Filter Data" :aria-expanded="showFilters"><span class="mr-2 text-sm font-semibold">Filter</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            </button>

                            {{-- Floating Full Filter Box --}}
                            <div x-show="showFilters" x-transition.opacity x-cloak @click.outside="showFilters = false" @click.stop
                                class="absolute left-0 top-full mt-2 w-[calc(100vw-40px)] sm:w-[500px] z-50 bg-white border border-gray-200 rounded-xl shadow-2xl p-5 space-y-4">
                                <h4 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-2 mb-3">Filter Data</h4>

                                {{-- Row 1: Status & Kategori --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Aset</label>
                                        <select x-model="filters.status" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                            <option value="">Semua Status</option>
                                            <option value="available">Tersedia</option>
                                            <option value="in_use">Sedang digunakan</option>
                                            <option value="borrowed">Dipinjam</option>
                                            <option value="maintenance">Dalam perawatan</option>
                                            <option value="broken">Rusak</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kategori</label>
                                        <select x-model="filters.category" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                            <option value="">Semua Kategori</option>
                                            @foreach($categories as $cat)
                                            <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Row 2: Date Range --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Rentang Tanggal</label>
                                    <div class="flex items-center gap-2">
                                        <input type="date" x-model="filters.date_from" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" title="Dari tanggal">
                                        <span class="text-gray-400 text-xs font-medium shrink-0">s/d</span>
                                        <input type="date" x-model="filters.date_to" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" title="Sampai tanggal">
                                    </div>
                                </div>

                                {{-- Row 3: Lifecycle --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Garansi</label>
                                        <select x-model="filters.warranty_state" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                            <option value="">Semua Kondisi Garansi</option>
                                            <option value="expiring">Garansi ≤ 30 hari</option>
                                            <option value="expired">Garansi sudah habis</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Peminjaman</label>
                                        <select x-model="filters.loan_state" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                            <option value="">Semua Status Pinjam</option>
                                            <option value="borrowed">Dipinjam</option>
                                            <option value="returned">Dikembalikan</option>
                                            <option value="overdue">Terlambat</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-gray-100 flex justify-between items-center">
                                    <button type="button" @click="resetFilters()" class="text-sm text-gray-500 font-semibold hover:text-red-600 transition-colors cursor-pointer">Reset</button>
                                    <button type="button" @click="showFilters = false" class="text-sm text-purple-600 font-semibold hover:text-purple-800 cursor-pointer">Tutup</button>
                                </div>
                            </div>
                        </div>

                        {{-- Search Bar --}}
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="performSearch()"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition"
                                placeholder="Cari aset berdasarkan ID, nama, atau kategori...">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                        <a href="{{ route('assets.create') }}" class="inline-flex items-center px-4 py-2.5 bg-purple-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1 transition shadow-sm shrink-0">
                            <svg class="w-4 h-4 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span class="whitespace-nowrap">Tambah Aset</span>
                        </a>
                        @if(auth()->user() && in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <a href="#" @click.prevent="exportCsv()" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 transition shadow-sm shrink-0">
                            <svg class="w-4 h-4 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span class="whitespace-nowrap">Export CSV</span>
                        </a>
                        <a href="{{ route('assets.export') }}" target="_blank" class="inline-flex items-center px-4 py-2.5 bg-purple-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1 transition shadow-sm shrink-0">
                            <svg class="w-4 h-4 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="whitespace-nowrap">Cetak Laporan</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <div x-ref="tableContainer" class="w-full flex flex-col gap-4" @click="handlePaginationClick($event)">
                @include('assets.partials.table')
            </div>

            <section x-show="selectedIds.length > 0" x-transition x-cloak class="asset-bulk-panel" x-data="{ bulkStatus: '' }" aria-label="Tindakan untuk aset terpilih">
                <div class="asset-bulk-heading">
                    <strong x-text="selectedIds.length + ' aset dipilih'" aria-live="polite"></strong>
                    <button type="button" @click="clearSelection()" class="asset-bulk-cancel">Batalkan pilihan</button>
                </div>
                <div class="asset-bulk-content">
                    <form method="POST" action="{{ route('assets.bulk-update') }}" class="asset-bulk-update">
                        @csrf
                        <template x-for="id in selectedIds" :key="id"><input type="hidden" name="asset_ids[]" :value="id"></template>
                        <div class="asset-bulk-field">
                            <label for="bulk-status">Ubah status</label>
                            <select name="status" id="bulk-status" x-model="bulkStatus">
                                <option value="">Tetap seperti semula</option>
                                <option value="available">Tersedia</option>
                                <option value="in_use">Sedang digunakan</option>
                                <option value="borrowed">Dipinjam</option>
                                <option value="maintenance">Dalam perawatan</option>
                                <option value="broken">Rusak</option>
                            </select>
                        </div>
                        <div class="asset-bulk-field">
                            <label for="bulk-location">Ubah lokasi</label>
                            <input type="text" name="location" id="bulk-location" maxlength="255" placeholder="Tetap seperti semula">
                        </div>
                        <div class="asset-bulk-loan" x-show="['borrowed', 'in_use'].includes(bulkStatus)" x-cloak>
                            <div class="asset-bulk-field"><label for="bulk-borrower">Nama peminjam</label><input type="text" id="bulk-borrower" name="borrower_name" maxlength="255" :required="['borrowed', 'in_use'].includes(bulkStatus)" :disabled="!['borrowed', 'in_use'].includes(bulkStatus)" placeholder="Nama peminjam untuk aset terpilih"></div>
                            <div class="asset-bulk-field"><label for="bulk-due">Jatuh tempo (opsional)</label><input type="date" id="bulk-due" name="due_at" :disabled="!['borrowed', 'in_use'].includes(bulkStatus)"></div>
                            <p>Data peminjam berlaku untuk semua aset yang dipilih. Peminjaman tercatat di riwayat setelah perubahan disetujui.</p>
                        </div>
                        <button type="submit" class="asset-bulk-save">Simpan perubahan</button>
                    </form>
                    <form method="POST" action="{{ route('assets.bulk-delete-request') }}" class="asset-bulk-delete" @submit.prevent="confirmBulkDelete($event)">
                        @csrf
                        <template x-for="id in selectedIds" :key="id"><input type="hidden" name="asset_ids[]" :value="id"></template>
                        <button type="submit">Hapus aset</button>
                    </form>
                </div>
            </section>
        </div>

        {{-- Edit Scanner Modal (Barcode) --}}
        <div x-show="showEditScanner" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditScanner" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="closeEditScanner()"></div>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div x-show="showEditScanner" x-transition class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-gray-100">
                    <div class="bg-gray-50 px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5v14M7 5v14M11 5v14M14 5v14M18 5v14M20 5v14"></path></svg>
                            Verifikasi Barcode
                        </h3>
                        <button @click="closeEditScanner()" class="text-gray-400 hover:text-red-600 transition-colors cursor-pointer">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-gray-600 mb-3 text-center">Scan barcode pada aset fisik untuk memastikan Anda mengedit aset yang benar.</p>

                        {{-- Video kamera + garis bantu --}}
                        <div class="relative w-full rounded-lg overflow-hidden bg-black" style="aspect-ratio: 4 / 3;">
                            <video x-ref="editVideo" playsinline muted autoplay
                                class="absolute inset-0 w-full h-full" style="object-fit:cover;"></video>
                            <div class="absolute pointer-events-none border-2 border-white/80 rounded-lg"
                                style="left:5%; right:5%; top:30%; bottom:30%;"></div>
                        </div>

                        <p class="text-center text-sm mt-3 font-medium"
                           :class="_scanStatus.includes('✅') ? 'text-emerald-600' : _scanStatus.includes('❌') ? 'text-red-600' : 'text-gray-400'"
                           x-text="_scanStatus"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js"></script>
    <script>
        // Pembaca barcode: decode frame kamera resolusi penuh.
        // State disimpan di luar Alpine agar tidak dibungkus Proxy.
        window.BarcodeCam = (function () {
            let stream = null;
            let timer = null;
            let detector = null;
            let zxingReader = null;
            let onText = null;
            let video = null;
            let busy = false;
            let pausedUntil = 0;
            let session = 0;
            const canvas = document.createElement('canvas');

            const NATIVE_FORMATS = ['code_128', 'code_39', 'ean_13', 'ean_8', 'upc_a'];

            async function initDecoder() {
                detector = null;
                zxingReader = null;

                // 1) BarcodeDetector bawaan browser (Chrome Android, dll.)
                if ('BarcodeDetector' in window) {
                    try {
                        const supported = await BarcodeDetector.getSupportedFormats();
                        const formats = NATIVE_FORMATS.filter(f => supported.includes(f));
                        if (formats.length) {
                            detector = new BarcodeDetector({ formats });
                            return;
                        }
                    } catch (_) {}
                }

                // 2) Fallback ZXing (iPhone/Safari, Firefox, dll.)
                const Z = window.ZXing;
                if (!Z) throw new Error('Library pembaca barcode gagal dimuat. Periksa koneksi internet.');

                const hints = new Map();
                hints.set(Z.DecodeHintType.POSSIBLE_FORMATS, [
                    Z.BarcodeFormat.CODE_128,
                    Z.BarcodeFormat.CODE_39,
                    Z.BarcodeFormat.EAN_13,
                    Z.BarcodeFormat.EAN_8,
                    Z.BarcodeFormat.UPC_A,
                ]);
                hints.set(Z.DecodeHintType.TRY_HARDER, true);
                zxingReader = new Z.MultiFormatReader();
                zxingReader.setHints(hints);
            }

            async function decodeFrame() {
                if (detector) {
                    const codes = await detector.detect(video);
                    return codes.length ? codes[0].rawValue : null;
                }

                const vw = video.videoWidth;
                const vh = video.videoHeight;
                const scale = Math.min(1, 1280 / Math.max(vw, vh));
                canvas.width = Math.floor(vw * scale);
                canvas.height = Math.floor(vh * scale);

                const ctx = canvas.getContext('2d', { willReadFrequently: true });
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const Z = window.ZXing;
                try {
                    const source = new Z.HTMLCanvasElementLuminanceSource(canvas);
                    const bitmap = new Z.BinaryBitmap(new Z.HybridBinarizer(source));
                    return zxingReader.decodeWithState(bitmap).getText();
                } catch (_) {
                    return null; // belum ditemukan di frame ini
                }
            }

            function loop(mySession) {
                if (mySession !== session) return;

                if (!busy && Date.now() >= pausedUntil && video && video.readyState >= 2 && video.videoWidth > 0) {
                    busy = true;
                    decodeFrame()
                        .then((text) => {
                            if (text && mySession === session && onText) onText(text);
                        })
                        .catch(() => {})
                        .finally(() => { busy = false; });
                }

                timer = setTimeout(() => loop(mySession), 80);
            }

            function release() {
                clearTimeout(timer);
                timer = null;
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
                if (video) video.srcObject = null;
                detector = null;
                zxingReader = null;
                onText = null;
                busy = false;
                pausedUntil = 0;
            }

            async function start(videoEl, callback) {
                stop();
                const mySession = ++session;

                if (!window.isSecureContext) {
                    throw new Error('Kamera hanya bisa dipakai lewat HTTPS (atau localhost).');
                }
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Browser tidak mendukung akses kamera.');
                }

                video = videoEl;
                onText = callback;

                await initDecoder();
                if (mySession !== session) return;

                const s = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                    },
                });

                // Modal ditutup saat kamera masih dibuka
                if (mySession !== session) {
                    s.getTracks().forEach(t => t.stop());
                    return;
                }
                stream = s;

                // Autofocus kontinu jika didukung
                try {
                    const track = s.getVideoTracks()[0];
                    const caps = track.getCapabilities ? track.getCapabilities() : {};
                    if (caps.focusMode && caps.focusMode.includes('continuous')) {
                        await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
                    }
                } catch (_) {}

                video.srcObject = s;
                await video.play();
                if (mySession !== session) return;

                loop(mySession);
            }

            function stop() {
                session++;
                release();
            }

            function pauseFor(ms) {
                pausedUntil = Date.now() + ms;
            }

            function errorMessage(error) {
                if (error?.name === 'NotAllowedError') return 'Akses kamera ditolak. Izinkan kamera pada pengaturan browser.';
                if (error?.name === 'NotFoundError') return 'Kamera tidak ditemukan pada perangkat.';
                if (error?.name === 'NotReadableError') return 'Kamera sedang digunakan oleh aplikasi lain.';
                if (error?.message) return error.message;
                return 'Kamera tidak dapat diakses. Periksa izin kamera browser.';
            }

            return { start, stop, pauseFor, errorMessage };
        })();
    </script>
    @endpush
</x-app-layout>