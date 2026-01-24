<?php

namespace App\Http\Controllers\pedoman;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedoman;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PedomanController extends Controller
{
    public function index()
    {
        $pedomans = Pedoman::all();
        return view('admin.pedoman.pedoman', compact('pedomans'));
    }

    public function pedoman()
    {
        $pedomans = Pedoman::all();
        return view('kebijakandanpedoman.kp', compact('pedomans'));
    }
    public function pedomanEng()
    {
        $pedomans = Pedoman::all();
        return view('eng.kebijakandanpedoman-eng.kp-eng', compact('pedomans'));
    }

    private function compressAndSaveImage($image)
    {
        try {
            $manager = new ImageManager(new Driver());
            
            // Baca gambar
            $img = $manager->read($image->getRealPath());
            
            // Resize gambar
            $img->scaleDown(800);
            
            // Generate nama file unik
            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Tentukan path penyimpanan
            $path = 'public/images/' . $fileName;
            
            // Encode dan simpan sesuai format
            switch (strtolower($image->getClientOriginalExtension())) {
                case 'png':
                    Storage::put($path, $img->toPng());
                    break;
                case 'gif':
                    Storage::put($path, $img->toGif());
                    break;
                default:
                    Storage::put($path, $img->toJpeg(80));
            }
            
            return $fileName;
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'judul' => 'required|string|max:255',
                'file' => 'required|mimes:pdf,doc,docx,xls,xlsx|max:20480',
                'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
                'deskripsi' => 'nullable|string',
                'text_align' => 'required|in:left,center,right,justify',
            ]);

            // Pastikan direktori ada
            if (!Storage::exists('public/documents')) {
                Storage::makeDirectory('public/documents');
            }
            if (!Storage::exists('public/images')) {
                Storage::makeDirectory('public/images');
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/documents', $fileName);

            $gambarName = null;
            if ($request->hasFile('gambar')) {
                $gambarName = $this->compressAndSaveImage($request->file('gambar'));
            }

            $pedoman = Pedoman::create([
                'judul' => $request->judul,
                'file' => $fileName,
                'gambar' => $gambarName,
                'deskripsi' => $request->deskripsi,
                'text_align' => $request->text_align,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pedoman berhasil ditambahkan',
                'data' => $pedoman
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pedoman: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'judul' => 'required|string|max:255',
                'file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx|max:20480',
                'gambar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:20840',
                'deskripsi' => 'nullable|string',
                'text_align' => 'required|in:left,center,right,justify',
            ]);

            $pedoman = Pedoman::findOrFail($id);

            // Update basic fields
            $pedoman->judul = $request->judul;
            $pedoman->deskripsi = $request->deskripsi;
            $pedoman->text_align = $request->text_align;

            // Handle file upload if new file is provided
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($pedoman->file) {
                    Storage::delete('public/documents/' . $pedoman->file);
                }
                $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
                $request->file('file')->storeAs('public/documents', $fileName);
                $pedoman->file = $fileName;
            }

            // Handle image upload if new image is provided
            if ($request->hasFile('gambar')) {
                // Delete old image if exists
                if ($pedoman->gambar) {
                    Storage::delete('public/images/' . $pedoman->gambar);
                }
                $pedoman->gambar = $this->compressAndSaveImage($request->file('gambar'));
            }

            $pedoman->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pedoman = Pedoman::findOrFail($id);
            if ($pedoman->file) {
                Storage::delete('public/documents/' . $pedoman->file);
            }
            if ($pedoman->gambar) {
                Storage::delete('public/images/' . $pedoman->gambar);
            }
            $pedoman->delete();
            return response()->json([
                'success' => true,
                'message' => 'Pedoman berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pedoman: ' . $e->getMessage()
            ], 500);
        }
    }
}
