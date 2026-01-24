<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\GambarSS; 
use App\Models\SwasegarCarousel;
use App\Models\TextSS;

class SwasegarController extends Controller
{
    public function index()
    {
        $carousels = SwasegarCarousel::all();
        $gambarSS = GambarSS::first(); 
        $textss = TextSS::all();
        return view('produkdanlayanan.swas', compact('carousels', 'gambarSS', 'textss')); 
    }   
    public function indexEng()
    {
        $carousels = SwasegarCarousel::all();
        $gambarSS = GambarSS::first(); 
        $textss = TextSS::all();
        return view('eng.produkdanlayanan-eng.swas-eng', compact('carousels', 'gambarSS', 'textss')); 
    }   
}
