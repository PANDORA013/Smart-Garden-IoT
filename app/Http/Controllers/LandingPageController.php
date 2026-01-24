<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use App\Models\SekilasPerusahaan;
use App\Models\JejakLangkah;
use App\Models\SertifikatPenghargaan;
use App\Models\VisiMisiBudaya;
use App\Models\FotoLayanan;
use App\models\SocialLink;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $carousels = Carousel::all();
        $sekilas = SekilasPerusahaan::all();
        $jejakLangkahs = JejakLangkah::all();
        $sertifikatPenghargaans = SertifikatPenghargaan::all();
        $fotoLayanan = FotoLayanan::first();
        $social = SocialLink::first();

        // Fetch Visi, Misi, and Budaya separately
        $visi = VisiMisiBudaya::where('type', 'visi')->get();
        $misi = VisiMisiBudaya::where('type', 'misi')->get();
        $budaya = VisiMisiBudaya::where('type', 'budaya')->get();

        return view('beranda.landingpage', compact('carousels', 'sekilas', 'jejakLangkahs', 'sertifikatPenghargaans', 'fotoLayanan','visi','misi','budaya','social'));
    }
    public function indexEng()
    {
        $carousels = Carousel::all();
        $sekilas = SekilasPerusahaan::all();
        $jejakLangkahs = JejakLangkah::all();
        $sertifikatPenghargaans = SertifikatPenghargaan::all();
        $fotoLayanan = FotoLayanan::first();
        $social = SocialLink::first();
        // Fetch Visi, Misi, and Budaya separately
        $visi = VisiMisiBudaya::where('type', 'visi')->get();
        $misi = VisiMisiBudaya::where('type', 'misi')->get();
        $budaya = VisiMisiBudaya::where('type', 'budaya')->get();

        return view('eng.beranda-eng.landingpage-eng', compact('carousels', 'sekilas', 'jejakLangkahs', 'sertifikatPenghargaans', 'fotoLayanan','visi','misi','budaya','social'));
    }
}
