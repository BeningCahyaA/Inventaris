<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Aset</title>
    <style>
        @page {
            margin: 15mm 12mm 20mm 12mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            line-height: 1.4;
        }

        /* --- Kop Surat --- */
        .kop {
            text-align: center;
            border-bottom: 3px double #003366;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .kop h1 {
            margin: 0;
            font-size: 16px;
            color: #003366;
            letter-spacing: 2px;
        }

        .kop .subtitle {
            font-size: 9px;
            color: #555;
            margin: 2px 0;
        }

        /* --- Judul Laporan --- */
        .report-title {
            text-align: center;
            margin: 10px 0 6px 0;
        }

        .report-title h2 {
            margin: 0;
            font-size: 13px;
            color: #003366;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-title .date {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }

        /* --- Ringkasan Box --- */
        .summary-section {
            margin-bottom: 14px;
        }

        .summary-section h3 {
            font-size: 10px;
            color: #003366;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
            margin: 0 0 6px 0;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .summary-box {
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 6px 8px;
            margin: 0 3px 4px 0;
            background: #fafafa;
        }

        .summary-box .label {
            font-size: 8px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-box .value {
            font-size: 13px;
            font-weight: bold;
            color: #003366;
            margin-top: 1px;
        }

        /* --- Mini Table (Breakdown) --- */
        .mini-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-top: 4px;
        }

        .mini-table th,
        .mini-table td {
            border: 1px solid #ddd;
            padding: 3px 6px;
        }

        .mini-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
            color: #333;
        }

        .mini-table td.number {
            text-align: right;
            font-weight: bold;
        }

        /* --- Tabel Utama Aset --- */
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #003366;
            background: #e8eef5;
            padding: 5px 8px;
            margin: 12px 0 0 0;
            border-left: 3px solid #003366;
        }

        .asset-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-size: 8px;
        }

        .asset-table th,
        .asset-table td {
            border: 1px solid #bbb;
            padding: 4px 5px;
            text-align: left;
        }

        .asset-table th {
            background: #003366;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .asset-table tbody tr:nth-child(even) {
            background: #f8f9fb;
        }

        .asset-table .text-center {
            text-align: center;
        }

        .asset-table .text-right {
            text-align: right;
        }

        .asset-table .monospace {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 7.5px;
        }

        /* Status & Kondisi */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
        }

        .badge-available { background: #dcfce7; color: #15803d; }
        .badge-in-use { background: #dbeafe; color: #1e40af; }
        .badge-maintenance { background: #fef3c7; color: #b45309; }
        .badge-broken { background: #fee2e2; color: #991b1b; }
        .badge-baik { background: #d1fae5; color: #065f46; }
        .badge-rusak { background: #fef3c7; color: #92400e; }
        .badge-rusak-total { background: #fee2e2; color: #991b1b; }

        /* Subtotal row */
        .subtotal-row {
            background: #e8eef5 !important;
            font-weight: bold;
        }

        .subtotal-row td {
            border-top: 2px solid #003366;
        }

        /* Grand Total */
        .grand-total-row {
            background: #003366 !important;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }

        .grand-total-row td {
            border: 1px solid #003366;
            padding: 5px;
        }

        /* --- Tanda Tangan --- */
        .signature-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-table .sig-title {
            font-size: 8px;
            color: #555;
            margin-bottom: 50px;
        }

        .signature-table .sig-line {
            border-top: 1px solid #333;
            display: inline-block;
            width: 120px;
            margin-bottom: 2px;
        }

        .signature-table .sig-name {
            font-size: 9px;
            font-weight: bold;
        }

        .signature-table .sig-role {
            font-size: 7px;
            color: #666;
        }

        /* --- Page Number --- */
        .page-number {
            position: fixed;
            bottom: 5mm;
            right: 0;
            width: 100%;
            text-align: center;
            font-size: 7px;
            color: #999;
        }

        .page-number::after {
            content: "Halaman " counter(page);
        }
        /* Tata letak cetak A4 landscape, kompatibel dengan DomPDF. */
        @page { margin: 10mm 10mm 14mm; }
        body { font-size: 9px; color: #334155; line-height: 1.25; }
        .kop { text-align: left; border-bottom: 2px solid #475a9d; padding-bottom: 6px; margin-bottom: 8px; }
        .letterhead { width: 100%; border-collapse: collapse; }
        .letterhead td { vertical-align: middle; }
        .brand-cell { width: 30%; }
        .brand-logo { width: 145px; height: auto; }
        .company-cell { text-align: right; }
        .kop h1 { font-size: 14px; letter-spacing: 1px; color: #263657; margin-bottom: 4px; }
        .kop .subtitle { font-size: 8px; color: #64748b; }
        .report-title { text-align: left; margin: 0 0 7px; }
        .report-title h2 { font-size: 15px; letter-spacing: 0; color: #263657; text-transform: none; }
        .report-title .date { margin: 4px 0 0; color: #64748b; }
        .overview { width: 100%; border-collapse: collapse; margin-bottom: 7px; }
        .overview td { width: 33.33%; padding: 5px 10px; background: #f1f5f9; border: 1px solid #dce3ed; }
        .overview .label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
        .overview .value { font-size: 13px; font-weight: bold; color: #263657; margin-top: 2px; }
        .breakdown-grid { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .breakdown-grid > tbody > tr > td { vertical-align: top; padding: 0 10px 0 0; }
        .breakdown-grid > tbody > tr > td:last-child { padding-right: 0; }
        .breakdown-heading { font-size: 10px; font-weight: bold; color: #263657; margin: 0 0 5px; }
        .mini-table { font-size: 8px; margin: 0; }
        .mini-table th { background: #eef2f7; color: #475569; }
        .mini-table th, .mini-table td { border-color: #dce3ed; padding: 2px 6px; }
        .section-title { margin-top: 8px; padding: 4px 8px; background: #eef2f7; border-left-color: #475a9d; color: #263657; page-break-after: avoid; }
        .asset-table { table-layout: fixed; }
        .asset-table thead { display: table-header-group; }
        .asset-table tr { page-break-inside: avoid; }
        .asset-table th, .asset-table td { border-color: #dce3ed; padding: 2px 5px; vertical-align: middle; line-height: 1.2; overflow-wrap: break-word; word-wrap: break-word; }
        .asset-table th { background: #35466c; font-size: 7.5px; letter-spacing: 0; }
        .asset-table tbody tr:nth-child(even) { background: #f8fafc; }
        .badge { display: inline; font-size: 7px; line-height: 1; padding: 1px 3px; white-space: nowrap; text-transform: none; }
        .subtotal-row { background: #eef2f7 !important; }
        .subtotal-row td { border-top: 1px solid #94a3b8; }
        .grand-total-row { background: #35466c !important; }
        .grand-total-row td { padding: 5px 8px; border-color: #35466c; }
        .signature-section { margin-top: 12px; }
        .signature-table .sig-title { margin-bottom: 28px; }
        .page-number { bottom: -9mm; text-align: right; color: #64748b; border-top: 1px solid #dce3ed; padding-top: 4px; }
        /* Hindari float pada footer fixed: DomPDF dapat membesarkan baris tabel. */
        .footer-brand { position: absolute; left: 0; top: 4px; }
        .empty-state { padding: 20px; text-align: center; border: 1px solid #dce3ed; color: #64748b; }
    </style>
</head>

<body>

    {{-- Nomor Halaman --}}
    <div class="page-number"><span class="footer-brand">Vodeco Digital Mediatama &bull; Laporan aset</span></div>

    {{-- Kop Surat --}}
    <div class="kop">
        <table class="letterhead"><tr>
        <td class="brand-cell"><img class="brand-logo" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/Master-Logo-Vodeco.png'))) }}" alt="Logo Vodeco"></td>
        <td class="company-cell"><h1>VODECO DIGITAL MEDIATAMA</h1>
        <p class="subtitle">Jl. Dagan, Procot, Kec. Slawi, Kab. Tegal, Jawa Tengah</p>
        <p class="subtitle">Telp: +62 878-1099-2666 &bull; Email: hello@vodeco.co.id &bull; Web: vodeco.co.id</p>
        </td></tr></table>
    </div>

    {{-- Judul --}}
    <div class="report-title">
        <h2>Laporan Rekapitulasi Aset</h2>
        <p class="date">Data per {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y') }} &bull; Seluruh kategori aset</p>
    </div>

    @php
        $statusLabels = ['available' => 'Tersedia', 'in_use' => 'Digunakan', 'borrowed' => 'Dipinjam', 'maintenance' => 'Perawatan', 'broken' => 'Rusak'];
        $condLabels = ['baik' => 'Baik', 'rusak' => 'Rusak', 'rusak_total' => 'Rusak total'];
    @endphp
    <table class="overview"><tr>
        <td><div class="label">Total aset</div><div class="value">{{ number_format($summary['total_assets'], 0, ',', '.') }} unit</div></td>
        <td><div class="label">Total nilai aset</div><div class="value">Rp {{ number_format($summary['total_value'], 0, ',', '.') }}</div></td>
        <td><div class="label">Kategori terdata</div><div class="value">{{ $categoryBreakdown->count() }} kategori</div></td>
    </tr></table>
    <table class="breakdown-grid"><tr>
        <td style="width: 25%;">
            <div class="breakdown-heading">Ringkasan status</div>
            <table class="mini-table"><thead><tr><th>Status</th><th>Jumlah</th></tr></thead><tbody>
                @foreach($statusLabels as $key => $label)
                <tr><td>{{ $label }}</td><td class="number">{{ $statusBreakdown[$key] ?? 0 }}</td></tr>
                @endforeach
            </tbody></table>
        </td>
        <td style="width: 25%;">
            <div class="breakdown-heading">Ringkasan kondisi</div>
            <table class="mini-table"><thead><tr><th>Kondisi</th><th>Jumlah</th></tr></thead><tbody>
                @foreach($condLabels as $key => $label)
                <tr><td>{{ $label }}</td><td class="number">{{ $conditionBreakdown[$key] ?? 0 }}</td></tr>
                @endforeach
            </tbody></table>
        </td>
        <td style="width: 50%;">
            <div class="breakdown-heading">Ringkasan kategori</div>
            <table class="mini-table"><thead><tr><th>Kategori</th><th>Jumlah</th><th>Nilai aset (Rp)</th></tr></thead><tbody>
                @forelse($categoryBreakdown as $category)
                <tr><td>{{ $category['name'] }}</td><td class="number">{{ $category['count'] }}</td><td class="number">{{ number_format($category['value'], 0, ',', '.') }}</td></tr>
                @empty
                <tr><td colspan="3">Belum ada kategori aset.</td></tr>
                @endforelse
            </tbody></table>
        </td>
    </tr></table>

    {{-- ===== SECTION 2: DETAIL ASET PER KATEGORI ===== --}}
    @php $globalNo = 1; $grandTotal = 0; @endphp

    @foreach($groupedAssets as $slug => $assets)
        @php
            $catName = $categories[$slug] ?? ucfirst($slug);
            $catTotal = $assets->sum('price');
            $grandTotal += $catTotal;
        @endphp

        <div class="section-title">Detail aset &mdash; {{ $catName }} ({{ $assets->count() }} unit)</div>

        <table class="asset-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 10%;">ID Aset</th>
                    <th style="width: 18%;">Nama Aset</th>
                    <th style="width: 10%;">Lokasi</th>
                    <th style="width: 10%;">PIC</th>
                    <th style="width: 8%;">Tgl Beli</th>
                    <th style="width: 11%;">Nilai (Rp)</th>
                    <th style="width: 7%;">Kondisi</th>
                    <th style="width: 7%;">Status</th>
                    <th style="width: 16%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $asset)
                <tr>
                    <td class="text-center">{{ $globalNo++ }}</td>
                    <td class="monospace">{{ $asset->asset_tag }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->location ?? '-' }}</td>
                    <td>{{ $asset->person_in_charge ?? '-' }}</td>
                    <td class="text-center">{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->locale('id')->isoFormat('D MMM YY') : '-' }}</td>
                    <td class="text-right">{{ number_format($asset->price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @php
                            $condClass = match($asset->condition) {
                                'baik' => 'badge-baik',
                                'rusak' => 'badge-rusak',
                                'rusak_total' => 'badge-rusak-total',
                                default => '',
                            };
                            $condLabel = match($asset->condition) {
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                                'rusak_total' => 'Rusak Total',
                                default => ucfirst($asset->condition ?? '-'),
                            };
                        @endphp
                        <span class="badge {{ $condClass }}">{{ $condLabel }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = match($asset->status) {
                                'available' => 'badge-available',
                                'in_use', 'borrowed' => 'badge-in-use',
                                'maintenance' => 'badge-maintenance',
                                'broken' => 'badge-broken',
                                default => '',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $statusLabels[$asset->status] ?? ucfirst(str_replace('_', ' ', $asset->status)) }}</span>
                    </td>
                    <td style="font-size: 7px;">{{ $asset->description ?? '-' }}</td>
                </tr>
                @endforeach
                {{-- Subtotal per Kategori --}}
                <tr class="subtotal-row">
                    <td colspan="6" style="text-align: right;">Subtotal {{ $catName }}:</td>
                    <td class="text-right">Rp {{ number_format($catTotal, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
    @endforeach

    @if($groupedAssets->isEmpty())
        <div class="empty-state">Belum ada data aset untuk ditampilkan dalam laporan.</div>
    @endif

    {{-- Grand Total --}}
    <table class="asset-table" style="margin-top: 6px;">
        <tr class="grand-total-row">
            <td style="width: 75%;">TOTAL NILAI SELURUH ASET</td>
            <td style="text-align: right; width: 25%;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- ===== SECTION 3: TANDA TANGAN ===== --}}
    <div class="signature-section">
        <p style="font-size: 8px; color: #666; margin-bottom: 5px;">Tegal, {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y') }}</p>
        <table class="signature-table">
            <tr>
                <td>
                    <div class="sig-title">Dibuat oleh,</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ Auth::user()->name }}</div>
                    <div class="sig-role">{{ Auth::user()->role === 'super_admin' ? 'Super Admin' : 'Admin' }}</div>
                </td>
                <td>
                    <div class="sig-title">Diperiksa oleh,</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">(...............................)</div>
                    <div class="sig-role">HR Vodeco</div>
                </td>
                <td>
                    <div class="sig-title">Disetujui oleh,</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">(...............................)</div>
                    <div class="sig-role">Owner Vodeco</div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
