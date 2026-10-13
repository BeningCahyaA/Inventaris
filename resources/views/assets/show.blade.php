<x-app-layout>
    <x-slot name="title">Detail Aset: {{ $asset->name }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Aset') }}
            </h2>
            <div class="flex items-center text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
            <a href="{{ route('assets.index') }}" class="text-sm font-bold text-purple-600 hover:text-purple-800 transition flex items-center gap-1">
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $images = $asset->attachments->filter(fn($a) => $a->isImage());
        $docs = $asset->attachments->reject(fn($a) => $a->isImage());
    @endphp

    <div class="asset-subpage" x-data="{ 
        activeTab: 'detail',
        activeImage: {{ $images->count() > 0 ? 0 : 'null' }},
        images: [
            @foreach($images as $atch)
                '{{ route('assets.attachments.show', [$asset->id, $atch->id], false) }}',
            @endforeach
        ],

        {{-- Barcode Scanner Logic --}}
        showEditScanner: false,
        _editTag: '{{ $asset->asset_tag }}',
        _editUrl: '{{ route('assets.edit', $asset->id) }}',
        _scanStatus: 'Scan barcode aset untuk verifikasi sebelum edit.',

        openEditScanner() {
            this._scanStatus = 'Scan barcode aset untuk verifikasi sebelum edit.';
            this.showEditScanner = true;
            this.$nextTick(() => this.startEditScanner());
        },

        startEditScanner() {
            this._scanStatus = 'Membuka kamera...';
            BarcodeCam.start(this.$refs.editVideo, (text) => this.onEditScanSuccess(text))
                .then(() => { this._scanStatus = 'Arahkan kamera ke barcode...'; })
                .catch((e) => { console.error('Scanner error:', e); this._scanStatus =  BarcodeCam.errorMessage(e); });
        },

        stopEditScanner() {
            BarcodeCam.stop();
        },

        closeEditScanner() {
            this.stopEditScanner();
            this.showEditScanner = false;
        },

        onEditScanSuccess(text) {
            const code = String(text || '').trim();
            if (code === String(this._editTag || '').trim()) {
                this._scanStatus = 'Barcode cocok! Mengalihkan...';
                const target = this._editUrl;
                this.stopEditScanner();
                this.showEditScanner = false;
                window.location.href = target;
            } else {
                BarcodeCam.pauseFor(2000);
                this._scanStatus = 'Barcode tidak cocok! Pastikan Anda scan aset yang benar.';
                setTimeout(() => { if (this.showEditScanner) this._scanStatus = 'Arahkan kamera ke barcode...'; }, 2000);
            }
        }
    }" @keydown.window.escape="closeEditScanner()">
        <div class="max-w-7xl mx-auto space-y-6">

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12">
                    
                    {{-- PHOTO GALLERY SECTION --}}
                    <div class="lg:col-span-4 bg-gray-50 p-6 flex flex-col justify-center items-center border-b md:border-b-0 md:border-r border-gray-100 relative">
                        @if($images->count() > 0)
                            <div class="asset-photo-frame w-full bg-white rounded-xl shadow-inner border border-gray-200 overflow-hidden relative flex items-center justify-center mb-4">
                                <template x-if="activeImage !== null">
                                    <img :src="images[activeImage]" alt="{{ $asset->name }}" class="object-contain w-full h-full transition-all duration-300" />
                                </template>
                            </div>
                            
                            @if($images->count() > 1)
                            <div class="flex items-center gap-2 overflow-x-auto pb-2 w-full custom-scrollbar">
                                <template x-for="(img, index) in images" :key="index">
                                    <button @click="activeImage = index" 
                                        class="shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all focus:outline-none"
                                        :class="activeImage === index ? 'border-purple-600 shadow-md ring-2 ring-purple-100' : 'border-transparent opacity-60 hover:opacity-100'">
                                        <img :src="img" class="w-full h-full object-cover" />
                                    </button>
                                </template>
                            </div>
                            @endif
                        @else
                            <div class="asset-photo-frame w-full bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-xs font-semibold uppercase tracking-wider">Tidak Ada Foto</span>
                            </div>
                        @endif
                    </div>

                    {{-- ASSET INFO SUMMARY SECTION --}}
                    <div class="lg:col-span-8 p-6 md:p-8 flex flex-col">
                        <div class="flex flex-col sm:flex-row items-start justify-between gap-4 mb-4">
                            <div>
                                <span class="px-2.5 py-1 bg-purple-50 text-purple-700 text-[10px] font-extrabold uppercase tracking-wider rounded-md border border-purple-100">{{ \App\Models\Category::where('slug', $asset->category)->value('name') ?? $asset->category }}</span>
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mt-2 leading-tight">{{ $asset->name }}</h1>
                                <p class="text-sm font-mono text-gray-500 mt-1 flex items-center gap-1.5 text-purple-600 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    {{ $asset->asset_tag }}
                                </p>
                            </div>
                            
                            @php
                                $statusColors = [
                                    'available' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'in_use' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'maintenance' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'broken' => 'bg-red-100 text-red-800 border-red-200',
                                    'sold' => 'bg-gray-100 text-gray-700 border-gray-200',
                                ];
                                $condColors = [
                                    'baik' => 'bg-emerald-50 text-emerald-600',
                                    'rusak' => 'bg-orange-50 text-orange-600',
                                    'rusak_total' => 'bg-red-50 text-red-600',
                                ];
                            @endphp
                            <div class="shrink-0 flex flex-wrap sm:flex-col items-start sm:items-end gap-2">
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusColors[$asset->status] ?? 'bg-gray-100' }}">
                                    {{ match($asset->status) { 'available' => 'Tersedia', 'in_use' => 'Sedang digunakan', 'maintenance' => 'Dalam perawatan', 'broken' => 'Rusak', 'sold' => 'Dijual · Arsip', default => $asset->status } }}
                                </span>
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider {{ $condColors[$asset->condition] ?? 'bg-gray-100' }}">
                                    Kondisi: {{ str_replace('_', ' ', $asset->condition) }}
                                </span>
                            </div>
                        </div>

                        <div class="asset-detail-facts grid grid-cols-1 sm:grid-cols-2 gap-4 my-6 flex-1">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-500 mb-1 tracking-wider">Penanggung Jawab</p>
                                <p class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-xs text-purple-700 font-bold shrink-0">{{ mb_substr($asset->person_in_charge ?? '', 0, 1) ?: '–' }}</span>
                                    {{ $asset->person_in_charge ?: 'Belum ditentukan' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-500 mb-1 tracking-wider">Lokasi</p>
                                <p class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $asset->location ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-500 mb-1 tracking-wider">Serial Number</p>
                                <p class="text-sm font-mono font-medium text-gray-700 bg-gray-50 px-2 py-1 rounded inline-block border border-gray-100">{{ $asset->serial_number ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-500 mb-1 tracking-wider">Harga Beli</p>
                                <p class="text-sm font-bold text-emerald-600 font-mono tracking-tight">Rp {{ number_format($asset->price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-500 mb-1">Penyusutan per tahun</p>
                                <p class="text-sm font-semibold text-gray-800">{{ number_format($asset->depreciation_rate, 2, ',', '.') }}% dari harga beli awal</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-500 mb-1">Nilai setelah penyusutan</p>
                                <p class="text-sm font-bold text-purple-700">Rp {{ number_format($depreciatedValue, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        @if($asset->status !== 'sold' && in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <div class="pt-5 border-t border-gray-100 flex gap-3 mt-auto">
                            <button @click="openEditScanner()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center py-3 rounded-xl font-bold text-sm transition-all shadow-md shadow-purple-100 flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Scan Barcode untuk edit
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TABBED DATA SECTION --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex border-b border-gray-100 bg-gray-50/50 overflow-x-auto hide-scrollbar">
                    <button @click="activeTab = 'detail'" 
                        class="px-4 sm:px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all"
                        :class="activeTab === 'detail' ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        Informasi & lampiran
                    </button>
                    @if($asset->sale)
                    <button @click="activeTab = 'sale'" type="button" class="px-4 sm:px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2" :class="activeTab === 'sale' ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500'">Riwayat penjualan</button>
                    @endif
                    <button @click="activeTab = 'depreciation'" type="button"
                        class="px-4 sm:px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all"
                        :class="activeTab === 'depreciation' ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        Riwayat penyusutan
                    </button>
                    <button @click="activeTab = 'history'" 
                        class="px-4 sm:px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all flex items-center gap-2"
                        :class="activeTab === 'history' ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        Riwayat Pinjam
                        <span class="bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full text-[10px]">{{ $asset->transactions->count() }}</span>
                    </button>
                    <button @click="activeTab = 'logs'" 
                        class="px-4 sm:px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all"
                        :class="activeTab === 'logs' ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        Riwayat perubahan
                    </button>
                </div>

                <div class="p-6 md:p-8">
                    {{-- TAB 1: DESKRIPSI --}}
                    @include('assets.partials.depreciation-history')
                    @include('assets.partials.sale-history')
                    <div x-show="activeTab === 'detail'" x-transition.opacity>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">Informasi Tambahan</h3>
                                <ul class="space-y-4">
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500">Tanggal Pembelian</span>
                                        <span class="font-semibold text-gray-800">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</span>
                                    </li>
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500">Masa Garansi</span>
                                        @if($asset->warranty_expiry_date)
                                            <span class="font-semibold px-2 py-0.5 rounded text-xs {{ $asset->warranty_expiry_date->isPast() ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                                {{ $asset->warranty_expiry_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="font-semibold text-gray-800">-</span>
                                        @endif
                                    </li>
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500">Vendor / Supplier</span>
                                        <span class="font-semibold text-gray-800">{{ $asset->vendor ?? '-' }}</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">Deskripsi</h3>
                                <div class="prose prose-sm text-gray-600 leading-relaxed mb-6">
                                    @if($asset->description)
                                        {!! nl2br(e($asset->description)) !!}
                                    @else
                                        <p class="italic text-gray-500">Tidak ada deskripsi tambahan untuk aset ini.</p>
                                    @endif
                                </div>

                                @if($docs->count() > 0)
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">Lampiran Dokumen</h3>
                                <ul class="space-y-2">
                                    @foreach($docs as $doc)
                                    <li>
                                        <a href="{{ route('assets.attachments.show', [$asset->id, $doc->id]) }}" target="_blank" class="flex items-center gap-2 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-100 transition group">
                                            <svg class="w-5 h-5 text-gray-500 group-hover:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span class="text-sm font-medium text-gray-700 break-all">{{ $doc->file_name }}</span>
                                            <span class="ml-auto text-[10px] text-gray-500 font-mono uppercase">{{ pathinfo($doc->file_name, PATHINFO_EXTENSION) }}</span>
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: HISTORY TRANSAKSI --}}
                    <div x-show="activeTab === 'history'" x-transition.opacity style="display: none;">
                        @if($asset->transactions->count() > 0)
                        <div class="overflow-x-auto border border-gray-100 rounded-xl">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-50 text-[11px] text-gray-500 uppercase tracking-widest border-b border-gray-100">
                                        <th class="px-5 py-3 font-bold">Peminjam</th>
                                        <th class="px-5 py-3 font-bold">Tgl Pinjam</th>
                                        <th class="px-5 py-3 font-bold">Jatuh Tempo</th>
                                        <th class="px-5 py-3 font-bold">Tgl Kembali</th>
                                        <th class="px-5 py-3 font-bold text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($asset->transactions as $trx)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-5 py-3.5 font-bold text-gray-800">{{ $trx->borrower_name }}</td>
                                        <td class="px-5 py-3.5 text-gray-600">{{ $trx->borrowed_at ? $trx->borrowed_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td class="px-5 py-3.5 text-gray-600">{{ $trx->due_at ? $trx->due_at->format('d/m/Y') : '-' }}</td>
                                        <td class="px-5 py-3.5 text-gray-600">{{ $trx->returned_at ? $trx->returned_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td class="px-5 py-3.5 text-center">
                                            @php
                                                $trxClass = match($trx->status) {
                                                    'borrowed' => 'bg-amber-100 text-amber-700',
                                                    'returned' => 'bg-emerald-100 text-emerald-700',
                                                    'overdue'  => 'bg-red-100 text-red-700',
                                                    default    => 'bg-gray-100 text-gray-700',
                                                };
                                            @endphp
                                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded {{ $trxClass }}">{{ match($trx->status) { 'borrowed' => 'Dipinjam', 'returned' => 'Dikembalikan', 'overdue' => 'Terlambat', default => $trx->status } }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-10">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-gray-500 font-medium">Aset ini belum pernah dipinjam.</p>
                        </div>
                        @endif
                    </div>

                    {{-- TAB 3: LOG AKTIVITAS --}}
                    <div x-show="activeTab === 'logs'" x-transition.opacity style="display: none;">
                        @if($asset->logs->count() > 0)
                        <div class="relative pl-6 sm:pl-8 border-l-2 border-purple-100 space-y-8 pb-4">
                            @foreach($asset->logs as $log)
                            <div class="relative">
                                <div class="absolute -left-[35px] sm:-left-[43px] bg-white p-1 rounded-full">
                                    <div class="w-4 h-4 rounded-full {{ $log->status === 'approved' ? 'bg-emerald-500' : ($log->status === 'rejected' ? 'bg-red-500' : 'bg-amber-500') }} ring-4 ring-white shadow-sm"></div>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-600 uppercase">{{ $log->action }}</span>
                                        <span class="text-xs font-bold text-gray-500">•</span>
                                        <span class="text-xs text-gray-500 font-medium">{{ $log->created_at->diffForHumans() }} ({{ $log->created_at->format('H:i') }})</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-800">
                                        Aksi diajukan oleh <strong class="text-purple-600">{{ $log->user->name ?? 'Sistem' }}</strong>.
                                    </p>
                                    @if($log->changes)
                                        <div class="mt-2 bg-gray-50 p-3 rounded-lg border border-gray-100 text-xs font-mono text-gray-600 overflow-x-auto max-h-32 custom-scrollbar">
                                            @php
                                                $changes = json_decode($log->changes, true);
                                            @endphp
                                            @if(is_array($changes))
                                                <ul class="space-y-1">
                                                @foreach($changes as $key => $val)
                                                    <li><span class="text-gray-500">{{ $key }}:</span> {{ is_array($val) ? json_encode($val) : $val }}</li>
                                                @endforeach
                                                </ul>
                                            @else
                                                {{ $log->changes }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-10">
                            <p class="text-gray-500 font-medium">Tidak ada rekaman log untuk aset ini.</p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>

         {{-- Edit Scanner Modal (Verifikasi Barcode sebelum edit) --}}
         <div x-show="showEditScanner" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showEditScanner" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="closeEditScanner()"></div>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div x-show="showEditScanner" x-transition class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-gray-100">
                    <div class="bg-gray-50 px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5v14M7 5v14M11 5v14M14 5v14M18 5v14M20 5v14"></path></svg>
                            Verifikasi Barcode
                        </h3>
                        <button @click="closeEditScanner()" class="text-gray-500 hover:text-red-600 transition-colors cursor-pointer">
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

                        <p class="text-center text-sm mt-3 font-medium" :class="_scanStatus.includes('✅') ? 'text-emerald-600' : _scanStatus.includes('❌') ? 'text-red-600' : 'text-gray-500'" x-text="_scanStatus"></p>
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

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</x-app-layout>
