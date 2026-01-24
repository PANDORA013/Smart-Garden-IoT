<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GambarKK extends Model
{
    use HasFactory;

    protected $table = 'gambar_k_k_s';

    protected $fillable = [
        'image',
    ];
}
