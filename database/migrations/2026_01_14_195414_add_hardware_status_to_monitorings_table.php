<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->json('hardware_status')->nullable()->after('raw_adc');
        });
    }

    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropColumn('hardware_status');
        });
    }
};
