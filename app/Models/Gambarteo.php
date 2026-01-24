<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gambarteo extends Model
{
    use HasFactory;
    protected $table = 'gambarteos';
    protected $fillable = ['gambar1', 'gambar2'];
}
