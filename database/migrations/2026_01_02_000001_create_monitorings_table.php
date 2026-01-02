<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Struktur tabel untuk monitoring cabai (soil moisture + pompa saja)
     */
    public function up(): void
    {
        Schema::create('monitorings', function (Blueprint $table) {
            $table->id();
            $table->float('soil_moisture')->default(0)->comment('Kelembapan tanah (%)');
            $table->string('status_pompa', 10)->default('Mati')->comment('Status pompa (Hidup/Mati)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitorings');
    }
};
