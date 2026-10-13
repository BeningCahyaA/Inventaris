<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Tests\TestCase;

class ReportPdfLayoutTest extends TestCase
{
    public function test_product_labels_render_four_columns_with_vector_barcodes_and_full_product_names(): void
    {
        $generator = new BarcodeGeneratorSVG();
        $assets = collect(range(1, 13))->map(function ($number) use ($generator) {
            $asset = new Asset([
                'asset_tag' => sprintf('IT-26-%03d', $number),
                'name' => 'Laptop Lenovo ThinkPad untuk ruang administrasi & operasional '.$number,
            ]);
            $asset->barcode = base64_encode($generator->getBarcode(
                $asset->asset_tag, $generator::TYPE_CODE_128, 2, 60
            ));

            return $asset;
        });

        $html = view('assets.pdf_label', compact('assets'))->render();
        $this->assertSame(13, substr_count($html, 'data:image/svg+xml;base64,'));
        $this->assertSame(16, substr_count($html, '<td class="label-cell">'));
        foreach ($assets as $asset) {
            $this->assertStringContainsString(e($asset->name), $html);
            $this->assertStringContainsString($asset->asset_tag, $html);
        }

        $pdf = Pdf::loadView('assets.pdf_label', compact('assets'))->setPaper('a4', 'portrait');
        $pdf->render();

        $this->assertStringStartsWith('%PDF-', $pdf->output());
        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_report_does_not_expand_each_asset_row_to_a_full_page(): void
    {
        $this->actingAs(User::factory()->make(['role' => 'admin']));

        $assets = Asset::factory()->count(12)->make([
            'name' => 'Dispenser Miyako Hot & Cool',
            'category' => 'art',
            'location' => 'Pantry',
            'person_in_charge' => null,
            'purchase_date' => null,
            'price' => 750000,
            'condition' => 'baik',
            'status' => 'in_use',
            'description' => 'Dispenser air panas & dingin untuk kebutuhan kantor.',
        ]);

        $pdf = Pdf::loadView('assets.pdf_report', [
            'assets' => $assets,
            'summary' => ['total_assets' => 12, 'total_value' => 9000000],
            'statusBreakdown' => collect(['in_use' => 12]),
            'conditionBreakdown' => collect(['baik' => 12]),
            'categories' => collect(['art' => 'Peralatan rumah tangga']),
            'categoryBreakdown' => collect([
                'art' => ['name' => 'Peralatan rumah tangga', 'count' => 12, 'value' => 9000000],
            ]),
            'groupedAssets' => collect(['art' => $assets]),
        ])->setPaper('a4', 'landscape');

        $pdf->render();

        // The fixed footer must not push every table row onto its own page.
        $this->assertLessThanOrEqual(2, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }
}
