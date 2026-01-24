<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GambarSA extends Model
{
    use HasFactory;

    protected $table = 'gambar_s_a_s';

    protected $fillable = [
        'gambar1',
        'gambar2',
    ];
}
