<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use App\Models\Gambar;
use App\Models\Text;
use Illuminate\Http\Request;

class DigitalSolutionController extends Controller
{
    public function index()
    {
        $carousels = Carousel::all();
        $gambards = Gambar::first();
        $texts = Text::all();
        
        return view('produkdanlayanan.swads', compact('carousels','gambards','texts'));
    }
    public function indexEng()
    {
        $carousels = Carousel::all();
        $gambards = Gambar::first();
        $texts = Text::all();
        
        return view('eng.produkdanlayanan-eng.swads-eng', compact('carousels','gambards','texts'));
    }
}
