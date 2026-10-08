<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class ReportPdfLayoutTest extends TestCase
{
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
