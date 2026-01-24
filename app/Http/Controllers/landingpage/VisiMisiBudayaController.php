<?php

namespace App\Http\Controllers\landingpage;
use App\Models\VisiMisiBudaya;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class VisiMisiBudayaController extends Controller
{
    public function index()
    {
        $visi = VisiMisiBudaya::where('type', 'visi')->get();
        $misi = VisiMisiBudaya::where('type', 'misi')->get();
        $budaya = VisiMisiBudaya::where('type', 'budaya')->get();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.landingpage.visimisi', compact('visi', 'misi', 'budaya', 'userRole', 'layout'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:visi,misi,budaya',
            'content' => 'required|string',
            'text_align' => 'required|in:left,center,right,justify',
        ]);

        $content = str_replace("\r\n", "\n", $request->content);
        
        // Initialize Google Translate
        $tr_en = new GoogleTranslate('en');

        $item = VisiMisiBudaya::create([
            'type' => $request->type,
            'content' => [
                'id' => $content,
                'en' => $tr_en->translate($content)
            ],
            'text_align' => $request->text_align,
        ]);

        return response()->json(['success' => true, 'message' => ucfirst($item->type) . ' successfully added']);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'content' => 'required|string',
                'text_align' => 'required|in:left,center,right,justify',
            ]);

            $content = str_replace("\r\n", "\n", $request->content);
            
            // Initialize Google Translate
            $tr_en = new GoogleTranslate('en');

            $item = VisiMisiBudaya::findOrFail($id);
            $item->update([
                'content' => [
                    'id' => $content,
                    'en' => $tr_en->translate($content)
                ],
                'text_align' => $request->text_align,
            ]);

            return response()->json(['success' => true, 'message' => ucfirst($item->type) . ' successfully updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating item'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = VisiMisiBudaya::findOrFail($id);
            $type = $item->type;
            $item->delete();

            return response()->json(['success' => true, 'message' => ucfirst($type) . ' successfully deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting item'], 500);
        }
    }
}