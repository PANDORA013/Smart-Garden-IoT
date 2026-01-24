<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextSA extends Model
{
    use HasFactory;
    
    protected $table = 'text_s_a_s';
    protected $fillable = ['content', 'text_align'];

    protected $casts = ['content' => 'array',];
}
