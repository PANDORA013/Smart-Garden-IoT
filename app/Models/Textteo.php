<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Textteo extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'text_align'];

    protected $casts = ['content' => 'array',];
}
