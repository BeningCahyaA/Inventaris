<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Hapus kategori lama yang tidak dipakai
        Category::whereIn('slug', ['mobile', 'semi-mobile', 'fixed'])->delete();

        $categories = [
            ['name' => 'Elektronik',              'slug' => 'elektronik',       'prefix' => 'ELK'],
            ['name' => 'Komputer & Laptop',        'slug' => 'komputer-laptop',  'prefix' => 'KL'],
            ['name' => 'Jaringan & Telekomunikasi','slug' => 'jaringan-telkom',  'prefix' => 'JT'],
            ['name' => 'Perabotan & Furnitur',     'slug' => 'perabotan',        'prefix' => 'PRB'],
            ['name' => 'ATK (Alat Tulis Kantor)',  'slug' => 'atk',              'prefix' => 'ATK'],
            ['name' => 'Alat Rumah Tangga',        'slug' => 'art',              'prefix' => 'ART'],
            ['name' => 'Kendaraan',                'slug' => 'kendaraan',        'prefix' => 'KND'],
            ['name' => 'Mesin & Peralatan',        'slug' => 'mesin-peralatan',  'prefix' => 'MP'],
            ['name' => 'Dokumen & Arsip',          'slug' => 'dokumen-arsip',    'prefix' => 'DA'],
            ['name' => 'Lain-lain',                'slug' => 'lain-lain',        'prefix' => 'LL'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
