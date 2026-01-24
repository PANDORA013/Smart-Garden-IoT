<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarouselFM extends Model
{
    use HasFactory;
    protected $table = 'carousel_f_m_s';

    protected $fillable = [
        'image',
    ];
}
