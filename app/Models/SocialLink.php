<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = ['facebook', 'youtube', 'youtube_landing', 'whatsapp', 'instagram'];

    // Validasi URL berdasarkan tipe
    public static function validateUrl($type, $url)
    {
        if (empty($url)) return true; // Boleh kosong/null

        switch ($type) {
            case 'facebook':
                return str_starts_with($url, 'https://facebook.com/') || 
                       str_starts_with($url, 'https://www.facebook.com/');
            case 'youtube':
            case 'youtube_landing':
                return str_starts_with($url, 'https://youtube.com/') || 
                       str_starts_with($url, 'https://www.youtube.com/');
            case 'whatsapp':
                return str_starts_with($url, 'https://wa.me/') || 
                       str_starts_with($url, 'https://api.whatsapp.com/');
            case 'instagram':
                return str_starts_with($url, 'https://instagram.com/') || 
                       str_starts_with($url, 'https://www.instagram.com/');
            default:
                return false;
        }
    }
}
