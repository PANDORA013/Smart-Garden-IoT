<?php

namespace App\Http\Controllers;

use App\Models\carousel;
use App\Models\CarouselKK;
Use App\Models\GambarKK;
use App\Models\textKK;
use App\Models\Faq;
use Illuminate\Http\Request;

class KontakkamiController extends Controller
{
    public function index()
    {
        $carousels = CarouselKK::all();
        $gambarKK = GambarKK::all();
        $textKK = textKK::all();
        $faqs = Faq::orderBy('id')->get();

        return view('kontak.kontak_kami', compact('carousels','gambarKK','textKK','faqs'));
    }
    public function indexEng()
    {
        $carousels = carouselKK::all();
        $gambarKK = GambarKK::all();
        $textKK = textKK::all();
        $faqs = Faq::orderBy('id')->get();
        return view('eng.kontak-eng.kontak-kami-eng', compact('carousels','gambarKK','textKK','faqs'));
    }
}
