<?php

namespace App\Http\Controllers\karir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class KarirController extends Controller
{

    public function karir()
    {
        
        return view('karir.karir');
    }
    public function karirEng()
    {
        
        return view('eng.karir-eng.karir-eng');
    }

}
