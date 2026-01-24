<?php

namespace App\Http\Controllers\kontakkami;

use App\Models\textKK;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TextKKController extends Controller
{
    public function index()
    {
        $texts = textKK::all();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.kontakkami.textKK', compact('texts', 'userRole', 'layout'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'link' => 'required|url',
            'text_align' => 'required|in:left,center,right,justify',
        ]);

        $content = str_replace("\r\n", "\n", $request->content);

        textKK::create([
            'content' => $content,
            'link' => $request->link,
            'text_align' => $request->text_align,
        ]);

        return redirect()->route('admin.kontakkami.textkk.index')->with('success', 'Text berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
            'link' => 'required|url',
            'text_align' => 'required|in:left,center,right,justify',
        ]);

        $content = str_replace("\r\n", "\n", $request->content);

        $textKK = textKK::findOrFail($id);
        $textKK->update([
            'content' => $content,
            'link' => $request->link,
            'text_align' => $request->text_align,
        ]);

        return redirect()->route('admin.kontakkami.textkk.index')->with('success', 'Text berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $textKK = textKK::findOrFail($id);
        $textKK->delete();

        return redirect()->route('admin.kontakkami.textkk.index')->with('success', 'Text berhasil dihapus.');
    }
}
