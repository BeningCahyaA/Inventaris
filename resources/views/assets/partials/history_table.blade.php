<div class="management-panel bg-white overflow-hidden">
    <div class="management-panel-heading"><h3>Daftar peminjaman</h3><span>{{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi</span></div>
    {{-- Desktop Table --}}
    <div class="overflow-x-auto hidden md:block">
        <table class="management-table history-table w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/80 text-gray-500 text-[11px] font-extrabold uppercase tracking-wider border-b border-gray-100">
                    <th class="px-5 py-4">Aset</th>
                    <th class="px-5 py-4">Peminjam</th>
                    <th class="px-5 py-4">Dipinjam</th>
                    <th class="px-5 py-4">Jatuh Tempo</th>
                    <th class="px-5 py-4 text-center">Durasi</th>
                    <th class="px-5 py-4 text-center">Status</th>
                    <th class="px-5 py-4">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($transactions as $transaction)
                    @php
                        $statusClass = match($transaction->status) {
                            'borrowed' => 'bg-amber-100 text-amber-700 border border-amber-200',
                            'returned' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                            'overdue' => 'bg-red-100 text-red-700 border border-red-200',
                            default => 'bg-gray-100 text-gray-700 border border-gray-200',
                        };
                    @endphp
                    <tr class="hover:bg-indigo-50/40 transition-colors duration-200 group">
                        <td class="px-5 py-4">
                            <div class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $transaction->asset->name ?? '-' }}</div>
                            <div class="text-[10px] text-gray-500 font-mono mt-0.5">{{ $transaction->asset->asset_tag ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700 font-medium">{{ $transaction->borrower_name }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600">
                            {{ $transaction->borrowed_at?->format('d M Y') }}
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $transaction->borrowed_at?->format('H:i') }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600">
                            {{ $transaction->due_at?->format('d M Y') }}
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $transaction->due_at?->format('H:i') }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600 text-center font-semibold">
                            {{ $transaction->duration_days ? $transaction->duration_days . ' hari' : '-' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="management-badge inline-block px-3 py-1 text-[10px] font-bold uppercase rounded-full {{ $statusClass }}">{{ match($transaction->status) { 'borrowed' => 'Sedang dipinjam', 'returned' => 'Sudah kembali', 'overdue' => 'Terlambat', default => $transaction->status } }}</span>
                        </td>
                        <td class="px-5 py-4 history-notes text-sm text-gray-500 max-w-xs" title="{{ $transaction->notes }}">
                            {{ $transaction->notes ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            Belum ada riwayat transaksi yang cocok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card Layout --}}
    <div class="md:hidden divide-y divide-gray-100">
        @forelse($transactions as $transaction)
            @php
                $statusColor = match($transaction->status) {
                    'borrowed' => 'bg-amber-100 text-amber-700',
                    'returned' => 'bg-emerald-100 text-emerald-700',
                    'overdue' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700',
                };
                $prefix = strtoupper(preg_replace('/[^A-Za-z].*/', '', $transaction->asset->asset_tag ?? 'AS'));
            @endphp
            <div class="px-4 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3 mb-3">
                    <div class="shrink-0 w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shadow-sm">
                        {{ $prefix }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 text-sm truncate">{{ $transaction->asset->name ?? '-' }}</h4>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-[10px] text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ $transaction->asset->asset_tag ?? '-' }}</span>
                            <span class="management-badge px-2 py-0.5 text-[9px] font-bold uppercase rounded-full {{ $statusColor }}">{{ match($transaction->status) { 'borrowed' => 'Sedang dipinjam', 'returned' => 'Sudah kembali', 'overdue' => 'Terlambat', default => $transaction->status } }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-[11px] text-gray-500 bg-gray-50/50 p-2.5 rounded-lg border border-gray-100">
                    <div class="space-y-1">
                        <label class="block text-[9px] uppercase tracking-wider font-bold text-gray-400">Peminjam</label>
                        <div class="text-gray-900 font-semibold">{{ $transaction->borrower_name }}</div>
                    </div>
                    <div class="space-y-1 text-right">
                        <label class="block text-[9px] uppercase tracking-wider font-bold text-gray-400">Durasi</label>
                        <div class="text-indigo-600 font-bold">{{ $transaction->duration_days ? $transaction->duration_days . ' hari' : '-' }}</div>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[9px] uppercase tracking-wider font-bold text-gray-400">Waktu Pinjam</label>
                        <div class="text-gray-700">{{ $transaction->borrowed_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="space-y-1 text-right">
                        <label class="block text-[9px] uppercase tracking-wider font-bold text-gray-400">Jatuh Tempo</label>
                        <div class="text-gray-700">{{ $transaction->due_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @if($transaction->notes)
                    <div class="mt-2 text-[10px] text-gray-500 italic px-1">
                        "{{ $transaction->notes }}"
                    </div>
                @endif
            </div>
        @empty
            <div class="py-12 text-center text-gray-400">
                Belum ada riwayat transaksi.
            </div>
        @endforelse
    </div>
</div>

@if($transactions->hasPages())
    <div class="mt-4 px-1">
        {{ $transactions->links('pagination.custom') }}
    </div>
@endif
