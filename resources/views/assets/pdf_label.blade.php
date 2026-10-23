<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Label Barcode Produk</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm;
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

        /* Empat label berjajar pada lebar kertas A4. */
        .label-cell {
            width: 25%;
            padding: 0.75mm;
            vertical-align: top;
        }

        .layout-table tr {
            page-break-inside: avoid;
        }

        .label-box {
            position: relative;
            height: 30.6mm;
            border: 0.2mm solid #000;
            padding: 1.5mm;
            background: #fff;
            border-radius: 1.5mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .product-name {
            font-family: 'DejaVu Sans', sans-serif;
            height: 8mm;
            font-size: 6.5pt;
            font-weight: bold;
            line-height: 1.15;
            text-align: center;
            overflow-wrap: break-word;
            margin: 0 0 0.8mm;
        }

        /* Ruang putih di kedua sisi barcode untuk membantu pembacaan scanner. */
        .barcode-wrapper {
            padding: 0.6mm 2.5mm;
            background: #fff;
        }

        .barcode-img {
            width: 100%;
            height: 9mm;
            display: block;
            margin: 0 auto;
            /* Memberi jarak antara barcode dan teks di bawahnya */
        }

        /* WRAPPER UNTUK TEKS DI BAWAH BARCODE */
        .text-wrapper {
            position: absolute;
            left: 1.5mm;
            right: 1.5mm;
            bottom: 1.5mm;
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

        .property-of {
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            line-height: 1;
            margin-top: 1px;
            letter-spacing: -0.2px;
        }

        .company-wordmark {
            width: 18mm;
        }

        .company-wordmark img {
            display: block;
            width: 18mm;
            height: auto;
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
            font-size: 8px;
            display: inline-block;
            padding: 3px 4px;
            border-radius: 4px;
            line-height: 1;
        }
    </style>
</head>

<body>
    @php
        $wordmarkData = base64_encode(file_get_contents(public_path('img/Logo-Vodeco-Media-Group-300x87.png')));
    @endphp

    <table class="layout-table">
        @foreach($assets->values()->chunk(4) as $rowAssets)
        <tr>
            @foreach($rowAssets as $asset)

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
                        </div>

                    </div>
                </div>
            </td>
            @endforeach

            @for($emptyColumn = $rowAssets->count(); $emptyColumn < 4; $emptyColumn++)
                <td class="label-cell"></td>
            @endfor
        </tr>
        @endforeach
    </table>

</body>

</html>
