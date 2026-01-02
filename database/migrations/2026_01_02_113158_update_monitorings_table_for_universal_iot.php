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
        Schema::table('monitorings', function (Blueprint $table) {
            // Tambah kolom baru untuk universal IoT dashboard
            $table->float('temperature')->nullable()->after('id');
            $table->float('humidity')->nullable()->after('temperature');
            $table->boolean('relay_status')->default(false)->after('humidity');
            $table->string('device_name')->nullable()->after('relay_status');
            $table->string('ip_address')->nullable()->after('device_name');
            
            // Ubah kolom lama agar nullable (backward compatible)
            $table->float('soil_moisture')->nullable()->change();
            $table->string('status_pompa')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropColumn(['temperature', 'humidity', 'relay_status', 'device_name', 'ip_address']);
        });
    }
};
