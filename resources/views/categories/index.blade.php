<x-app-layout>
    <x-slot name="title">Kategori</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kategori') }}</h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto">
            <div class="mb-5">
                <h1 class="text-2xl font-bold text-gray-900">Kategori aset</h1>
                <p class="mt-2 text-sm text-gray-600">Kelompokkan aset berdasarkan jenisnya. Pilih jumlah aset untuk melihat isi kategori.</p>
            </div>

            @if(session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="font-semibold text-sm">{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="font-semibold text-sm">{{ session('error') }}</span>
            </div>
            @endif

            <div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="relative flex-1 w-full max-w-md">
                        <label for="search-input" class="sr-only">Cari nama, kode kategori, atau kode tag</label>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" id="search-input" value="{{ request('search') }}"
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition"
                            placeholder="Cari nama atau kode kategori…">
                    </div>

                    <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2.5 bg-purple-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1 transition shadow-sm shrink-0 self-end sm:self-auto">
                        <svg class="w-4 h-4 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="whitespace-nowrap">Tambah Kategori</span>
                    </a>
                </div>
            </div>

            <div id="table-container" class="w-full flex flex-col gap-4">
                @include('categories.partials.table')
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const tableContainer = document.getElementById('table-container');
            let typingTimer;
            const doneTypingInterval = 500;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(performSearch, doneTypingInterval);
                });
            }

            function buildUrl() {
                let url = new URL(window.location.href);
                const query = searchInput ? searchInput.value : '';

                if (query) url.searchParams.set('search', query);
                else url.searchParams.delete('search');
                url.searchParams.delete('page');
                return url;
            }

            function performSearch() {
                const url = buildUrl();
                window.history.pushState({}, '', url);
                fetchTable(url.toString());
            }

            function fetchTable(url) {
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                    })
                    .catch(error => console.error('Error:', error));
            }

            tableContainer.addEventListener('click', function(e) {
                if (e.target.closest('.pagination a')) {
                    e.preventDefault();
                    let pageUrl = e.target.closest('a').href;
                    window.history.pushState({}, '', pageUrl);
                    fetch(pageUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            tableContainer.innerHTML = html;
                            tableContainer.scrollIntoView({
                                behavior: 'smooth'
                            });
                        });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>