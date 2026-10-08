<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Category;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $assets = [
            // Elektronik (ELK)
            ['category' => 'elektronik', 'name' => 'TV LED Samsung 55" Smart TV', 'person_in_charge' => null, 'status' => 'in_use', 'condition' => 'baik', 'price' => 8500000, 'purchase_date' => '2025-08-15', 'location' => 'Ruang Meeting Utama', 'vendor' => 'Samsung Store', 'serial_number' => 'SMG-TV55-2025-001', 'description' => 'Smart TV 4K untuk presentasi dan video conference'],
            ['category' => 'elektronik', 'name' => 'Speaker Portable JBL Charge 5', 'person_in_charge' => 'Sari', 'status' => 'available', 'condition' => 'baik', 'price' => 2200000, 'purchase_date' => '2026-01-20', 'location' => 'Gudang', 'vendor' => 'JBL Official', 'serial_number' => 'JBL-CH5-2026-008', 'description' => 'Speaker bluetooth waterproof untuk acara kantor'],

            // Komputer & Laptop (KL)
            ['category' => 'komputer-laptop', 'name' => 'Laptop Lenovo ThinkPad X1 Carbon', 'person_in_charge' => 'Indra', 'status' => 'in_use', 'condition' => 'baik', 'price' => 22000000, 'purchase_date' => '2025-11-10', 'location' => 'Ruang Dev', 'vendor' => 'Lenovo Indonesia', 'serial_number' => 'LNV-X1C-2025-044', 'description' => 'Laptop premium Core i7 Gen 13, RAM 16GB, SSD 512GB'],
            ['category' => 'komputer-laptop', 'name' => 'PC Desktop HP ProDesk 400 G9', 'person_in_charge' => 'Alam', 'status' => 'in_use', 'condition' => 'baik', 'price' => 12500000, 'purchase_date' => '2025-09-05', 'location' => 'Ruang Admin', 'vendor' => 'HP Indonesia', 'serial_number' => 'HP-PD400-2025-019', 'description' => 'PC desktop untuk administrasi, i5 Gen 12'],

            // Jaringan & Telekomunikasi (JT)
            ['category' => 'jaringan-telkom', 'name' => 'Router MikroTik RB750Gr3', 'person_in_charge' => null, 'status' => 'in_use', 'condition' => 'baik', 'price' => 1200000, 'purchase_date' => '2025-04-12', 'location' => 'Ruang Server', 'vendor' => 'MikroTik Official', 'serial_number' => 'MTK-750-2025-003', 'description' => 'Router utama kantor, Gigabit 5 port'],
            ['category' => 'jaringan-telkom', 'name' => 'Switch TP-Link 24 Port Gigabit', 'person_in_charge' => null, 'status' => 'in_use', 'condition' => 'baik', 'price' => 2800000, 'purchase_date' => '2025-04-12', 'location' => 'Ruang Server', 'vendor' => 'TP-Link', 'serial_number' => 'TPL-SW24-2025-007', 'description' => 'Managed switch untuk distribusi jaringan LAN'],

            // Perabotan & Furnitur (PRB)
            ['category' => 'perabotan', 'name' => 'Meja Kerja Standing Desk 140cm', 'person_in_charge' => 'Budi', 'status' => 'in_use', 'condition' => 'baik', 'price' => 4500000, 'purchase_date' => '2025-07-01', 'location' => 'Ruang Manager', 'vendor' => 'IKEA Indonesia', 'serial_number' => null, 'description' => 'Meja kerja adjustable height, electric motor'],

            // ATK (ATK)
            ['category' => 'atk', 'name' => 'Whiteboard Magnetic 120x240cm', 'person_in_charge' => null, 'status' => 'in_use', 'condition' => 'baik', 'price' => 850000, 'purchase_date' => '2025-03-15', 'location' => 'Ruang Meeting B', 'vendor' => 'Sakana Office', 'serial_number' => null, 'description' => 'Whiteboard magnetic ukuran besar untuk brainstorming'],

            // Alat Rumah Tangga (ART)
            ['category' => 'art', 'name' => 'Dispenser Miyako Hot & Cool', 'person_in_charge' => null, 'status' => 'in_use', 'condition' => 'baik', 'price' => 750000, 'purchase_date' => '2025-06-20', 'location' => 'Pantry', 'vendor' => 'Miyako Official', 'serial_number' => 'MYK-DSP-2025-002', 'description' => 'Dispenser air panas & dingin untuk karyawan'],

            // Kendaraan (KND)
            ['category' => 'kendaraan', 'name' => 'Toyota Avanza 1.5 G CVT 2024', 'person_in_charge' => 'Driver Adi', 'status' => 'in_use', 'condition' => 'baik', 'price' => 265000000, 'purchase_date' => '2024-10-15', 'location' => 'Parkiran Kantor', 'vendor' => 'Toyota Astra Motor', 'serial_number' => 'MHKM1BA2JRK012345', 'description' => 'Mobil operasional kantor, warna putih, plat B 1234 ABC'],

            // Mesin & Peralatan (MP)
            ['category' => 'mesin-peralatan', 'name' => 'Genset Yamaha EF6300iSE', 'person_in_charge' => null, 'status' => 'available', 'condition' => 'baik', 'price' => 35000000, 'purchase_date' => '2025-02-28', 'location' => 'Gudang Belakang', 'vendor' => 'Yamaha Motor', 'serial_number' => 'YMH-GS-2025-001', 'description' => 'Generator set inverter 6.3KVA untuk backup listrik'],

            // Dokumen & Arsip (DA)
            ['category' => 'dokumen-arsip', 'name' => 'Lemari Arsip Besi 4 Laci', 'person_in_charge' => null, 'status' => 'in_use', 'condition' => 'baik', 'price' => 2200000, 'purchase_date' => '2024-11-05', 'location' => 'Ruang Admin', 'vendor' => 'Brother Furniture', 'serial_number' => null, 'description' => 'Filing cabinet besi 4 drawer dengan kunci pengaman'],
            ['category' => 'dokumen-arsip', 'name' => 'Mesin Penghancur Kertas GBC ShredMaster', 'person_in_charge' => null, 'status' => 'maintenance', 'condition' => 'rusak', 'price' => 3500000, 'purchase_date' => '2025-01-10', 'location' => 'Ruang Admin', 'vendor' => 'GBC Official', 'serial_number' => 'GBC-SM-2025-004', 'description' => 'Shredder cross-cut, motor perlu service'],

            // Lain-lain (LL)
            ['category' => 'lain-lain', 'name' => 'Brankas Chubb Safes Cobra', 'person_in_charge' => null, 'status' => 'in_use', 'condition' => 'baik', 'price' => 9800000, 'purchase_date' => '2024-08-20', 'location' => 'Ruang Keuangan', 'vendor' => 'Chubb Safes', 'serial_number' => 'CHB-CBR-2024-001', 'description' => 'Brankas tahan api untuk dokumen penting dan petty cash'],
        ];

        foreach ($assets as $data) {
            $category = $categories[$data['category']] ?? null;
            if (!$category) {
                continue;
            }

            $prefix = $category->prefix;
            $year = date('y', strtotime($data['purchase_date']));

            $lastAsset = Asset::where('asset_tag', 'LIKE', "$prefix-$year-%")
                ->orderBy('id', 'desc')->first();
            $sequence = $lastAsset ? intval(substr($lastAsset->asset_tag, -3)) + 1 : 1;
            $assetTag = sprintf("%s-%s-%03d", $prefix, $year, $sequence);

            Asset::create([
                'asset_tag' => $assetTag,
                'name' => $data['name'],
                'person_in_charge' => $data['person_in_charge'],
                'purchase_date' => $data['purchase_date'],
                'category' => $data['category'],
                'status' => $data['status'],
                'condition' => $data['condition'],
                'price' => $data['price'],
                'location' => $data['location'] ?? null,
                'vendor' => $data['vendor'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'description' => $data['description'],
            ]);
        }
    }
}