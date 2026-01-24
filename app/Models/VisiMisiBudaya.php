<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisiMisiBudaya extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'content', 'text_align'];
    
    protected $casts = [
        'content' => 'array',
    ];
}
