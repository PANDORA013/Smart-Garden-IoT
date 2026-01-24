<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CarouselFM;
use App\Models\GambarFM;
use App\Models\TextFM;
class FacilityManagementController extends Controller
{
    public function index()
    {
        $carousels = CarouselFM::all();
        $gambarFM = GambarFM::first() ?? new GambarFM(); 
        $texts = TextFM::all();
        return view('produkdanlayanan.swafm', compact('carousels', 'gambarFM', 'texts'));
        
    }
    public function indexEng()
    {
        $carousels = CarouselFM::all();
        $gambarFM = GambarFM::first() ?? new GambarFM(); 
        $texts = TextFM::all();
        return view('eng.produkdanlayanan-eng.swafm-eng', compact('carousels', 'gambarFM', 'texts'));
        
    }
}
