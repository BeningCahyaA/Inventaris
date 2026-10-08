<x-app-layout>
    <x-slot name="title">Tiket Validasi</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tiket Validasi') }}
        </h2>
        <p class="text-sm text-gray-500 mt-1">Review dan validasi permintaan perubahan atau penghapusan aset dari Admin.</p>
    </x-slot>

    <div class="py-8" x-data="{ 
        showRejectModal: false, 
        rejectId: '', 
        rejectAction: '',
        rejectType: '' 
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Tabs --}}
            <div class="flex items-center gap-2 mb-4 px-4 sm:px-0 overflow-x-auto pb-2 sm:pb-0 hide-scrollbar scroll-smooth">
                <a href="{{ route('approvals.index', ['status' => 'pending']) }}"
                   class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === 'pending' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    Menunggu Validasi
                </a>
                <a href="{{ route('approvals.index', ['status' => 'approved']) }}"
                   class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === 'approved' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    Disetujui
                </a>
                <a href="{{ route('approvals.index', ['status' => 'rejected']) }}"
                   class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === 'rejected' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    Ditolak
                </a>
                <a href="{{ route('approvals.index', ['status' => 'all']) }}"
                   class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === 'all' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    Semua Riwayat
                </a>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $status === 'pending' ? 'Tiket Menunggu Validasi' : ($status === 'approved' ? 'Tiket Disetujui' : ($status === 'rejected' ? 'Tiket Ditolak' : 'Semua Tiket Validasi')) }}
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full shadow-inner">{{ $pendingLogs->total() }} Tiket</span>
                    </div>
                </div>

                {{-- Desktop Table --}}
                <div class="overflow-x-auto hidden md:block">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-gray-100 text-[11px] text-gray-400 uppercase tracking-widest">
                                @if($status === 'pending')
                                <th class="px-6 py-4 w-10">
                                    <input type="checkbox" id="select-all-page" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                </th>
                                @endif
                                <th class="px-6 py-4 font-bold">Waktu Pengajuan</th>
                                <th class="px-6 py-4 font-bold">Data Aset</th>
                                <th class="px-6 py-4 font-bold">Diajukan Oleh</th>
                                <th class="px-6 py-4 font-bold">Tipe Aksi</th>
                                <th class="px-6 py-4 font-bold">Perbandingan Perubahan</th>
                                <th class="px-6 py-4 font-bold text-right">{{ $status === 'pending' ? 'Tindakan Eksekusi' : 'Status' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pendingLogs as $log)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                @if($status === 'pending')
                                <td class="px-6 py-4">
                                    <input type="checkbox" value="{{ $log->log_type }}:{{ $log->id }}" class="ticket-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                </td>
                                @endif
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-[11px] font-mono text-gray-500 mt-0.5">{{ $log->created_at->format('H:i:s A') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->log_type === 'asset')
                                        <div class="text-sm font-extrabold text-gray-900 group-hover:text-indigo-700 transition-colors">{{ $log->asset->name ?? 'Aset Dihapus / Hilang' }}</div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $log->asset->asset_tag ?? '-' }}</div>
                                    @else
                                        <div class="text-sm font-extrabold text-gray-900 group-hover:text-indigo-700 transition-colors">{{ $log->category->name ?? 'Kategori Dihapus' }}</div>
                                        <div class="text-[10px] font-bold text-indigo-600 uppercase mt-0.5">Kategori</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                        <div class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px]">{{ substr($log->user->name ?? 'S', 0, 1) }}</div>
                                        {{ $log->user->name ?? 'Sistem' }}
                                    </div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase mt-1 pl-6">{{ str_replace('_', ' ', $log->user->role ?? '-') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->action === 'update')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 uppercase border border-blue-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit Data
                                        </span>
                                    @elseif($log->action === 'delete')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-bold bg-red-50 text-red-700 uppercase border border-red-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus {{ $log->log_type === 'asset' ? 'Aset' : 'Kategori' }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-700 uppercase">{{ $log->action }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top">
                                    @if($log->action === 'update' && !empty($log->changed_fields))
                                        <div class="space-y-2 max-w-md">
                                            @foreach($log->changed_fields as $field => $values)
                                                <div class="border border-gray-200 rounded-lg p-2 bg-gray-50">
                                                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500 mb-1">{{ str_replace('_', ' ', $field) }}</div>
                                                    <div class="text-[11px] text-red-600"><span class="font-semibold">Lama:</span> {{ is_array($values['old']) ? json_encode($values['old']) : ($values['old'] ?? '-') }}</div>
                                                    <div class="text-[11px] text-emerald-700"><span class="font-semibold">Baru:</span> {{ is_array($values['new']) ? json_encode($values['new']) : ($values['new'] ?? '-') }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($log->action === 'delete')
                                        <span class="text-xs text-red-600 font-semibold">{{ $log->log_type === 'asset' ? 'Aset' : 'Kategori' }} akan dihapus permanen.</span>
                                    @else
                                        <span class="text-xs text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($log->status === 'pending')
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" 
                                                @click="showRejectModal = true; rejectId = '{{ $log->id }}'; rejectAction = '{{ strtoupper($log->action) }}'; rejectType = '{{ $log->log_type }}'"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-red-200 text-red-600 text-xs font-bold rounded-lg hover:bg-red-50 hover:border-red-300 transition shadow-sm cursor-pointer">
                                                Tolak
                                            </button>

                                            <form action="{{ route('approvals.approve', [$log->log_type, $log->id]) }}" method="POST" class="inline form-approve">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 hover:border-emerald-700 transition shadow-sm cursor-pointer">
                                                    Setujui
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($log->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Disetujui
                                        </span>
                                    @elseif($log->status === 'rejected')
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Ditolak
                                            </span>
                                            @if($log->rejection_note)
                                                <p class="text-[10px] text-red-500 italic max-w-[150px] truncate" title="{{ $log->rejection_note }}">{{ $log->rejection_note }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-50 mb-4 shadow-inner border border-emerald-100">
                                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 mb-1">Antrean Kosong</h3>
                                    <p class="text-sm text-gray-500">Tidak ada permintaan yang menunggu untuk divalidasi.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card Layout --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse($pendingLogs as $log)
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                @if($status === 'pending')
                                <input type="checkbox" value="{{ $log->log_type }}:{{ $log->id }}" class="ticket-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                @endif
                                <div>
                                    @if($log->log_type === 'asset')
                                    <div class="font-bold text-sm text-gray-900">{{ $log->asset->name ?? 'Aset Dihapus' }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $log->asset->asset_tag ?? '-' }}</div>
                                    @else
                                    <div class="font-bold text-sm text-gray-900">{{ $log->category->name ?? 'Kategori Dihapus' }}</div>
                                    <div class="text-[10px] font-bold text-indigo-600 uppercase">Kategori</div>
                                    @endif
                                </div>
                            </div>
                            @if($log->action === 'update')
                                <span class="px-2 py-1 rounded text-[10px] font-bold bg-blue-50 text-blue-700 uppercase border border-blue-200">Edit</span>
                            @elseif($log->action === 'delete')
                                <span class="px-2 py-1 rounded text-[10px] font-bold bg-red-50 text-red-700 uppercase border border-red-200">Hapus</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <div class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[8px] font-bold">{{ substr($log->user->name ?? 'S', 0, 1) }}</div>
                                {{ $log->user->name ?? 'Sistem' }}
                            </span>
                            <span>·</span>
                            <span>{{ $log->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        @if($log->action === 'update' && !empty($log->changed_fields))
                            <div class="space-y-1.5">
                                @foreach($log->changed_fields as $field => $values)
                                    <div class="border border-gray-200 rounded p-2 bg-gray-50">
                                        <div class="text-[10px] font-bold uppercase text-gray-500 mb-0.5">{{ str_replace('_', ' ', $field) }}</div>
                                        <div class="text-[11px] text-red-600">Lama: {{ is_array($values['old']) ? json_encode($values['old']) : ($values['old'] ?? '-') }}</div>
                                        <div class="text-[11px] text-emerald-700">Baru: {{ is_array($values['new']) ? json_encode($values['new']) : ($values['new'] ?? '-') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($log->action === 'delete')
                            <div class="text-xs text-red-600 font-semibold bg-red-50 px-2 py-1 rounded">{{ $log->log_type === 'asset' ? 'Aset' : 'Kategori' }} akan dihapus permanen.</div>
                        @endif
                        @if($log->status === 'pending')
                            <div class="flex gap-2">
                                <button type="button" 
                                    @click="showRejectModal = true; rejectId = '{{ $log->id }}'; rejectAction = '{{ strtoupper($log->action) }}'; rejectType = '{{ $log->log_type }}'"
                                    class="flex-1 px-3 py-2 bg-white border border-red-200 text-red-600 text-xs font-bold rounded-lg hover:bg-red-50 transition cursor-pointer text-center">
                                    Tolak
                                </button>
                                <form action="{{ route('approvals.approve', [$log->log_type, $log->id]) }}" method="POST" class="flex-1 form-approve">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition cursor-pointer">
                                        Setujui
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="mt-2 text-right">
                                @if($log->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Disetujui
                                    </span>
                                @elseif($log->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Ditolak
                                    </span>
                                    @if($log->rejection_note)
                                        <p class="text-xs text-red-500 mt-2">Catatan: {{ $log->rejection_note }}</p>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                    @empty
                    <div class="px-4 py-12 text-center">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-50 mb-3">
                            <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 mb-1">Antrean Kosong</h3>
                        <p class="text-xs text-gray-500">Tidak ada permintaan pending.</p>
                    </div>
                    @endforelse
                </div>

                @if($pendingLogs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $pendingLogs->links('pagination.custom') }}
                    </div>
                @endif
                
            </div>

            {{-- Bulk Action Bar (floating) --}}
            <div id="bulk-action-bar"
                 class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-gray-900/90 backdrop-blur-md text-white rounded-2xl shadow-2xl px-6 py-3 flex items-center gap-5 border border-white/10 min-w-[320px] md:min-w-[450px]">
                <div class="flex items-center gap-2">
                    <span class="bg-indigo-600 text-[10px] font-black px-2 py-0.5 rounded-full shadow-lg" id="bulk-count-badge">0</span>
                    <span class="text-sm font-bold tracking-tight" id="bulk-count-label">tiket dipilih</span>
                </div>
                
                <div class="h-6 w-px bg-white/20"></div>

                <div class="flex items-center gap-2">
                    {{-- Bulk Approve Form --}}
                    <form id="bulk-approve-form" method="POST" action="{{ route('approvals.bulk-approve') }}" class="inline">
                        @csrf
                        <div id="bulk-approve-ids"></div>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 shadow-lg shadow-emerald-500/20">
                            Setujui
                        </button>
                    </form>

                    {{-- Bulk Reject Form --}}
                    <form id="bulk-reject-form" method="POST" action="{{ route('approvals.bulk-reject') }}" class="inline">
                        @csrf
                        <div id="bulk-reject-ids"></div>
                        <input type="hidden" name="rejection_note" id="bulk-rejection-note-input">
                        <button type="button" @click="openBulkRejectModal()" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 shadow-lg shadow-red-500/20">
                            Tolak
                        </button>
                    </form>
                </div>

                <button id="bulk-clear-btn" class="ml-auto text-gray-400 hover:text-white transition-colors text-xs font-bold uppercase tracking-widest cursor-pointer">
                    Batal
                </button>
            </div>
        </div>

        {{-- Bulk Reject Reason Modal --}}
        <div x-data="{ bulkNote: '' }" x-show="false" id="bulk-reject-modal" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" onclick="closeBulkRejectModal()"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md border border-gray-100">
                    <h3 class="text-xl font-black text-gray-900 mb-2">Tolak Massal</h3>
                    <p class="text-sm text-gray-500 mb-5">Berikan alasan penolakan untuk <span class="bulk-selected-count font-bold text-red-600"></span> tiket yang dipilih.</p>
                    
                    <textarea id="bulk-rejection-note-textarea" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all placeholder-gray-300" placeholder="Contoh: Lampiran tidak lengkap, data tidak valid..."></textarea>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeBulkRejectModal()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition">Batal</button>
                        <button type="button" onclick="submitBulkReject()" class="px-6 py-2.5 bg-red-600 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-red-500 shadow-lg shadow-red-500/30 transition">Tolak Semua</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showRejectModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showRejectModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="showRejectModal = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div x-show="showRejectModal" x-transition class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-gray-100 p-6">
                    
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0 border border-red-200">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900">Tolak Permintaan</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Anda akan menolak aksi <span x-text="rejectAction" class="font-bold text-red-600"></span> ini.</p>
                        </div>
                    </div>
                    
                    <form method="POST" :action="`/approvals/${rejectType}/${rejectId}/reject`">
                        @csrf
                        <div class="mb-5 bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Catatan Penolakan (Opsional)</label>
                            <textarea name="rejection_note" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition shadow-sm" placeholder="Berikan alasan spesifik kepada Admin mengapa tiket ini ditolak..."></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showRejectModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit" class="px-5 py-2.5 bg-red-600 border border-red-600 rounded-lg text-sm font-bold text-white hover:bg-red-700 transition shadow-md">Tolak & Kembalikan</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        let selectedIds = new Set();
        const bulkBar = document.getElementById('bulk-action-bar');
        const bulkCountBadge = document.getElementById('bulk-count-badge');
        const bulkCountLbl = document.getElementById('bulk-count-label');
        const selectAll = document.getElementById('select-all-page');
        const checkboxes = document.querySelectorAll('.ticket-checkbox');
        const approveIdsContainer = document.getElementById('bulk-approve-ids');
        const rejectIdsContainer = document.getElementById('bulk-reject-ids');
        const bulkRejectModalLayout = document.getElementById('bulk-reject-modal');
        const bulkRejectionNoteInput = document.getElementById('bulk-rejection-note-input');

        function syncBulkBar() {
            const count = selectedIds.size;
            if (count > 0) {
                bulkBar.classList.remove('hidden');
                bulkBar.classList.add('flex');
                bulkCountBadge.textContent = count;
                bulkCountLbl.textContent = count > 1 ? 'tiket dipilih' : 'tiket dipilih';
                
                // Update hidden inputs
                [approveIdsContainer, rejectIdsContainer].forEach(container => {
                    container.innerHTML = '';
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'selected_ids[]';
                        input.value = id;
                        container.appendChild(input);
                    });
                });
            } else {
                bulkBar.classList.add('hidden');
                bulkBar.classList.remove('flex');
            }
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) selectedIds.add(this.value);
                else selectedIds.delete(this.value);
                syncBulkBar();
                
                // Update select all state
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                if (selectAll) selectAll.checked = allChecked;
            });
        });

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    if (this.checked) selectedIds.add(cb.value);
                    else selectedIds.delete(cb.value);
                });
                syncBulkBar();
            });
        }

        document.getElementById('bulk-clear-btn')?.addEventListener('click', function() {
            selectedIds.clear();
            checkboxes.forEach(cb => cb.checked = false);
            if (selectAll) selectAll.checked = false;
            syncBulkBar();
        });

        // Bulk Approve Confirmation
        document.getElementById('bulk-approve-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Setujui Massal?',
                text: `Anda akan menyetujui ${selectedIds.size} tiket permintaan secara massal.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Setujui Semua',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Bulk Reject Modals Logic
        window.openBulkRejectModal = function() {
            document.querySelectorAll('.bulk-selected-count').forEach(el => el.textContent = selectedIds.size);
            const bulkRejectModal = document.getElementById('bulk-reject-modal');
            bulkRejectModal.style.display = 'block';
            setTimeout(() => bulkRejectModal.classList.add('show'), 10);
        };

        window.closeBulkRejectModal = function() {
            const bulkRejectModal = document.getElementById('bulk-reject-modal');
            bulkRejectModal.style.display = 'none';
        };

        window.submitBulkReject = function() {
            const noteArea = document.getElementById('bulk-rejection-note-textarea');
            const note = noteArea.value.trim();
            const bulkRejectionNoteInput = document.getElementById('bulk-rejection-note-input');
            bulkRejectionNoteInput.value = note || 'Ditolak secara massal oleh Super Admin.';
            document.getElementById('bulk-reject-form').submit();
        };
    </script>
    @endpush
</x-app-layout>