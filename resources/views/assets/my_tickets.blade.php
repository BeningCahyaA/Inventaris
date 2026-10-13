<x-app-layout>
    <x-slot name="title">Tiket Saya</x-slot>
    <x-slot name="header"><h2 class="font-semibold text-lg sm:text-xl text-gray-800">Tiket Saya</h2></x-slot>
    @php
        $statusLabels = ['all' => 'Semua tiket', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
        $fieldLabels = ['name' => 'Nama', 'category' => 'Kategori', 'status' => 'Status aset', 'condition' => 'Kondisi fisik', 'description' => 'Deskripsi', 'purchase_date' => 'Tanggal pembelian', 'warranty_expiry_date' => 'Akhir masa garansi', 'price' => 'Harga pembelian', 'person_in_charge' => 'Penanggung jawab', 'location' => 'Lokasi', 'vendor' => 'Vendor', 'serial_number' => 'Nomor seri', 'borrower_name' => 'Peminjam', 'due_at' => 'Jatuh tempo', 'borrow_notes' => 'Catatan peminjaman', 'notes' => 'Catatan', 'slug' => 'Kode kategori', 'prefix' => 'Awalan kode tag', 'delete_attachments' => 'ID lampiran yang dihapus'];
        $fieldLabels['depreciation_rate'] = 'Penyusutan per tahun (%)';
        $fieldLabels['sale_data'] = 'Penjualan aset';
        $valueLabels = ['available' => 'Tersedia', 'in_use' => 'Sedang digunakan', 'maintenance' => 'Dalam perawatan', 'broken' => 'Rusak', 'sold' => 'Dijual', 'baik' => 'Baik', 'rusak' => 'Rusak', 'rusak_total' => 'Rusak total'];
        $formatTicketValue = function ($field, $value) use ($valueLabels) {
            if ($value === null || $value === '') return 'Belum diisi';
            if (is_array($value)) return implode(', ', array_map(fn($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string) $v, $value)) ?: 'Tidak ada';
            if ($field === 'price' && is_numeric($value)) return 'Rp ' . number_format((float) $value, 0, ',', '.');
            if ($field === 'depreciation_rate' && is_numeric($value)) return number_format((float) $value, 2, ',', '.') . '%';
            return in_array($field, ['status', 'condition'], true) ? ($valueLabels[$value] ?? $value) : $value;
        };
    @endphp
    <div class="max-w-7xl mx-auto ticket-page">
        <div class="ticket-intro"><h1>Tiket saya</h1><p>Pantau permintaan perubahan dan penghapusan aset atau kategori. Buka rincian untuk melihat apa yang diajukan.</p></div>
        <nav class="ticket-filters" aria-label="Filter status tiket">
            @foreach($statusLabels as $key => $label)
            <a href="{{ route('approvals.my-tickets', ['status' => $key]) }}" class="ticket-filter {{ $status === $key ? 'ticket-filter-active' : '' }}" @if($status === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="ticket-list-heading"><h2>{{ $statusLabels[$status] ?? 'Daftar tiket' }}</h2><p>{{ $tickets->total() }} tiket · {{ $tickets->total() ? $tickets->firstItem() : 0 }}–{{ $tickets->lastItem() ?? 0 }} ditampilkan</p></div>
        <div class="ticket-list">
            @forelse($tickets as $ticket)
            @php
                $entity = $ticket->log_type === 'category' ? $ticket->category : $ticket->asset;
                $entityName = $entity->name ?? $ticket->old_data['name'] ?? $ticket->new_data['name'] ?? ($ticket->log_type === 'category' ? 'Kategori sudah dihapus' : 'Aset sudah dihapus');
                $actionLabel = match($ticket->action) { 'create' => 'Penambahan', 'update' => 'Perubahan', 'delete' => 'Penghapusan', 'check_out' => 'Peminjaman', 'check_in' => 'Pengembalian', 'maintenance' => 'Perawatan', default => ucfirst($ticket->action) };
            @endphp
            <article class="ticket-card">
                <div class="ticket-card-top">
                    <div class="ticket-identity"><p class="ticket-meta">{{ $ticket->log_type === 'category' ? 'Kategori' : 'Aset' }} · {{ $actionLabel }} · Tiket #{{ $ticket->log_type === 'category' ? 'K' : 'A' }}{{ $ticket->id }}</p><h3>{{ $entityName }}</h3><p class="ticket-meta">{{ $ticket->log_type === 'category' ? ($entity->slug ?? $ticket->old_data['slug'] ?? '–') : ($entity->asset_tag ?? $ticket->old_data['asset_tag'] ?? '–') }}</p></div>
                    <span class="ticket-badge ticket-badge-{{ $ticket->status }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                </div>
                <div class="ticket-timeline"><span>Diajukan {{ $ticket->created_at->timezone('Asia/Jakarta')->format('d/m/Y, H:i') }} WIB</span>
                    @if($ticket->approver)
                    <span>Diproses oleh {{ $ticket->approver->name }}
                        @if($ticket->approved_at)
                        · {{ $ticket->approved_at->timezone('Asia/Jakarta')->format('d/m/Y, H:i') }} WIB
                        @endif
                    </span>
                    @endif
                </div>
                @if($ticket->status === 'pending')<p class="ticket-pending-note">Menunggu pemeriksaan dan keputusan Super Admin.</p>@endif
                @if($ticket->status === 'rejected' && $ticket->rejection_note)
                <div class="ticket-rejection"><strong>Catatan penolakan</strong><p>{{ $ticket->rejection_note }}</p></div>
                @endif
                @if($ticket->action === 'update' && !empty($ticket->changed_fields))
                <details class="ticket-details"><summary>Lihat perubahan yang diajukan <span>{{ count($ticket->changed_fields) }} kolom</span></summary>
                    <div class="ticket-diff-header" aria-hidden="true"><span>Kolom</span><span>Sebelum</span><span>Yang diajukan</span></div>
                    <dl class="ticket-diff-list">
                        @foreach($ticket->changed_fields as $field => $values)
                            @if($field === 'sale_data')
                                <div class="p-3 text-sm"><p class="font-semibold">Penjualan aset · {{ $values['new']['sold_on'] ?? '-' }}</p><p>Rp {{ number_format($values['new']['amount'] ?? 0, 2, ',', '.') }}</p><a href="{{ route('assets.sale-request-proof', $ticket->id) }}" target="_blank" rel="noopener" class="text-purple-700 underline">Lihat bukti penjualan</a></div>
                                @continue
                            @endif
                        <div class="ticket-diff-row"><dt>{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</dt><dd><span class="ticket-value-label">Sebelum</span>{{ $formatTicketValue($field, $values['old']) }}</dd><dd class="ticket-new-value"><span class="ticket-value-label">Yang diajukan</span>{{ $formatTicketValue($field, $values['new']) }}</dd></div>
                        @endforeach
                    </dl>
                </details>
                @elseif($ticket->action === 'delete')<p class="ticket-delete-note">Permintaan untuk menghapus {{ $ticket->log_type === 'category' ? 'kategori' : 'aset' }} ini dari daftar aktif.</p>
                @endif
            </article>
            @empty
            <div class="ticket-empty"><h3>{{ $status === 'all' ? 'Belum ada tiket' : 'Tidak ada tiket dengan status ini' }}</h3><p>{{ $status === 'all' ? 'Permintaan perubahan atau penghapusan yang Anda ajukan akan ditampilkan di sini.' : 'Pilih Semua tiket untuk melihat permintaan dengan status lainnya.' }}</p>@if($status !== 'all')<a href="{{ route('approvals.my-tickets', ['status' => 'all']) }}" class="asset-back-link">Lihat semua tiket →</a>@endif</div>
            @endforelse
        </div>
        @if($tickets->hasPages())<div class="mt-5">{{ $tickets->links('pagination.custom') }}</div>@endif
    </div>
</x-app-layout>
