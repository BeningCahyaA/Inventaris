<x-app-layout>
    <x-slot name="title">Aset</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aset') }}
        </h2>
    </x-slot>

    <div class="asset-page" x-data="{
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
        _scanner: null,
        _editTag: null,
        _editUrl: null,
        _scanStatus: 'Scan QR Code aset untuk verifikasi sebelum edit.',

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
        openEditScanner(expectedTag, editUrl) {
            this._editTag = expectedTag; this._editUrl = editUrl;
            this._scanStatus = 'Scan QR Code aset untuk verifikasi sebelum edit.';
            this.showEditScanner = true;
            this.$nextTick(() => this.startEditScanner());
        },
        startEditScanner() {
            if (!this._scanner) this._scanner = new Html5Qrcode('edit-reader');
            this._scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 250, height: 250 } }, (text) => this.onEditScanSuccess(text)).then(() => this._scanStatus = 'Arahkan kamera ke QR Code...');
        },
        stopEditScanner() { if (this._scanner && this._scanner.isScanning) this._scanner.stop().catch(() => {}); this._scanStatus = 'Scan QR Code aset untuk verifikasi sebelum edit.'; },
        onEditScanSuccess(tag) {
            this._scanner.pause();
            if (tag === this._editTag) { this._scanStatus = 'QR Code cocok! Mengalihkan...'; this.stopEditScanner(); this.showEditScanner = false; window.location.href = this._editUrl; }
            else { this._scanStatus = 'QR Code tidak cocok! Pastikan Anda scan aset yang benar.'; setTimeout(() => { this._scanStatus = 'Arahkan kamera ke QR Code...'; this._scanner.resume(); }, 2000); }
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
                                class="inline-flex items-center justify-center p-2.5 border rounded-lg transition shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shrink-0"
                                :class="showFilters ? 'bg-indigo-50 border-indigo-200 text-indigo-600' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-indigo-600'"
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
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                                            <option value="">Semua Status</option>
                                            <option value="available">Tersedia</option>
                                            <option value="in_use">Sedang digunakan</option>
                                            <option value="maintenance">Dalam perawatan</option>
                                            <option value="broken">Rusak</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kategori</label>
                                        <select x-model="filters.category" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
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
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" title="Dari tanggal">
                                        <span class="text-gray-400 text-xs font-medium shrink-0">s/d</span>
                                        <input type="date" x-model="filters.date_to" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" title="Sampai tanggal">
                                    </div>
                                </div>

                                {{-- Row 3: Lifecycle --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Garansi</label>
                                        <select x-model="filters.warranty_state" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                                            <option value="">Semua Kondisi Garansi</option>
                                            <option value="expiring">Garansi ≤ 30 hari</option>
                                            <option value="expired">Garansi sudah habis</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Peminjaman</label>
                                        <select x-model="filters.loan_state" @change="performSearch()"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                                            <option value="">Semua Status Pinjam</option>
                                            <option value="borrowed">Dipinjam</option>
                                            <option value="returned">Dikembalikan</option>
                                            <option value="overdue">Terlambat</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-gray-100 flex justify-between items-center">
                                    <button type="button" @click="resetFilters()" class="text-sm text-gray-500 font-semibold hover:text-red-600 transition-colors cursor-pointer">Reset</button>
                                    <button type="button" @click="showFilters = false" class="text-sm text-indigo-600 font-semibold hover:text-indigo-800 cursor-pointer">Tutup</button>
                                </div>
                            </div>
                        </div>

                        {{-- Search Bar --}}
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="performSearch()"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition"
                                placeholder="Cari aset berdasarkan ID, nama, atau kategori...">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                        <a href="{{ route('assets.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition shadow-sm shrink-0">
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

            {{-- Bulk Action Bar (floating) --}}
            <div x-show="selectedIds.length > 0" x-transition x-cloak
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-gray-900/90 backdrop-blur-md text-white rounded-2xl shadow-2xl px-6 py-3 flex items-center gap-5 border border-white/10 min-w-[320px] md:min-w-[450px]">
                <span class="text-sm font-semibold" x-text="selectedIds.length + ' aset dipilih'"></span>
                <div class="h-5 w-px bg-gray-600"></div>

                {{-- Bulk Update Form --}}
                <form method="POST" action="{{ route('assets.bulk-update') }}" class="flex items-center gap-2">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="asset_ids[]" :value="id">
                    </template>
                    <select name="status" class="text-xs bg-gray-800 border border-gray-600 text-white rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-400 cursor-pointer">
                        <option value="">— Ubah Status —</option>
                        <option value="available">Tersedia</option>
                        <option value="in_use">Sedang digunakan</option>
                        <option value="maintenance">Dalam perawatan</option>
                        <option value="broken">Rusak</option>
                    </select>
                    <input type="text" name="location" placeholder="Ubah Lokasi"
                           class="text-xs bg-gray-800 border border-gray-600 text-white rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-400 placeholder-gray-400 w-28">
                    <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 shadow-lg shadow-indigo-500/20">
                        Update
                    </button>
                </form>

                <div class="h-5 w-px bg-gray-600"></div>

                {{-- Bulk Delete Form --}}
                <form method="POST" action="{{ route('assets.bulk-delete-request') }}" class="inline" @submit.prevent="confirmBulkDelete($event)">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="asset_ids[]" :value="id">
                    </template>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 shadow-lg shadow-red-500/20">
                        Hapus
                    </button>
                </form>

                <button @click="clearSelection()" class="ml-auto text-gray-400 hover:text-white transition-colors text-xs font-bold uppercase tracking-widest cursor-pointer">
                    Batal
                </button>
            </div>
        </div>

        {{-- Edit Scanner Modal --}}
        <div x-show="showEditScanner" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditScanner" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="closeEditScanner()"></div>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div x-show="showEditScanner" x-transition class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-gray-100">
                    <div class="bg-gray-50 px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-18m0 0h2m-2-11h2m14 0h2M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 5h1v1H9v-1zm5 0h1v1h-1v-1zM7 5h4v4H7V5zm8 0h4v4h-4V5zm-8 8h4v4H7v-4zm8 0h4v4h-4v-4z"></path></svg>
                            Verifikasi QR Code
                        </h3>
                        <button @click="closeEditScanner()" class="text-gray-400 hover:text-red-600 transition-colors cursor-pointer">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-gray-600 mb-3 text-center">Scan QR Code pada aset fisik untuk memastikan Anda mengedit aset yang benar.</p>
                        <div id="edit-reader" class="w-full rounded-lg overflow-hidden bg-black aspect-square"></div>
                        <p class="text-center text-sm mt-3 font-medium" :class="_scanStatus.includes('✅') ? 'text-emerald-600' : _scanStatus.includes('❌') ? 'text-red-600' : 'text-gray-400'" x-text="_scanStatus"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>