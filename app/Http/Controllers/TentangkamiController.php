<?php

namespace App\Http\Controllers;
use App\Models\VisiMisiBudaya;
use App\Models\SekilasPerusahaan;
use App\Models\SertifikatPenghargaan;
use App\Models\FotoLayanan;
use Illuminate\Http\Request;

class TentangkamiController extends Controller
{
    public function sekilasPerusahaan()
    {
        $sekilas = SekilasPerusahaan::all();
        $fotolayanan = FotoLayanan::all();
        
        return view('tentangkami.sekilas', compact('sekilas', 'fotolayanan'));
    }
    public function visiMisiBudaya()
    {
        $visiMisiBudaya = VisiMisiBudaya::all();
        
        return view('tentangkami.visimisi', compact('visiMisiBudaya'));
    }
    public function sertifikat()
    {
        $sertifikatPenghargaan = SertifikatPenghargaan::all();
        return view('tentangkami.sertifikat', compact('sertifikatPenghargaan'));
    }

    //Controller English
    public function sekilasPerusahaanEng()
    {
        $sekilas = SekilasPerusahaan::all();
        $fotolayanan = FotoLayanan::all();
        
        return view('eng.tentangkami-eng.sekilas-eng', compact('sekilas', 'fotolayanan'));
    }
    public function visiMisiBudayaEng()
    {
        $visiMisiBudaya = VisiMisiBudaya::all();
        
        return view('eng.tentangkami-eng.visimisi-eng', compact('visiMisiBudaya'));
    }
    public function sertifikatEng()
    {
        $sertifikatPenghargaan = SertifikatPenghargaan::all();
        return view('eng.tentangkami-eng.sertifikat-eng', compact('sertifikatPenghargaan'));
    }
}
