<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Struktur tabel untuk Smart Garden Gateway (Pico W)
     * Support: Multi-device, Fuzzy Logic, 2-Way Communication
     */
    public function up(): void
    {
        Schema::create('monitorings', function (Blueprint $table) {
            $table->id();
            
            // === DEVICE IDENTIFICATION ===
            $table->string('device_id')->index()->comment('ID Gateway (e.g., PICO_CABAI_01)');
            $table->string('device_name')->nullable()->comment('Nama device yang mudah dibaca');
            $table->string('ip_address')->nullable()->comment('IP Address device');
            
            // === SENSOR READINGS (Wajib untuk Fuzzy Logic) ===
            $table->float('soil_moisture')->default(0)->comment('Kelembapan tanah (%)');
            $table->float('temperature')->nullable()->comment('Suhu udara (°C) - untuk Fuzzy Logic');
            $table->float('humidity')->nullable()->comment('Kelembaban udara (%) - untuk Fuzzy Logic');
            
            // === ACTUATOR STATUS (2-Way Feedback) ===
            $table->string('status_pompa', 10)->default('Mati')->comment('Status pompa (Hidup/Mati)');
            $table->boolean('relay_status')->default(false)->comment('Status relay (true=ON, false=OFF)');
            
            // === METADATA ===
            $table->integer('raw_adc')->nullable()->comment('Nilai ADC mentah dari sensor');
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
