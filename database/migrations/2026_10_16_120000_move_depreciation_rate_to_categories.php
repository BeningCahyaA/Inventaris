<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->decimal('depreciation_rate', 5, 2)->default(0);
        });
        // Transfer only unambiguous settings; mixed categories require an explicit choice.
        DB::table('categories')->orderBy('id')->each(function ($category) {
            $rates = DB::table('assets')->where('category', $category->slug)
                ->whereNull('deleted_at')->distinct()->pluck('depreciation_rate');
            if ($rates->count() === 1) {
                DB::table('categories')->where('id', $category->id)
                    ->update(['depreciation_rate' => $rates->first()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('depreciation_rate'));
    }
};
