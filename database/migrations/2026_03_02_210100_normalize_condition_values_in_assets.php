<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normalisasi data condition yang sudah ada ke format enum
        DB::table('assets')->where('condition', 'like', '%baik%')->update(['condition' => 'baik']);
        DB::table('assets')->where('condition', 'like', '%Rusak Total%')->update(['condition' => 'rusak_total']);
        DB::table('assets')
            ->where('condition', 'like', '%rusak%')
            ->where('condition', '!=', 'rusak_total')
            ->update(['condition' => 'rusak']);

        // Value lain yang mungkin nyangkut
        DB::table('assets')
            ->whereNotIn('condition', ['baik', 'rusak', 'rusak_total'])
            ->update(['condition' => 'baik']);
    }

    public function down(): void
    {
        // Kembalikan ke format title case
        DB::table('assets')->where('condition', 'baik')->update(['condition' => 'Baik']);
        DB::table('assets')->where('condition', 'rusak')->update(['condition' => 'Rusak']);
        DB::table('assets')->where('condition', 'rusak_total')->update(['condition' => 'Rusak Total']);
    }
};
