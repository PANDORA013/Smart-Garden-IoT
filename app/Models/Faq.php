<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;
    
    protected $fillable = ['content', 'text_align'];
    
    protected $casts = [
        'content' => 'array'
    ];

    // Helper method untuk mendapatkan pertanyaan dalam bahasa tertentu
    public function getPertanyaan($lang = 'id')
    {
        return $this->content[$lang]['pertanyaan'] ?? '';
    }

    // Helper method untuk mendapatkan jawaban dalam bahasa tertentu
    public function getJawaban($lang = 'id')
    {
        return $this->content[$lang]['jawaban'] ?? '';
    }
}
