<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GambarFM extends Model
{
    use HasFactory;

    protected $table = 'gambar_f_m_s';

    protected $fillable = [
        'gambar1',
        'gambar2',
    ];
}
