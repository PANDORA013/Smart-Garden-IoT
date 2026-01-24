<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextFM extends Model
{
    use HasFactory;
    
    protected $table = 'text_f_m_s';
    protected $fillable = ['content', 'text_align'];

    protected $casts = ['content' => 'array',];
}