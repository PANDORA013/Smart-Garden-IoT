<?php

namespace App\Http\Controllers;

use App\Models\Carouselds;
use App\Models\Gambards;
use App\Models\Textds;
use Illuminate\Http\Request;

class DigitalSolutionController extends Controller
{
    public function index()
    {
        $carousels = Carouselds::all();
        $gambards = Gambards::first();
        $texts = Textds::all();
        
        return view('produkdanlayanan.swads', compact('carousels','gambards','texts'));
    }
    public function indexEng()
    {
        $carousels = Carouselds::all();
        $gambards = Gambards::first();
        $texts = Textds::all();
        
        return view('eng.produkdanlayanan-eng.swads-eng', compact('carousels','gambards','texts'));
    }
}
