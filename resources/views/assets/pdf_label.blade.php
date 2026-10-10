<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Label Barcode Produk</title>
    <style>
        @page {
            margin: 15px;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* Diubah menjadi 50% agar menjadi 2 kolom dan kotak lebih panjang ke samping */
        .label-cell {
            width: 50%;
            padding: 4px;
            vertical-align: top;
        }

        .layout-table tr {
            page-break-inside: avoid;
        }

        .label-box {
            border: 1px solid #000;
            padding: 6px;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .product-name {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
            text-align: center;
            overflow-wrap: break-word;
            margin: 0 0 4px;
        }

        /* Ruang putih di kedua sisi barcode untuk membantu pembacaan scanner. */
        .barcode-wrapper {
            padding: 3px 20px;
            background: #fff;
        }

        .barcode-img {
            width: 100%;
            height: 48px;
            display: block;
            margin: 0 auto;
            /* Memberi jarak antara barcode dan teks di bawahnya */
        }

        /* WRAPPER UNTUK TEKS DI BAWAH BARCODE */
        .text-wrapper {
            padding: 3px 2px 0;
        }

        .identity-row {
            display: table;
            width: 100%;
        }

        .brand-container {
            display: table-cell;
            vertical-align: middle;
        }

        /* --- SEMUA GAYA TULISAN DI BAWAH INI SAMA PERSIS DENGAN KODE ASLI --- */
        .top-row {
            width: 100%;
            margin-top: 2px;
            display: table;
        }

        .warning-text {
            display: table-cell;
            font-size: 5px;
            font-weight: bold;
            color: #000;
            line-height: 1;
            vertical-align: middle;
            width: 80%;
        }

        .logo-container {
            display: table-cell;
            vertical-align: top;
            text-align: right;
        }

        .logo-img {
            width: 10px;
            height: auto;
        }

        .property-of {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            line-height: 1;
            margin-top: 1px;
            letter-spacing: -0.2px;
        }

        /* Tampilkan hanya tulisan vodeco dari master logo, tanpa ikon dan slogan. */
        .company-wordmark {
            position: relative;
            width: 100px;
            height: 22px;
            overflow: hidden;
        }

        .company-wordmark img {
            position: absolute;
            width: 145.74px;
            height: auto;
            left: -44.44px;
            top: -1.85px;
        }

        .id-tag-container {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .id-tag {
            background: #000;
            color: #fff;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 11px;
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            line-height: 1;
        }
    </style>
</head>

<body>
    @php
        $logoPath = public_path('img/Favicon-Vodeco.png');
        $logoData = is_file($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        $wordmarkData = base64_encode(file_get_contents(public_path('img/Master-Logo-Vodeco.png')));
    @endphp

    <table class="layout-table">
        <tr>
            @foreach($assets as $key => $asset)
            <!-- Pembagian kolom diubah dari 3 menjadi 2 agar proporsi kotak lebih panjang -->
            @if($key > 0 && $key % 2 == 0)
        </tr>
        <tr>
            @endif

            <td class="label-cell">
                <div class="label-box">
                    <div class="product-name">{{ $asset->name }}</div>
                    <!-- BARCODE DI BAGIAN ATAS -->
                    <div class="barcode-wrapper">
                        <img class="barcode-img" src="data:image/svg+xml;base64,{{ $asset->barcode }}" alt="Barcode {{ $asset->asset_tag }}">
                    </div>

                    <!-- TEKS DI BAGIAN BAWAH -->
                    <div class="text-wrapper">
                        <div class="identity-row">
                            <div class="brand-container">
                                <div class="property-of">PROPERTY OF</div>
                                <div class="company-wordmark">
                                    <img src="data:image/png;base64,{{ $wordmarkData }}" alt="vodeco">
                                </div>
                            </div>
                            <div class="id-tag-container">
                                <div class="id-tag">{{ $asset->asset_tag }}</div>
                            </div>
                        </div>
                        <div class="top-row">
                            <div class="warning-text">
                                UNAUTHORIZED USE IS PROHIBITED.
                            </div>
                            <div class="logo-container">
                                @if($logoData)
                                    <img class="logo-img" src="data:image/png;base64,{{ $logoData }}" alt="Vodeco">
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </td>
            @endforeach

            @php
            /* Mengatur sisa kolom kosong jika jumlah data ganjil */
            $sisa = 2 - (count($assets) % 2);
            if($sisa < 2 && $sisa !=0) {
                for($i=0; $i < $sisa; $i++) echo '<td class="label-cell"></td>' ;
                }
                @endphp
                </tr>
    </table>

</body>

</html>
