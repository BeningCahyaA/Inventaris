<x-app-layout>
    <x-slot name="title">Scanner Aset</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">QR Scanner Aset</h2>
        <p class="text-sm text-gray-500 mt-1">Akses cepat untuk pindai aset</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="assetScanner(@json(auth()->user()->role === 'admin'), '{{ route('scanner.resolve') }}', '{{ route('scanner.status') }}', '{{ route('scanner.quick-edit') }}')">

            {{-- Scanner card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Kamera Scanner</h3>
                    <button @click="toggleScanner()"
                            class="text-sm font-semibold px-3 py-1.5 rounded-lg transition cursor-pointer"
                            :class="scanning ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-indigo-100 text-indigo-600 hover:bg-indigo-200'">
                        <span x-text="scanning ? 'Stop Kamera' : 'Buka Kamera'"></span>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Camera viewport --}}
                    <div id="qr-reader"
                         class="w-full rounded-xl overflow-hidden border-2 transition"
                         :class="scanning ? 'border-indigo-400' : 'border-dashed border-gray-300 min-h-[200px] flex items-center justify-center'"
                         x-show="scanning || !assetData">
                        <template x-if="!scanning">
                            <div class="text-center text-gray-400 py-12">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                <p class="text-sm">Klik "Buka Kamera" untuk mulai scan</p>
                            </div>
                        </template>
                    </div>

                    {{-- Manual input --}}
                    <div class="mt-4 flex gap-2">
                        <input type="text" x-model="manualTag"
                               placeholder="Atau ketik Asset Tag (misal: NB-26-001)"
                               @keyup.enter="resolveTag(manualTag)"
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <button @click="resolveTag(manualTag)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition cursor-pointer">
                            Cari
                        </button>
                    </div>
                </div>
            </div>

            {{-- Loading --}}
            <div x-show="loading" class="text-center text-indigo-600 font-semibold py-4">
                <svg class="animate-spin w-6 h-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Memuat data aset...
            </div>

            {{-- Error --}}
            <div x-show="errorMsg" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-sm font-semibold" x-text="errorMsg"></div>

            {{-- Asset summary card --}}
            <div x-show="assetData" x-cloak class="bg-white rounded-xl border border-indigo-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-indigo-100 bg-indigo-50/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg" x-text="assetData?.name"></h3>
                            <p class="text-xs text-gray-500 font-mono mt-0.5" x-text="assetData?.asset_tag"></p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase"
                              :class="{
                                  'bg-green-100 text-green-700': assetData?.status === 'available',
                                  'bg-blue-100 text-blue-700': assetData?.status === 'in_use',
                                  'bg-amber-100 text-amber-700': assetData?.status === 'maintenance',
                                  'bg-red-100 text-red-700': assetData?.status === 'broken',
                              }"
                              x-text="statusLabel(assetData?.status)">
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-gray-400">Kategori:</span> <span class="font-semibold text-gray-800" x-text="assetData?.category ?? '-'"></span></div>
                        <div><span class="text-gray-400">Lokasi:</span> <span class="font-semibold text-gray-800" x-text="assetData?.location ?? '-'"></span></div>
                        <div><span class="text-gray-400">Kondisi:</span> <span class="font-semibold text-gray-800" x-text="assetData?.condition ?? '-'"></span></div>
                        <template x-if="assetData?.borrower">
                            <div><span class="text-gray-400">Peminjam:</span> <span class="font-semibold text-gray-800" x-text="assetData?.borrower"></span></div>
                        </template>
                        <template x-if="assetData?.due_at">
                            <div><span class="text-gray-400">Jatuh tempo:</span> <span class="font-semibold text-red-600" x-text="assetData?.due_at"></span></div>
                        </template>
                    </div>

                    {{-- Action buttons --}}
                    <div class="pt-3 border-t border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Aksi</p>
                        <template x-if="validActions?.length === 0">
                            <p class="text-sm text-gray-400 italic">Tidak ada aksi yang tersedia untuk status ini.</p>
                        </template>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="act in validActions" :key="act">
                                <button @click="selectedAction = act; showActionForm = true"
                                        class="px-4 py-2 rounded-lg text-sm font-semibold transition cursor-pointer border"
                                        :class="{
                                            'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700': act === 'check_out',
                                            'bg-blue-600 text-white border-blue-600 hover:bg-blue-700': act === 'check_in',
                                            'bg-amber-500 text-white border-amber-500 hover:bg-amber-600': act === 'maintenance',
                                        }"
                                        x-text="actionLabel(act)">
                                </button>
                            </template>
                            {{-- Quick edit — selalu tampil saat aset ditemukan --}}
                            <button @click="openEditForm()"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold transition cursor-pointer border border-gray-300 bg-white text-gray-700 hover:bg-gray-100">
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action form modal overlay --}}
            <div x-show="showActionForm" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                 @click.self="showActionForm = false">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h4 class="font-bold text-gray-800" x-text="'Konfirmasi: ' + actionLabel(selectedAction)"></h4>
                        <button @click="showActionForm = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">✕</button>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-600">
                            Aset: <span class="font-bold" x-text="assetData?.name"></span>
                        </p>

                        <template x-if="selectedAction === 'check_out'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Nama Peminjam</label>
                                    <input type="text" x-model="form.borrower_name" placeholder="Nama lengkap peminjam"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal Jatuh Tempo</label>
                                    <input type="date" x-model="form.due_at"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                </div>
                            </div>
                        </template>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Catatan (opsional)</label>
                            <textarea x-model="form.notes" rows="2" placeholder="Catatan tambahan..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"></textarea>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button @click="submitAction()"
                                    :disabled="submitting"
                                    class="flex-1 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:opacity-60 transition cursor-pointer">
                                <span x-text="submitting ? 'Memproses...' : 'Konfirmasi'"></span>
                            </button>
                            <button @click="showActionForm = false"
                                    class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-50 transition cursor-pointer">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Edit modal --}}
            <div x-show="showEditForm" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                 @click.self="showEditForm = false">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden flex flex-col" style="max-height:90vh">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                        <div>
                            <h4 class="font-bold text-gray-800">Edit Cepat Aset</h4>
                            <p class="text-xs text-gray-500 font-mono mt-0.5" x-text="assetData?.asset_tag"></p>
                        </div>
                        <button @click="showEditForm = false" class="text-gray-400 hover:text-gray-600 cursor-pointer text-xl leading-none">✕</button>
                    </div>

                    <div class="p-6 space-y-3 overflow-y-auto flex-1">
                        <template x-if="isAdmin">
                            <p class="text-xs bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-amber-700">
                                ⚠️ Sebagai Admin, perubahan akan masuk antrean persetujuan Super Admin.
                            </p>
                        </template>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Nama Aset</label>
                            <input type="text" x-model="editForm.name" placeholder="Nama aset"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Lokasi</label>
                            <input type="text" x-model="editForm.location" placeholder="Lokasi penyimpanan"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Penanggung Jawab</label>
                            <input type="text" x-model="editForm.person_in_charge" placeholder="Nama penanggung jawab"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Kategori</label>
                            <input type="text" x-model="editForm.category" placeholder="Kategori aset"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Kondisi</label>
                            <select x-model="editForm.condition"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white">
                                <option value="">— tidak diubah —</option>
                                <option value="baik">Baik</option>
                                <option value="rusak">Rusak</option>
                                <option value="rusak_total">Rusak Total</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Deskripsi</label>
                            <textarea x-model="editForm.description" rows="3" placeholder="Deskripsi aset (opsional)"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"></textarea>
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-4 border-t border-gray-100 flex gap-2 flex-shrink-0">
                        <button @click="submitEdit()"
                                :disabled="editSubmitting"
                                class="flex-1 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:opacity-60 transition cursor-pointer">
                            <span x-text="editSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                        </button>
                        <button @click="showEditForm = false"
                                class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-50 transition cursor-pointer">
                            Batal
                        </button>
                    </div>
                </div>
            </div>

            {{-- Success toast --}}
            <div x-show="successMsg" x-cloak x-transition
                 class="fixed bottom-6 right-6 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm z-50"
                 x-text="successMsg">
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    function assetScanner(isAdminFlag, resolveUrl, statusUrl, quickEditUrl) {
        return {
            scanning: false,
            loading: false,
            manualTag: '',
            assetData: null,
            validActions: [],
            selectedAction: null,
            showActionForm: false,
            submitting: false,
            showEditForm: false,
            editSubmitting: false,
            editForm: { name: '', location: '', person_in_charge: '', category: '', condition: '', description: '' },
            errorMsg: '',
            successMsg: '',
            form: { borrower_name: '', due_at: '', notes: '' },
            html5QrCode: null,
            isAdmin: isAdminFlag,

            toggleScanner() {
                if (this.scanning) {
                    this.stopScanner();
                } else {
                    this.startScanner();
                }
            },

            startScanner() {
                this.scanning = true;
                this.$nextTick(() => {
                    this.html5QrCode = new Html5Qrcode('qr-reader');
                    this.html5QrCode.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            this.stopScanner();
                            // Extract asset_tag from URL or use raw text
                            const tag = this.extractTag(decodedText);
                            this.resolveTag(tag);
                        },
                        () => {}
                    ).catch(() => {
                        this.scanning = false;
                        this.errorMsg = 'Kamera tidak dapat diakses. Gunakan input manual.';
                    });
                });
            },

            stopScanner() {
                if (this.html5QrCode) {
                    this.html5QrCode.stop().then(() => {
                        this.html5QrCode.clear();
                    }).catch(() => {});
                }
                this.scanning = false;
            },

            extractTag(text) {
                // Support QR that encodes full URL ending with asset_tag query param
                try {
                    const url = new URL(text);
                    const tag = url.searchParams.get('tag') || url.searchParams.get('asset_tag');
                    if (tag) return tag;
                } catch (_) {}
                return text.trim();
            },

            async resolveTag(tag) {
                if (!tag) return;
                this.assetData = null;
                this.validActions = [];
                this.errorMsg = '';
                this.loading = true;

                try {
                    const resp = await fetch(resolveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ asset_tag: tag }),
                    });
                    const data = await resp.json();

                    if (!data.found) {
                        this.errorMsg = data.message;
                    } else {
                        this.assetData    = data.asset;
                        this.validActions = data.valid_actions;
                        this.manualTag    = '';
                    }
                } catch (e) {
                    this.errorMsg = 'Terjadi kesalahan jaringan.';
                } finally {
                    this.loading = false;
                }
            },

            async submitAction() {
                this.submitting = true;
                this.errorMsg   = '';

                try {
                    const payload = {
                        asset_tag: this.assetData.asset_tag,
                        action: this.selectedAction,
                        ...this.form,
                    };

                    const resp = await fetch(statusUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await resp.json();

                    if (data.success) {
                        this.showActionForm = false;
                        this.successMsg     = data.message;
                        this.assetData.status = data.new_status;
                        // Re-resolve to refresh valid actions
                        await this.resolveTag(this.assetData.asset_tag);
                        this.form = { borrower_name: '', due_at: '', notes: '' };
                        setTimeout(() => { this.successMsg = ''; }, 3500);
                    } else {
                        this.errorMsg = data.message;
                        this.showActionForm = false;
                    }
                } catch (e) {
                    this.errorMsg = 'Terjadi kesalahan jaringan.';
                } finally {
                    this.submitting = false;
                }
            },

            openEditForm() {
                this.editForm = {
                    name:             this.assetData?.name ?? '',
                    location:         this.assetData?.location ?? '',
                    person_in_charge: this.assetData?.person_in_charge ?? '',
                    category:         this.assetData?.category ?? '',
                    condition:        this.assetData?.condition ?? '',
                    description:      this.assetData?.description ?? '',
                };
                this.showEditForm = true;
            },

            async submitEdit() {
                this.editSubmitting = true;
                this.errorMsg = '';

                try {
                    const resp = await fetch(quickEditUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            asset_id: this.assetData.id,
                            ...this.editForm,
                        }),
                    });
                    const data = await resp.json();

                    if (data.success) {
                        this.showEditForm = false;
                        this.successMsg = data.message;
                        // Perbarui data lokal jika super_admin langsung update
                        if (data.asset) {
                            Object.assign(this.assetData, data.asset);
                        }
                        setTimeout(() => { this.successMsg = ''; }, 3500);
                    } else {
                        this.errorMsg = data.message ?? 'Terjadi kesalahan.';
                    }
                } catch (e) {
                    this.errorMsg = 'Terjadi kesalahan jaringan.';
                } finally {
                    this.editSubmitting = false;
                }
            },

            statusLabel(status) {
                const map = { available: 'Available', in_use: 'In Use', maintenance: 'Maintenance', broken: 'Broken' };
                return map[status] ?? status;
            },

            actionLabel(act) {
                const map = { check_out: 'Check Out', check_in: 'Check In', maintenance: 'Maintenance' };
                return map[act] ?? act;
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
