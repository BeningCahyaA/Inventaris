<x-app-layout>
    <x-slot name="title">Cetak Label</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cetak Label') }}
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-4 rounded-xl shadow-sm mb-6 flex flex-col sm:flex-row justify-between items-center gap-4 border border-gray-100">
                
                <div class="w-full sm:w-1/2 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="search-input" value="{{ request('search') }}" 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition duration-150 ease-in-out" 
                        placeholder="Cari label aset yang ingin dicetak...">
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    
                    <span id="selected-count" class="text-xs font-bold text-purple-700 bg-purple-50 px-3 py-2 rounded-md hidden transition-all">
                        0 Selected
                    </span>
                    
                    <button type="button" id="btn-select-all" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Select All
                    </button>

                    <button type="button" id="btn-print" onclick="printSelected()" disabled 
                        class="inline-flex items-center px-4 py-2 bg-purple-600  rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150 shadow-md cursor-pointer">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print
                    </button>
                </div>
            </div>

            <div id="table-container" class="w-full flex flex-col gap-4">
                @include('assets.partials.print-table')
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedAssets = JSON.parse(localStorage.getItem('selectedAssets')) || [];
            const searchInput = document.getElementById('search-input');
            const tableContainer = document.getElementById('table-container');
            const btnPrint = document.getElementById('btn-print');
            const btnSelectAll = document.getElementById('btn-select-all');
            const countDisplay = document.getElementById('selected-count');
            
            let typingTimer;
            const doneTypingInterval = 500; // Typing Delay 500ms

            // === 1. INISIALISASI ===
            initCheckboxListeners(); 
            updateUI(); 

            // === 2. LIVE SEARCH ===
            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(performSearch, doneTypingInterval);
            });

            function performSearch() {
                let query = searchInput.value;
                let url = new URL(window.location.href);
                if(query) url.searchParams.set('search', query);
                else url.searchParams.delete('search');
                url.searchParams.delete('page'); 
                window.history.pushState({}, '', url);

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    initCheckboxListeners(); 
                })
                .catch(error => console.error('Error:', error));
            }

            // === 3. PAGINATION ===
            tableContainer.addEventListener('click', function(e) {
                if (e.target.closest('.pagination a')) {
                    e.preventDefault();
                    let pageUrl = e.target.closest('a').href;
                    window.history.pushState({}, '', pageUrl);
                    fetch(pageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                        initCheckboxListeners();
                        tableContainer.scrollIntoView({ behavior: 'smooth' });
                    });
                }
            });

            // === 4. CHECKBOX LOGIC ===
            function initCheckboxListeners() {
                const checkboxes = document.querySelectorAll('.asset-checkbox');
                checkboxes.forEach(cb => {
                    if (selectedAssets.includes(cb.value)) cb.checked = true;
                    cb.addEventListener('change', function() {
                        if (this.checked) {
                            if (!selectedAssets.includes(this.value)) selectedAssets.push(this.value);
                        } else {
                            selectedAssets = selectedAssets.filter(id => id !== this.value);
                        }
                        saveAndRefresh();
                    });
                });
                updateSelectAllButtonState();
            }

            // === 5. SELECT ALL BUTTON ===
            btnSelectAll.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.asset-checkbox');
                const currentPageIds = Array.from(checkboxes).map(cb => cb.value);
                const allCheckedOnPage = currentPageIds.every(id => selectedAssets.includes(id));
                
                if (allCheckedOnPage && selectedAssets.length > currentPageIds.length) {
                    // Jika sudah select all (across pages), maka unselect all
                    selectedAssets = [];
                    checkboxes.forEach(cb => cb.checked = false);
                    saveAndRefresh();
                } else if (allCheckedOnPage) {
                    // Jika hanya current page yang tercentang, uncheck current page
                    checkboxes.forEach(cb => {
                        cb.checked = false;
                        selectedAssets = selectedAssets.filter(id => id !== cb.value);
                    });
                    saveAndRefresh();
                } else {
                    // Jika belum semua tercentang, panggil API untuk select all across pages
                    selectAllAcrossPages();
                }
            });

            // Fungsi untuk select all across pages
            function selectAllAcrossPages() {
                // Tampilkan loading state
                btnSelectAll.disabled = true;
                btnSelectAll.textContent = 'Loading...';
                
                // Ambil parameter search saat ini
                const searchParam = searchInput.value ? `?search=${encodeURIComponent(searchInput.value)}` : '';
                const apiUrl = "{{ route('assets.all-ids') }}" + searchParam;
                
                fetch(apiUrl, {
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Simpan semua ID ke selectedAssets
                        selectedAssets = data.ids.map(id => String(id));
                        
                        // Centang semua checkbox di halaman saat ini
                        const checkboxes = document.querySelectorAll('.asset-checkbox');
                        checkboxes.forEach(cb => {
                            if (selectedAssets.includes(cb.value)) {
                                cb.checked = true;
                            }
                        });
                        
                        saveAndRefresh();
                        
                        // Tampilkan notifikasi
                        if (data.total > 0) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: `${data.total} aset dipilih dari semua halaman`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat memuat data',
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .finally(() => {
                    btnSelectAll.disabled = false;
                });
            }

            function saveAndRefresh() {
                localStorage.setItem('selectedAssets', JSON.stringify(selectedAssets));
                updateUI();
                updateSelectAllButtonState();
            }

            function updateSelectAllButtonState() {
                const checkboxes = document.querySelectorAll('.asset-checkbox');
                if (checkboxes.length === 0) {
                    btnSelectAll.textContent = "Select All";
                    btnSelectAll.className = "inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150";
                    return;
                }
                
                const currentPageIds = Array.from(checkboxes).map(cb => cb.value);
                const allCheckedOnPage = currentPageIds.every(id => selectedAssets.includes(id));
                const hasMoreSelected = selectedAssets.length > currentPageIds.length;
                
                // Update teks dan style button
                if (allCheckedOnPage && hasMoreSelected) {
                    btnSelectAll.textContent = `Deselect All`;
                    btnSelectAll.className = "inline-flex items-center px-4 py-2 bg-purple-600  rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 transition ease-in-out duration-150 shadow-md";
                } else if (allCheckedOnPage) {
                    btnSelectAll.textContent = "Deselect All";
                    btnSelectAll.className = "inline-flex items-center px-4 py-2 bg-blue-600  rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 transition ease-in-out duration-150 shadow-md";
                } else {
                    btnSelectAll.textContent = "Select All";
                    btnSelectAll.className = "inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150";
                }
            }

            function updateUI() {
                if (selectedAssets.length > 0) {
                    countDisplay.innerText = selectedAssets.length + ' Selected';
                    countDisplay.classList.remove('hidden');
                    btnPrint.disabled = false;
                    btnPrint.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    countDisplay.classList.add('hidden');
                    btnPrint.disabled = true;
                    btnPrint.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }

            window.printSelected = function() {
                if (selectedAssets.length === 0) return;
                let url = "{{ route('assets.pdf') }}"; 
                const params = new URLSearchParams();
                selectedAssets.forEach(id => params.append('selected_assets[]', id));
                window.open(url + '?' + params.toString(), '_blank');
            };
        });
    </script>
</x-app-layout>