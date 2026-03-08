<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Optimize database queries for frequently accessed fields:
     * - device_id: Filter by specific device
     * - created_at: Sort and filter by time (latest records)
     * - relay_status: Monitor pump status changes
     * - temperature: Alert on extreme values
     * - soil_moisture: Filter by sensor readings
     */
    public function up(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            // Index for device filtering (commonly used in fetchStats, history, logs)
            $table->index(['device_id', 'created_at'], 'idx_device_created');
            
            // Index for latest record queries (ORDER BY created_at DESC LIMIT 1)
            $table->index('created_at', 'idx_created_at');
            
            // Index for relay status changes detection
            $table->index('relay_status', 'idx_relay_status');
            
            // Index for temperature alerts (ALERT: temp > 35°C)
            $table->index('temperature', 'idx_temperature');
            
            // Index for soil moisture threshold queries
            $table->index('soil_moisture', 'idx_soil_moisture');
            
            // Composite index for date range queries (last 24h, 7d, etc)
            $table->index(['device_id', 'created_at', 'relay_status'], 'idx_device_time_relay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropIndex('idx_device_created');
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_relay_status');
            $table->dropIndex('idx_temperature');
            $table->dropIndex('idx_soil_moisture');
            $table->dropIndex('idx_device_time_relay');
        });
    }
};
