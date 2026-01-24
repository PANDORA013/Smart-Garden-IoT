<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gambards extends Model
{
    use HasFactory;

    protected $table = 'gambards';

    protected $fillable = [
        'gambar1',
        'gambar2',
    ];
}
