<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carouselteo extends Model
{
    use HasFactory;

    protected $table = 'carouselteos';
    protected $fillable = ['image'];
}
