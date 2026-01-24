<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SekilasPerusahaan extends Model
{
    use HasFactory;

    protected $table = 'sekilas_perusahaans';
    protected $primaryKey = 'Id_sekilas';

    protected $fillable = ['maintext', 'text_align'];

    protected $casts = [ 'maintext' => 'array',];
}

