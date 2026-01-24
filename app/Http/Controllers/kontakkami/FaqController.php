<?php

namespace App\Http\Controllers\kontakkami;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stichoza\GoogleTranslate\GoogleTranslate;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('id')->get();
        $faqs = $faqs->map(function ($faq, $index) {
            $faq->row_number = $index + 1;
            return $faq;
        });
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.kontakkami.faq', compact('faqs', 'userRole', 'layout'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan' => 'required|string',
            'jawaban' => 'required|string',
            'text_align' => 'required|in:left,center,right,justify',
        ]);

        try {
            // Initialize Google Translate
            $tr = new GoogleTranslate();
            
            // Translate to English
            $tr->setTarget('en');
            $pertanyaan_en = $tr->translate($request->pertanyaan);
            $jawaban_en = $tr->translate($request->jawaban);

            Faq::create([
                'content' => [
                    'id' => [
                        'pertanyaan' => $request->pertanyaan,
                        'jawaban' => $request->jawaban
                    ],
                    'en' => [
                        'pertanyaan' => $pertanyaan_en,
                        'jawaban' => $jawaban_en
                    ]
                ],
                'text_align' => $request->text_align
            ]);

            return response()->json(['success' => 'FAQ berhasil ditambahkan']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pertanyaan' => 'required|string',
            'jawaban' => 'required|string',
            'text_align' => 'required|in:left,center,right,justify',
        ]);

        try {
            $faq = Faq::findOrFail($id);

            // Initialize Google Translate
            $tr = new GoogleTranslate();
            
            // Translate to English
            $tr->setTarget('en');
            $pertanyaan_en = $tr->translate($request->pertanyaan);
            $jawaban_en = $tr->translate($request->jawaban);

            $faq->update([
                'content' => [
                    'id' => [
                        'pertanyaan' => $request->pertanyaan,
                        'jawaban' => $request->jawaban
                    ],
                    'en' => [
                        'pertanyaan' => $pertanyaan_en,
                        'jawaban' => $jawaban_en
                    ]
                ],
                'text_align' => $request->text_align
            ]);

            return response()->json(['success' => 'FAQ berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            $faq->delete();

            // Reset auto increment
            $faqs = Faq::orderBy('id')->get();
            DB::statement('ALTER TABLE faqs AUTO_INCREMENT = 1');

            // Update IDs
            foreach ($faqs as $index => $faq) {
                $newId = $index + 1;
                if ($faq->id !== $newId) {
                    DB::table('faqs')->where('id', $faq->id)->update(['id' => $newId]);
                }
            }

            return response()->json(['success' => 'FAQ berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getContent($id, $lang)
    {
        try {
            $faq = Faq::findOrFail($id);
            return response()->json([
                'pertanyaan' => $faq->getPertanyaan($lang),
                'jawaban' => $faq->getJawaban($lang)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
