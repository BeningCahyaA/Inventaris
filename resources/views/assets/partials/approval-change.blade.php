@php
    $fieldLabels = ['name' => 'Nama', 'status' => 'Status', 'category' => 'Kategori', 'condition' => 'Kondisi', 'price' => 'Harga aset', 'borrower_name' => 'Nama peminjam', 'due_at' => 'Jatuh tempo', 'borrow_notes' => 'Catatan peminjaman', 'purchase_date' => 'Tanggal pembelian', 'person_in_charge' => 'Penanggung jawab', 'location' => 'Lokasi', 'description' => 'Deskripsi', 'depreciation_rate' => 'Penyusutan per tahun (%)', 'warranty_expiry_date' => 'Akhir garansi', 'vendor' => 'Vendor', 'serial_number' => 'Nomor seri', 'slug' => 'Kode kategori', 'prefix' => 'Awalan tag'];
    $statusLabels = ['available' => 'Tersedia', 'in_use' => 'Sedang digunakan', 'borrowed' => 'Dipinjam', 'maintenance' => 'Dalam perawatan', 'broken' => 'Rusak', 'sold' => 'Dijual', 'baik' => 'Baik', 'rusak' => 'Rusak', 'rusak_total' => 'Rusak total'];
    $formatValue = function ($value) use ($field, $statusLabels) {
        if ($value === null || $value === '') return '—';
        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
        if (in_array($field, ['status', 'condition'], true)) return $statusLabels[$value] ?? $value;
        if ($field === 'price' && is_numeric($value)) return 'Rp '.number_format((float) $value, 0, ',', '.');
        if ($field === 'depreciation_rate' && is_numeric($value)) return number_format((float) $value, 2, ',', '.').'%';
        return $value;
    };
@endphp
<div class="approval-change">
    <p class="approval-change-label">{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</p>
    <dl class="approval-change-values">
        <div><dt>Sebelumnya</dt><dd>{{ $formatValue($values['old'] ?? null) }}</dd></div>
        <div><dt>Diajukan</dt><dd>{{ $formatValue($values['new'] ?? null) }}</dd></div>
    </dl>
</div>
