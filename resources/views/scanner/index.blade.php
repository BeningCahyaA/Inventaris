<x-app-layout>
    <x-slot name="title">Scanner Aset</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Barcode Scanner Aset
        </h2>
        <p class="text-sm text-gray-500 mt-1">Akses cepat untuk pindai aset</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="assetScanner(@json(auth()->user()->role === 'admin'), '{{ route('scanner.resolve') }}', '{{ route('scanner.status') }}', '{{ route('scanner.quick-edit') }}')">

            {{-- Scanner card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Kamera Scanner</h3>
                    <button @click="toggleScanner()"
                        class="text-sm font-semibold px-3 py-1.5 rounded-lg transition cursor-pointer"
                        :class="scanning ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-purple-100 text-purple-600 hover:bg-purple-200'">
                        <span x-text="scanning ? 'Stop Kamera' : 'Buka Kamera'"></span>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Camera viewport --}}
                    <div class="w-full rounded-xl overflow-hidden border-2 transition"
                        :class="scanning ? 'border-purple-400' : 'border-dashed border-gray-300'"
                        x-show="scanning || !assetData">

                        {{-- Video kamera + garis bantu --}}
                        <div x-show="scanning" class="relative bg-black">
                            <video x-ref="video" playsinline muted autoplay
                                class="w-full block" style="max-height:60vh; object-fit:cover;"></video>
                            <div class="absolute pointer-events-none border-2 border-white/80 rounded-lg"
                                style="left:5%; right:5%; top:30%; bottom:30%;"></div>
                            <p class="absolute bottom-2 inset-x-0 text-center text-xs text-white/90">
                                Arahkan barcode ke dalam kotak
                            </p>
                        </div>

                        {{-- Placeholder --}}
                        <div x-show="!scanning" class="text-center text-gray-400 py-12">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7V4h3m10 0h3v3M4 17v3h3m10 0h3v-3M7 8v8m3-8v8m4-8v8m3-8v8" />
                            </svg>
                            <p class="text-sm">Klik "Buka Kamera" untuk mulai scan</p>
                        </div>
                    </div>

                    {{-- Manual input --}}
                    <div class="mt-4 flex gap-2">
                        <input type="text" x-model="manualTag"
                            placeholder="Atau ketik Asset Tag (misal: NB-26-001)"
                            @keyup.enter="resolveTag(manualTag)"
                            class="min-w-0 flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none">
                        <button @click="resolveTag(manualTag)"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition cursor-pointer">
                            Cari
                        </button>
                    </div>
                </div>
            </div>

            {{-- Loading --}}
            <div x-show="loading" class="text-center text-purple-600 font-semibold py-4">
                <svg class="animate-spin w-6 h-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Memuat data aset...
            </div>

            {{-- Error --}}
            <div x-show="errorMsg" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-sm font-semibold" x-text="errorMsg"></div>

            {{-- Asset summary card --}}
            <div x-show="assetData && !loading" x-cloak aria-label="Hasil scan barcode" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-purple-50/50">
                    <p class="text-xs font-semibold text-purple-700 mb-2">Hasil scan · Aset ditemukan</p>
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900 text-lg leading-snug break-words" x-text="assetData?.name"></h3>
                            <p class="text-xs text-gray-600 mt-2">Kode aset <span class="inline-block font-mono font-semibold text-purple-700 bg-white border border-purple-100 rounded px-2 py-1 ml-1 break-all" x-text="assetData?.asset_tag"></span></p>
                        </div>
                        <span class="self-start shrink-0 px-2.5 py-1 rounded-full text-xs font-semibold"
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

                <div class="p-4 sm:p-6 space-y-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div class="min-w-0"><dt class="text-xs text-gray-500 mb-1">Kategori</dt><dd class="font-semibold text-gray-800 break-words" x-text="detailLabel(assetData?.category)"></dd></div>
                        <div class="min-w-0"><dt class="text-xs text-gray-500 mb-1">Lokasi</dt><dd class="font-semibold text-gray-800 break-words" x-text="assetData?.location || '-'"></dd></div>
                        <div class="min-w-0"><dt class="text-xs text-gray-500 mb-1">Kondisi</dt><dd class="font-semibold text-gray-800 break-words" x-text="detailLabel(assetData?.condition)"></dd></div>
                        <div class="min-w-0"><dt class="text-xs text-gray-500 mb-1">Penanggung jawab (PIC)</dt><dd class="font-semibold text-gray-800 break-words" x-text="assetData?.person_in_charge || '-'"></dd></div>
                        <template x-if="assetData?.borrower">
                            <div class="min-w-0"><dt class="text-xs text-gray-500 mb-1">Peminjam</dt><dd class="font-semibold text-gray-800 break-words" x-text="assetData?.borrower"></dd></div>
                        </template>
                        <template x-if="assetData?.due_at">
                            <div><dt class="text-xs text-gray-500 mb-1">Jatuh tempo</dt><dd class="font-semibold text-gray-800" x-text="assetData?.due_at"></dd></div>
                        </template>
                    </dl>
                    <template x-if="assetData?.description">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-500 mb-1">Deskripsi</p>
                            <p class="text-sm text-gray-700 whitespace-pre-line break-words" x-text="assetData?.description"></p>
                        </div>
                    </template>

                    {{-- Action buttons --}}
                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 mb-3">Tindakan aset</p>
                        <template x-if="validActions?.length === 0">
                            <p class="text-sm text-gray-400 italic">Tidak ada aksi yang tersedia untuk status ini.</p>
                        </template>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="act in validActions" :key="act">
                                <button @click="selectedAction = act; showActionForm = true"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold transition cursor-pointer border"
                                    :class="{
                                            'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700': act === 'check_out',
                                            'bg-purple-600 text-white border-purple-600 hover:bg-purple-700': act === 'check_in',
                                            'bg-amber-500 text-white border-amber-500 hover:bg-amber-600': act === 'maintenance',
                                        }"
                                    x-text="actionLabel(act)">
                                </button>
                            </template>
                            {{-- Quick edit — selalu tampil saat aset ditemukan --}}
                            <button x-show="assetData?.status !== 'sold'" @click="openEditForm()"
                                class="px-4 py-2 rounded-lg text-sm font-semibold transition cursor-pointer border border-gray-300 bg-white text-gray-700 hover:bg-gray-100">
                                Edit aset
                            </button>
                        </div>
                        <button type="button" @click="startScanner()" :disabled="scanning" class="mt-3 w-full px-4 py-2 rounded-lg text-sm font-semibold text-purple-700 border border-purple-200 hover:bg-purple-50 disabled:opacity-50 transition cursor-pointer">
                            <span x-text="scanning ? 'Kamera sedang aktif' : 'Scan barang berikutnya'"></span>
                        </button>
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
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal Jatuh Tempo</label>
                                    <input type="date" x-model="form.due_at"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none">
                                </div>
                            </div>
                        </template>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Catatan (opsional)</label>
                            <textarea x-model="form.notes" rows="2" placeholder="Catatan tambahan..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none resize-none"></textarea>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button @click="submitAction()"
                                :disabled="submitting"
                                class="flex-1 py-2 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 disabled:opacity-60 transition cursor-pointer">
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
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Lokasi</label>
                            <input type="text" x-model="editForm.location" placeholder="Lokasi penyimpanan"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Penanggung Jawab</label>
                            <input type="text" x-model="editForm.person_in_charge" placeholder="Nama penanggung jawab"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Kategori</label>
                            <input type="text" x-model="editForm.category" placeholder="Kategori aset"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Kondisi</label>
                            <select x-model="editForm.condition"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none bg-white">
                                <option value="">— tidak diubah —</option>
                                <option value="baik">Baik</option>
                                <option value="rusak">Rusak</option>
                                <option value="rusak_total">Rusak Total</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Deskripsi</label>
                            <textarea x-model="editForm.description" rows="3" placeholder="Deskripsi aset (opsional)"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none resize-none"></textarea>
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-4 border-t border-gray-100 flex gap-2 flex-shrink-0">
                        <button @click="submitEdit()"
                            :disabled="editSubmitting"
                            class="flex-1 py-2 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 disabled:opacity-60 transition cursor-pointer">
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
    <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js"></script>
    <script>
        function assetScanner(isAdminFlag, resolveUrl, statusUrl, quickEditUrl) {
            // Disimpan di luar state Alpine agar tidak dibungkus Proxy
            let stream = null;
            let timer = null;
            let detector = null;
            let zxingReader = null;
            let handled = false;
            let busy = false;
            const canvas = document.createElement('canvas');

            const NATIVE_FORMATS = ['code_128', 'code_39', 'ean_13', 'ean_8', 'upc_a', 'qr_code'];

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
                editForm: {
                    name: '',
                    location: '',
                    person_in_charge: '',
                    category: '',
                    condition: '',
                    description: ''
                },
                errorMsg: '',
                successMsg: '',
                form: {
                    borrower_name: '',
                    due_at: '',
                    notes: ''
                },
                isAdmin: isAdminFlag,

                toggleScanner() {
                    if (this.scanning) {
                        this.stopScanner();
                    } else {
                        this.startScanner();
                    }
                },

                async initDecoder() {
                    // 1) BarcodeDetector bawaan browser (Chrome Android, dll.)
                    if ('BarcodeDetector' in window) {
                        try {
                            const supported = await BarcodeDetector.getSupportedFormats();
                            const formats = NATIVE_FORMATS.filter(f => supported.includes(f));
                            if (formats.length) {
                                detector = new BarcodeDetector({
                                    formats
                                });
                                return;
                            }
                        } catch (_) {}
                    }

                    // 2) Fallback ZXing
                    const Z = window.ZXing;
                    const hints = new Map();
                    hints.set(Z.DecodeHintType.POSSIBLE_FORMATS, [
                        Z.BarcodeFormat.CODE_128,
                        Z.BarcodeFormat.CODE_39,
                        Z.BarcodeFormat.EAN_13,
                        Z.BarcodeFormat.EAN_8,
                        Z.BarcodeFormat.UPC_A,
                        Z.BarcodeFormat.QR_CODE,
                    ]);
                    hints.set(Z.DecodeHintType.TRY_HARDER, true);
                    zxingReader = new Z.MultiFormatReader();
                    zxingReader.setHints(hints);
                },

                startScanner() {
                    this.scanning = true;
                    this.errorMsg = '';
                    handled = false;
                    busy = false;

                    this.$nextTick(async () => {
                        try {
                            if (!window.isSecureContext) {
                                throw new Error('Kamera hanya bisa dipakai lewat HTTPS (atau localhost).');
                            }
                            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                throw new Error('Browser tidak mendukung akses kamera.');
                            }

                            await this.initDecoder();

                            stream = await navigator.mediaDevices.getUserMedia({
                                audio: false,
                                video: {
                                    facingMode: {
                                        ideal: 'environment'
                                    },
                                    width: {
                                        ideal: 1920
                                    },
                                    height: {
                                        ideal: 1080
                                    },
                                },
                            });

                            // Coba aktifkan autofocus kontinu (diabaikan jika tidak didukung)
                            try {
                                const track = stream.getVideoTracks()[0];
                                const caps = track.getCapabilities ? track.getCapabilities() : {};
                                if (caps.focusMode && caps.focusMode.includes('continuous')) {
                                    await track.applyConstraints({
                                        advanced: [{
                                            focusMode: 'continuous'
                                        }]
                                    });
                                }
                            } catch (_) {}

                            const video = this.$refs.video;
                            video.srcObject = stream;
                            await video.play();

                            this.scanLoop();

                        } catch (error) {
                            console.error('Scanner error:', error);
                            this.releaseCamera();
                            this.scanning = false;

                            if (error?.name === 'NotAllowedError') {
                                this.errorMsg = 'Akses kamera ditolak. Izinkan kamera pada pengaturan browser.';
                            } else if (error?.name === 'NotFoundError') {
                                this.errorMsg = 'Kamera tidak ditemukan pada perangkat.';
                            } else if (error?.name === 'NotReadableError') {
                                this.errorMsg = 'Kamera sedang digunakan oleh aplikasi lain.';
                            } else if (error?.message) {
                                this.errorMsg = error.message;
                            } else {
                                this.errorMsg = 'Kamera tidak dapat diakses. Periksa izin kamera browser.';
                            }
                        }
                    });
                },

                scanLoop() {
                    if (!this.scanning || handled) return;

                    const video = this.$refs.video;

                    if (!busy && video && video.readyState >= 2 && video.videoWidth > 0) {
                        busy = true;
                        this.decodeFrame(video)
                            .then((text) => {
                                if (text && !handled) {
                                    handled = true;
                                    this.stopScanner();
                                    this.resolveTag(this.extractTag(text));
                                }
                            })
                            .catch(() => {})
                            .finally(() => {
                                busy = false;
                            });
                    }

                    timer = setTimeout(() => this.scanLoop(), 80);
                },

                async decodeFrame(video) {
                    // Decoder bawaan browser: baca frame video resolusi penuh
                    if (detector) {
                        const codes = await detector.detect(video);
                        return codes.length ? codes[0].rawValue : null;
                    }

                    // ZXing: gambar frame ke canvas pada resolusi kamera (maks 1280px)
                    const vw = video.videoWidth;
                    const vh = video.videoHeight;
                    const scale = Math.min(1, 1280 / Math.max(vw, vh));
                    canvas.width = Math.floor(vw * scale);
                    canvas.height = Math.floor(vh * scale);

                    const ctx = canvas.getContext('2d', {
                        willReadFrequently: true
                    });
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const Z = window.ZXing;
                    try {
                        const source = new Z.HTMLCanvasElementLuminanceSource(canvas);
                        const bitmap = new Z.BinaryBitmap(new Z.HybridBinarizer(source));
                        return zxingReader.decodeWithState(bitmap).getText();
                    } catch (_) {
                        return null; // belum ditemukan di frame ini
                    }
                },

                releaseCamera() {
                    clearTimeout(timer);
                    timer = null;
                    if (stream) {
                        stream.getTracks().forEach(t => t.stop());
                        stream = null;
                    }
                    const video = this.$refs.video;
                    if (video) video.srcObject = null;
                    detector = null;
                    zxingReader = null;
                },

                stopScanner() {
                    this.scanning = false;
                    this.releaseCamera();
                },

                extractTag(text) {
                    // Support QR yang meng-encode URL lengkap dengan query param tag/asset_tag
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
                            body: JSON.stringify({
                                asset_tag: tag
                            }),
                        });
                        const data = await resp.json();

                        if (!data.found) {
                            this.errorMsg = data.message;
                        } else {
                            this.assetData = data.asset;
                            this.validActions = data.valid_actions;
                            this.manualTag = '';
                        }
                    } catch (e) {
                        this.errorMsg = 'Terjadi kesalahan jaringan.';
                    } finally {
                        this.loading = false;
                    }
                },

                async submitAction() {
                    this.submitting = true;
                    this.errorMsg = '';

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
                            this.successMsg = data.message;
                            this.assetData.status = data.new_status;
                            // Re-resolve untuk refresh valid actions
                            await this.resolveTag(this.assetData.asset_tag);
                            this.form = {
                                borrower_name: '',
                                due_at: '',
                                notes: ''
                            };
                            setTimeout(() => {
                                this.successMsg = '';
                            }, 3500);
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
                        name: this.assetData?.name ?? '',
                        location: this.assetData?.location ?? '',
                        person_in_charge: this.assetData?.person_in_charge ?? '',
                        category: this.assetData?.category ?? '',
                        condition: this.assetData?.condition ?? '',
                        description: this.assetData?.description ?? '',
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
                            setTimeout(() => {
                                this.successMsg = '';
                            }, 3500);
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
                    const map = {
                        available: 'Available',
                        in_use: 'In Use',
                        maintenance: 'Maintenance',
                        broken: 'Broken',
                        sold: 'Dijual · Arsip'
                    };
                    return map[status] ?? status;
                },

                detailLabel(value) {
                    if (!value) return '-';
                    return String(value).replace(/[_-]+/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase());
                },

                actionLabel(act) {
                    const map = {
                        check_out: 'Check Out',
                        check_in: 'Check In',
                        maintenance: 'Maintenance'
                    };
                    return map[act] ?? act;
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
