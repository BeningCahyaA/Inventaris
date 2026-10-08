<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportController extends Controller
{
    /**
     * Export laporan rekapitulasi aset ke PDF.
     */
    public function exportReport(): HttpResponse
    {
        $assets = Asset::orderBy('category')->orderBy('name')->get();

        // Ringkasan umum
        $summary = [
            'total_assets' => $assets->count(),
            'total_value' => $assets->sum('price'),
        ];

        // Breakdown per status
        $statusBreakdown = $assets->groupBy('status')->map->count();

        // Breakdown per kondisi (normalize ke lowercase supaya match view keys)
        $conditionBreakdown = $assets->groupBy(fn ($a) => strtolower(str_replace(' ', '_', $a->condition ?? '')))->map->count();

        // Group by kategori (nama dari DB)
        $categories = Category::pluck('name', 'slug');
        $groupedAssets = $assets->groupBy('category');

        // Ringkasan per kategori (jumlah + total nilai)
        $categoryBreakdown = $groupedAssets->map(function ($items, $slug) use ($categories) {
            return [
                'name' => $categories[$slug] ?? ucfirst($slug),
                'count' => $items->count(),
                'value' => $items->sum('price'),
            ];
        });

        $pdf = Pdf::loadView('assets.pdf_report', compact(
            'assets',
            'summary',
            'statusBreakdown',
            'conditionBreakdown',
            'groupedAssets',
            'categoryBreakdown',
            'categories',
        ));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Rekapitulasi-Aset-Vodeco.pdf');
    }

    /**
     * Preview & cetak label QR Code.
     */
    public function printPreview(Request $request): View|string
    {
        $query = Asset::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('asset_tag', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $assets = $query->latest()->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('assets.partials.print-table', compact('assets'))->render();
        }

        return view('assets.print_preview', compact('assets'));
    }

    /**
     * Download PDF label QR Code (selektif atau semua).
     */
    public function downloadPdf(Request $request): HttpResponse
    {
        if ($request->has('selected_assets')) {
            $assets = Asset::whereIn('id', $request->selected_assets)
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $assets = Asset::orderBy('id', 'asc')->get();
        }

        foreach ($assets as $asset) {
            $asset->qr_code = base64_encode((string) QrCode::format('png')->size(100)->generate($asset->asset_tag));
        }

        $pdf = Pdf::loadView('assets.pdf_label', compact('assets'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('labels-vodeco-selected.pdf');
    }

    /**
     * Export data aset ke CSV (mendukung filter aktif).
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Asset::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $assets = $query->orderBy('category')->orderBy('name')->get();

        // Map slug → nama kategori
        $categoryNames = Category::pluck('name', 'slug');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="data-aset-vodeco-' . date('Y-m-d') . '.csv"',
        ];

        $columns = [
            'No',
            'Asset Tag',
            'Nama Aset',
            'Kategori',
            'Status',
            'Kondisi',
            'PIC',
            'Lokasi',
            'Vendor',
            'Serial Number',
            'Harga',
            'Tanggal Pembelian',
            'Masa Garansi',
            'Deskripsi',
        ];

        return response()->streamDownload(function () use ($assets, $columns, $categoryNames) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 agar Excel mengenali encoding
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns, ';');

            foreach ($assets as $index => $asset) {
                $kondisi = match (strtolower(str_replace(' ', '_', $asset->condition ?? ''))) {
                    'baik' => 'Baik',
                    'rusak' => 'Rusak',
                    'rusak_total' => 'Rusak Total',
                    default => ucfirst($asset->condition ?? '-'),
                };

                fputcsv($file, [
                    $index + 1,
                    $asset->asset_tag,
                    $asset->name,
                    $categoryNames[$asset->category] ?? ucfirst($asset->category),
                    str_replace('_', ' ', ucfirst($asset->status)),
                    $kondisi,
                    $asset->person_in_charge ?? '-',
                    $asset->location ?? '-',
                    $asset->vendor ?? '-',
                    $asset->serial_number ?? '-',
                    $asset->price ?? 0,
                    $asset->purchase_date?->format('d/m/Y') ?? '-',
                    $asset->warranty_expiry_date?->format('d/m/Y') ?? '-',
                    $asset->description ?? '-',
                ], ';');
            }

            fclose($file);
        }, 'data-aset-vodeco-' . date('Y-m-d') . '.csv', $headers);
    }
}
