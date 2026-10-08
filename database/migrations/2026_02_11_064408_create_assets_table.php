<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique(); 
            $table->string('name');
            $table->string('person_in_charge')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('category')->default('General'); 
            $table->string('status')->default('available');
            $table->string('condition')->default('Baik');
            $table->bigInteger('price')->default(0)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
