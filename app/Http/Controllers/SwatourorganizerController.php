<?php

namespace App\Http\Controllers;

use App\Models\Carouselteo;
use App\Models\Gambarteo;
use App\Models\Textteo;
use Illuminate\Http\Request;

class SwatourorganizerController extends Controller
{
    public function index()
    {
        $carousels = Carouselteo::all(); 
        $gambars = Gambarteo::all(); 
        $texts = Textteo::all(); 
        return view('produkdanlayanan.swateo', compact('carousels', 'gambars', 'texts')); 
    }
    public function indexEng()
    {
        $carousels = Carouselteo::all(); 
        $gambars = Gambarteo::all(); 
        $texts = Textteo::all(); 
        return view('eng.produkdanlayanan-eng.swateo-eng', compact('carousels', 'gambars', 'texts')); 
    }
}
