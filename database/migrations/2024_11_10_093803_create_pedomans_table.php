<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedomans', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file')->nullable();
            $table->string('gambar')->nullable();
            $table->enum('text_align', ['left', 'center', 'right', 'justify'])->default('left');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedomans');
    }
};