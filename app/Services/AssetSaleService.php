<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetSaleService
{
    public function prepare(Request $request): array
    {
        $proof = $request->file('sale_proof');
        $path = $proof->store('sale-proofs', 'local');
        abort_unless($path !== false, 500, 'Bukti penjualan gagal disimpan.');

        return [
            'sold_on' => $request->input('sale_date'), 'amount' => $request->input('sale_price'),
            'buyer_name' => $request->input('sale_buyer'), 'notes' => $request->input('sale_notes'),
            'proof_path' => $path, 'proof_name' => $proof->getClientOriginalName(),
            'recorded_by' => $request->user()->id,
        ];
    }

    public function record(Asset $asset, array $sale): void
    {
        abort_unless(!empty($sale['proof_path']) && !empty($sale['sold_on']), 422, 'Bukti dan tanggal penjualan wajib diisi.');
        $latest = $asset->depreciations()->max('depreciated_on');
        abort_if($latest && $latest > $sale['sold_on'], 422, 'Tanggal penjualan mendahului riwayat penyusutan terakhir.');
        abort_unless(Storage::disk('local')->exists($sale['proof_path']), 422, 'Bukti penjualan tidak ditemukan.');
        app(AssetDepreciationService::class)->sync($asset, Carbon::parse($sale['sold_on']));
        $asset->sale()->create($sale);
    }
}
