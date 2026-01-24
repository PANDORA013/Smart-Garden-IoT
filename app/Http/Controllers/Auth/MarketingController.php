<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Faq;
use App\Models\SertifikatPenghargaan;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function index()
    {
        $beritas = Berita::all();
        $sertifikats = SertifikatPenghargaan::all();
        $faq = faq::all();
        return view('admin.marketing', compact('beritas', 'sertifikats','faq')); // Anda perlu membuat file view ini
    }
}
