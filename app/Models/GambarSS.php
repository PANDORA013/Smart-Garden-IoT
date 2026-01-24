<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GambarSS extends Model
{
    use HasFactory;

    protected $table = 'gambar_s_s';

    protected $fillable = [
        'gambar1',
        'gambar2',
    ];
}
