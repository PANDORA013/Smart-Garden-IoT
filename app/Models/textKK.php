<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class textKK extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'link', 'text_align'];
}
