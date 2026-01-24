<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarouselKK extends Model
{
    use HasFactory;
    protected $table = 'carousel_k_k_s';

    protected $fillable = ['image'];
}
