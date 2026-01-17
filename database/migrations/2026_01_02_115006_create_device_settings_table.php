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
        Schema::create('device_settings', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique()->comment('ID Unik Alat (e.g., CABAI_01, ESP32-A1)');
            $table->string('device_name')->nullable()->comment('Nama alat yang mudah dibaca');
            $table->string('plant_type')->default('cabai')->comment('Jenis tanaman (cabai, tomat, dll)');
            
            // === MODE OPERASI (1=Basic, 2=Fuzzy Logic, 3=Schedule) ===
            $table->integer('mode')->default(1)->comment('1=Basic Threshold, 2=Fuzzy Logic, 3=Schedule');
            
            // === KALIBRASI SENSOR (Auto-filled untuk device baru) ===
            $table->integer('sensor_min')->default(4095)->comment('ADC value sensor kering (di udara)');
            $table->integer('sensor_max')->default(1500)->comment('ADC value sensor basah (di air)');
            
            // === PARAMETER MODE 1: BASIC THRESHOLD ===
            $table->integer('batas_siram')->default(20)->comment('Pompa ON jika kelembaban < nilai ini (%) - Default 20%');
            $table->integer('batas_stop')->default(30)->comment('Pompa OFF jika kelembaban >= nilai ini (%) - Default 30%');
            
            // === PARAMETER MODE 2: FUZZY LOGIC ===
            // Tidak ada parameter - fully automatic based on sensor readings
            
            // === PARAMETER MODE 3: SCHEDULE ===
            $table->time('jam_pagi')->default('07:00:00')->comment('Jadwal siram pagi (Mode 3)');
            $table->time('jam_sore')->default('17:00:00')->comment('Jadwal siram sore (Mode 3)');
            $table->integer('durasi_siram')->default(5)->comment('Durasi siram dalam detik (Mode 3)');
            
            // === STATUS & INFO ===
            $table->boolean('is_active')->default(true)->comment('Status alat aktif/nonaktif');
            $table->timestamp('last_seen')->nullable()->comment('Terakhir kali alat lapor ke server');
            $table->string('firmware_version')->nullable()->comment('Versi firmware Arduino');
            $table->text('notes')->nullable()->comment('Catatan admin');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_settings');
    }
};
