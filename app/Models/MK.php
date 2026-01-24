<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MK extends Model
{
    protected $fillable = ['image', 'title', 'description'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
    ];
}