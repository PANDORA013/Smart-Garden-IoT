<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carouselds extends Model
{
    use HasFactory;
    protected $table = 'carouselds';

    protected $fillable = ['image'];
}
