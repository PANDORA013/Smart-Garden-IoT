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
        Schema::table('device_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('device_settings', 'relay_command')) {
                $table->boolean('relay_command')->default(false)->comment('Relay command: ON/OFF')->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            if (Schema::hasColumn('device_settings', 'relay_command')) {
                $table->dropColumn('relay_command');
            }
        });
    }
};
