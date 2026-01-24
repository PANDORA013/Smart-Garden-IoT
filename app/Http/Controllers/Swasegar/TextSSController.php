<?php

namespace App\Http\Controllers\Swasegar;

use App\models\TextSS;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TextSSController extends Controller
{
    public function index()
    {
        $texts = TextSS::orderBy('id')->get();
        $texts = $texts->map(function ($text, $index) {
            $text->row_number = $index + 1;
            return $text;
        });
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.swasegar.textSS', compact('texts', 'userRole', 'layout'));
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

        TextSS::create([
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

        $textSS = TextSS::findOrFail($id);
        $textSS->update([
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
        try {
            // Delete the specified text
            $textSS = TextSS::findOrFail($id);
            $textSS->delete();

            // Get all remaining texts ordered by ID
            $texts = TextSS::orderBy('id')->get();

            // Reset the auto-increment
            DB::statement('ALTER TABLE text_s_s AUTO_INCREMENT = 1');

            // Update the IDs
            foreach ($texts as $index => $text) {
                $newId = $index + 1;
                if ($text->id !== $newId) {
                    DB::table('text_s_s')->where('id', $text->id)->update(['id' => $newId]);
                }
            }

            if (request()->ajax()) {
                return response()->json(['success' => 'Text berhasil dihapus dan ID direset.']);
            }
            return redirect()->back()->with('success', 'Text berhasil dihapus dan ID direset.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
