<?php

namespace App\Http\Controllers\swaacademy;

use App\Models\TextSA;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TextSAController extends Controller
{
    public function index()
    {
        $texts = TextSA::all();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.swaacademy.textSA', compact('texts', 'userRole', 'layout'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'text_align' => 'required|in:left,center,right,justify',
        ]);

        $content = str_replace("\r\n", "\n", $request->text);
        
        // Initialize Google Translate
        $tr_en = new GoogleTranslate('en');

        TextSA::create([
            'content' => [
                'id' => $content,
                'en' => $tr_en->translate($content)
            ],
            'text_align' => $request->text_align,
        ]);

        return redirect()->back()->with('success', 'Text berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'text' => 'required|string',
            'text_align' => 'required|in:left,center,right,justify',
        ]);

        $content = str_replace("\r\n", "\n", $request->text);
        
        // Initialize Google Translate
        $tr_en = new GoogleTranslate('en');

        $textSA = TextSA::findOrFail($id);
        $textSA->update([
            'content' => [
                'id' => $content,
                'en' => $tr_en->translate($content)
            ],
            'text_align' => $request->text_align,
        ]);

        return redirect()->back()->with('success', 'Text berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $textSA = TextSA::findOrFail($id);
        $textSA->delete();

        return redirect()->back()->with('success', 'Text berhasil dihapus.');
    }
}
