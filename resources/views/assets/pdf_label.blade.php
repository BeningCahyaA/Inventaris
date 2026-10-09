<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Print Labels</title>
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
        .layout-table td {
            width: 50%;
            padding: 5px;
            vertical-align: top;
        }

        .label-box {
            border: 2px solid #000;
            padding: 8px;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
        }

        .barcode-img {
            width: 100%;
            height: 60px;
            display: block;
            margin: 0 auto 8px auto;
            /* Memberi jarak antara barcode dan teks di bawahnya */
        }

        /* WRAPPER UNTUK TEKS DI BAWAH BARCODE */
        .text-wrapper {
            padding: 0 4px;
        }

        /* --- SEMUA GAYA TULISAN DI BAWAH INI SAMA PERSIS DENGAN KODE ASLI --- */
        .top-row {
            width: 100%;
            height: 14px;
            margin-bottom: 2px;
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
            width: 14px;
            height: auto;
        }

        .property-of {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            line-height: 1;
            margin-top: 1px;
            letter-spacing: -0.2px;
        }

        .company-name {
            font-size: 26px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            line-height: 0.85;
            margin-top: 0;
            margin-bottom: 4px;
            letter-spacing: -1.5px;
        }

        .id-tag-container {
            text-align: center;
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

    <table class="layout-table">
        <tr>
            @foreach($assets as $key => $asset)
            <!-- Pembagian kolom diubah dari 3 menjadi 2 agar proporsi kotak lebih panjang -->
            @if($key > 0 && $key % 2 == 0)
        </tr>
        <tr>
            @endif

            <td>
                <div class="label-box">
                    <!-- BARCODE DI BAGIAN ATAS -->
                    <img class="barcode-img" src="data:image/png;base64,{{ $asset->barcode }}" alt="Barcode {{ $asset->asset_tag }}">

                    <!-- TEKS DI BAGIAN BAWAH -->
                    <div class="text-wrapper">
                        <div class="top-row">
                            <div class="warning-text">
                                UNAUTHORIZED USE IS<br>PROHIBITED.
                            </div>
                            <div class="logo-container">
                                <img class="logo-img" src="{{ public_path('img/Favicon-Vodeco.png') }}" onerror="this.style.display='none'">
                            </div>
                        </div>

                        <div class="property-of">PROPERTY OF</div>

                        <div class="company-name">VODECO</div>

                        <div class="id-tag-container">
                            <div class="id-tag">{{ $asset->asset_tag }}</div>
                        </div>
                    </div>
                </div>
            </td>
            @endforeach

            @php
            /* Mengatur sisa kolom kosong jika jumlah data ganjil */
            $sisa = 2 - (count($assets) % 2);
            if($sisa < 2 && $sisa !=0) {
                for($i=0; $i < $sisa; $i++) echo "<td></td>" ;
                }
                @endphp
                </tr>
    </table>

</body>

</html>