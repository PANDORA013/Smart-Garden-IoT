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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('device_name')->nullable();
            $table->string('plant_type')->nullable();
            $table->enum('status', ['online', 'idle', 'offline', 'never_connected'])->default('offline');
            $table->timestamp('last_seen')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('hardware_status')->nullable();
            $table->timestamps();
            
            // Index untuk frequently queried fields
            $table->index('device_id');
            $table->index('status');
            $table->index('last_seen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
