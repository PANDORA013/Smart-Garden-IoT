<?php

namespace App\Http\Controllers;
use App\Models\CarouselSA;
use App\Models\GambarSA;
use App\Models\TextSA;
use Illuminate\Http\Request;

class SwaacademyController extends Controller
{
    public function index()
    {
        $carousels = CarouselSA::all();
        $gambarSA = GambarSA::first();
        $texts = TextSA::all();
        
        return view('produkdanlayanan.swaac', compact('carousels', 'gambarSA', 'texts'));
    }
    public function indexEng()
    {
        $carousels = CarouselSA::all();
        $gambarSA = GambarSA::first();
        $texts = TextSA::all();
        
        return view('eng.produkdanlayanan-eng.swaac-eng', compact('carousels', 'gambarSA', 'texts'));
    }
}
