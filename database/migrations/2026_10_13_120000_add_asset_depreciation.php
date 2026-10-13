<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('depreciation_rate', 5, 2)->default(0);
        });
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('year_number');
            $table->date('depreciated_on');
            $table->decimal('original_value', 18, 2);
            $table->decimal('rate', 5, 2);
            $table->decimal('opening_value', 18, 2);
            $table->decimal('amount', 18, 2);
            $table->decimal('closing_value', 18, 2);
            $table->timestamps();
            $table->unique(['asset_id', 'year_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::table('assets', fn (Blueprint $table) => $table->dropColumn('depreciation_rate'));
    }
};
