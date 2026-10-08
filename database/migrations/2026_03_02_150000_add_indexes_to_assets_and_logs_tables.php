<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->index('status');
            $table->index('category');
            $table->index('condition');
            $table->index('purchase_date');
        });

        Schema::table('asset_logs', function (Blueprint $table) {
            $table->index('status');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['category']);
            $table->dropIndex(['condition']);
            $table->dropIndex(['purchase_date']);
        });

        Schema::table('asset_logs', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['action']);
        });
    }
};
