<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JejakLangkah extends Model
{
    use HasFactory;

    // Tentukan nama tabel yang digunakan
    protected $table = 'jejak_langkahs';

    // Tentukan kolom yang dapat diisi secara massal
    protected $fillable = [
        'image', // Menyimpan path gambar
    ];
}
