<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoLayanan extends Model
{
    use HasFactory;
    protected $fillable = [
        'gambar_direksi_1',
        'gambar_direksi_2',
        'jejak_langkah',
    ];
}
