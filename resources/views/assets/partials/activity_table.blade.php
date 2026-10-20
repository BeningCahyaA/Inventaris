<div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
    <form method="GET" action="{{ route('assets.history') }}" class="flex flex-col sm:flex-row gap-3" role="search">
        <input type="hidden" name="tab" value="activity">
        <x-management-search label="Cari log aktivitas" placeholder="Cari pelaku, nama aset, kategori, atau tag..." />
        <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700">Cari</button>
        @if(request()->filled('search'))<a href="{{ route('assets.history', ['tab' => 'activity']) }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Reset</a>@endif
    </form>
</div>
<div class="management-panel bg-white overflow-hidden">
    <div class="management-panel-heading"><h3>Log aktivitas</h3><span>{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} aktivitas</span></div>
    <div class="divide-y divide-gray-100">
        @forelse($logs as $log)
            @php
                $action = match($log->action) { 'create' => 'Tambah', 'update' => 'Ubah', 'delete' => 'Hapus', 'check_in' => 'Pengembalian', 'check_out' => 'Peminjaman', default => ucfirst(str_replace('_', ' ', $log->action)) };
                $status = match($log->status) { 'approved' => 'Disetujui', 'pending' => 'Menunggu persetujuan', 'rejected' => 'Ditolak', default => $log->status };
                $statusClass = match($log->status) { 'approved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-700' };
            @endphp
            <article class="p-5 flex flex-col sm:flex-row sm:items-start gap-3 sm:gap-5">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2"><span class="text-xs font-semibold text-purple-700 bg-purple-50 px-2 py-1 rounded">{{ $log->type }} · {{ $action }}</span><span class="management-badge {{ $statusClass }}">{{ $status }}</span></div>
                    <h4 class="text-sm font-semibold text-gray-900 break-words">{{ $log->reference ?? $log->type.' dihapus' }}</h4>
                    @if($log->tag)<p class="text-xs font-mono text-gray-500 mt-1 break-all">{{ $log->tag }}</p>@endif
                    <p class="text-sm text-gray-600 mt-2">Oleh <span class="font-medium">{{ $log->actor ?? 'Sistem' }}</span></p>
                </div>
                <time datetime="{{ \Carbon\Carbon::parse($log->created_at)->toIso8601String() }}" class="text-xs text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</time>
            </article>
        @empty
            <div class="management-empty"><strong>Belum ada log aktivitas yang sesuai.</strong><p>{{ request()->filled('search') ? 'Coba kata kunci lain atau reset pencarian.' : 'Aktivitas aset dan kategori akan ditampilkan di sini.' }}</p></div>
        @endforelse
    </div>
</div>
@if($logs->hasPages())<div class="mt-4 px-1">{{ $logs->links('pagination.custom') }}</div>@endif
