<x-app-layout>
    <x-slot name="title">Riwayat Aset</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Aset') }}
        </h2>
    </x-slot>

    <div class="asset-subpage management-page history-page" x-data="{ showFilters: false }">
        <div class="max-w-7xl mx-auto">
            <div class="management-intro"><div><h3>Riwayat aset</h3><p>Pantau riwayat peminjaman dan aktivitas pengelolaan aset.</p></div></div>
            <nav class="flex gap-6 border-b border-gray-200 mb-6" aria-label="Jenis riwayat">
                @foreach(['history' => 'Riwayat peminjaman', 'activity' => 'Log aktivitas'] as $tab => $label)
                    <a href="{{ route('assets.history', ['tab' => $tab]) }}" @if($activeTab === $tab) aria-current="page" @endif
                        class="inline-flex items-center py-3 border-b-2 text-sm font-semibold transition {{ $activeTab === $tab ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
                @endforeach
            </nav>
            @if($activeTab === 'activity')
                @include('assets.partials.activity_table')
            @else
            {{-- Modern Responsive Filter Bar --}}
            <div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                    <div class="flex items-center gap-2 flex-1 relative">
                        {{-- Funnel Filter Button & Dropdown --}}
                        <div class="relative" @click.outside="showFilters = false">
                            <button type="button" @click="showFilters = !showFilters"
                                class="inline-flex items-center justify-center p-2.5 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-purple-600 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 shrink-0"
                                :class="showFilters ? 'bg-purple-50 border-purple-200 text-purple-600' : ''"
                                title="Filter Data">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            </button>

                            {{-- Floating Filter Box --}}
                            <div x-show="showFilters" x-transition.opacity x-cloak
                                class="absolute left-0 top-full mt-2 w-[calc(100vw-40px)] sm:w-[500px] z-50 bg-white border border-gray-200 rounded-xl shadow-2xl p-5 space-y-4">
                                <h4 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-2 mb-3">Filter Riwayat</h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Transaksi</label>
                                        <select id="status-filter" class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                            <option value="">Semua Status</option>
                                            <option value="borrowed" @selected(request('status') === 'borrowed')>Sedang Dipinjam</option>
                                            <option value="returned" @selected(request('status') === 'returned')>Sudah Kembali</option>
                                            <option value="overdue" @selected(request('status') === 'overdue')>Terlambat (Overdue)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Pinjam (Extra)</label>
                                        <select id="loan-state-filter" class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                            <option value="">Semua status</option>
                                            <option value="borrowed">Sedang dipinjam</option>
                                            <option value="returned">Sudah kembali</option>
                                            <option value="overdue">Terlambat</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Rentang Tanggal Pinjam</label>
                                    <div class="flex items-center gap-2">
                                        <input type="date" id="date-from" value="{{ request('date_from') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" title="Dari tanggal">
                                        <span class="text-gray-500 text-xs font-medium shrink-0">s/d</span>
                                        <input type="date" id="date-to" value="{{ request('date_to') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" title="Sampai tanggal">
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-gray-100 flex justify-end">
                                    <button type="button" @click="showFilters = false" class="text-sm font-bold bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 transition shadow-lg shadow-purple-100">
                                        Oke
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Search Bar --}}
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="search" aria-label="Cari riwayat peminjaman" id="search-input" value="{{ request('search') }}"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition shadow-inner"
                                placeholder="Cari peminjam, nama aset atau tag...">
                        </div>
                    </div>
                </div>
            </div>

            <div id="table-container" class="w-full flex flex-col gap-4">
                @include('assets.partials.history_table')
            </div>
            @endif
        </div>
    </div>

    @if($activeTab === 'history')
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const statusFilter = document.getElementById('status-filter');
            const loanStateFilter = document.getElementById('loan-state-filter');
            const dateFrom = document.getElementById('date-from');
            const dateTo = document.getElementById('date-to');
            const tableContainer = document.getElementById('table-container');

            let typingTimer;
            const doneTypingInterval = 500;

            const performSearch = () => {
                const url = new URL(window.location.href);
                const params = {
                    search: searchInput.value,
                    status: statusFilter.value,
                    loan_state: loanStateFilter.value,
                    date_from: document.getElementById('date-from').value,
                    date_to: document.getElementById('date-to').value
                };

                Object.keys(params).forEach(key => {
                    if (params[key]) url.searchParams.set(key, params[key]);
                    else url.searchParams.delete(key);
                });

                url.searchParams.delete('page');
                window.history.pushState({}, '', url);

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                    })
                    .catch(error => console.error('Error:', error));
            };

            [searchInput].forEach(el => {
                el?.addEventListener('input', () => {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(performSearch, doneTypingInterval);
                });
            });

            [statusFilter, loanStateFilter, document.getElementById('date-from'), document.getElementById('date-to')].forEach(el => {
                el?.addEventListener('change', performSearch);
            });

            // AJAX Pagination
            tableContainer.addEventListener('click', function(e) {
                if (e.target.closest('.pagination a')) {
                    e.preventDefault();
                    let pageUrl = e.target.closest('a').href;
                    window.history.pushState({}, '', pageUrl);
                    fetch(pageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(response => response.text())
                        .then(html => {
                            tableContainer.innerHTML = html;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        });
                }
            });
        });
    </script>
    @endpush
    @endif
</x-app-layout>
