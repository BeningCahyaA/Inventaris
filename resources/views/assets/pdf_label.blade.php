<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Print Labels</title>
    <style>
        @page { margin: 15px; }
        body { margin: 0; padding: 0; font-family: 'Helvetica', 'Arial', sans-serif; }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .layout-table td {
            width: 33.33%;
            padding: 3px;
            vertical-align: top;
        }

        .label-box {
            border: 2px solid #000;
            height: 96px;
            padding: 4px;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
        }

        .inner-table { width: 100%; border-collapse: collapse; height: 100%; }

        /* KOLOM KIRI: KHUSUS QR CODE BESAR */
        .td-qr {
            width: 34%; /* Lebar pas untuk QR */
            vertical-align: middle;
            text-align: center;
            border-right: 2px solid #000;
            padding-right: 2px;
            line-height: 0;
        }

        /* KOLOM KANAN: TEXT PENUH */
        .td-text {
            width: 66%;
            vertical-align: top;
            padding-left: 6px;
            position: relative;
        }

        /* QR CODE: Dibuat memenuhi ruang kolom kiri */
        .qr-img {
            width: 86px; 
            height: 86px;
            display: block;
            margin: 0 auto;
        }

        /* BARIS ATAS: Warning & Logo */
        .top-row { width: 100%; height: 14px; margin-bottom: 2px; display: table; }
        
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
        .logo-img { width: 14px; height: auto; }

        /* TEXT VODECO */
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
                @if($key > 0 && $key % 3 == 0)
                    </tr><tr>
                @endif

                <td>
                    <div class="label-box">
                        <table class="inner-table">
                            <tr>
                                <td class="td-qr">
                                    <img class="qr-img" src="data:image/png;base64, {{ base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::class::format('png')->size(120)->margin(0)->generate($asset->asset_tag)) }}">
                                </td>

                                <td class="td-text">
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
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            @endforeach

            @php 
                $sisa = 3 - (count($assets) % 3);
                if($sisa < 3 && $sisa != 0) {
                    for($i=0; $i < $sisa; $i++) echo "<td></td>"; 
                }
            @endphp
        </tr>
    </table>

</body>
</html>