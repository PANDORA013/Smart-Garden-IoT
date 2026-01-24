<?php

namespace App\Http\Controllers\Berita;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class BeritaController extends Controller
{
    public function index()
    {
        $berita = Berita::all(); 
        return view('berita.berita', compact('berita'));
    }
    public function indexEng()
    {
        $berita = Berita::all(); 
        return view('eng.berita-eng.berita-eng', compact('berita'));
    }

    public function showberita()
    {
        $berita = Berita::all(); 
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.berita.berita', compact('berita', 'userRole', 'layout'));
    }

    private function compressAndSaveImage($file)
    {
        try {
            Log::info('Starting image compression', ['original_size' => $file->getSize()]);
            
            // Create directory if it doesn't exist
            $storagePath = storage_path('app/public/beritas');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            $image->scaleDown(1200);

            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = in_array($extension, $allowedExtensions) ? $extension : 'jpg';

            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = 'beritas/' . $fileName;

            $fullPath = storage_path('app/public/' . $path);
            
            switch ($extension) {
                case 'gif':
                    $image->toGif()->save($fullPath);
                    break;
                case 'png':
                    $image->toPng()->save($fullPath);
                    break;
                default:
                    $image->toJpeg(80)->save($fullPath);
            }

            if (!file_exists($fullPath)) {
                throw new \Exception("Failed to save image to {$fullPath}");
            }

            Log::info('Image compression completed', ['compressed_size' => filesize($fullPath)]);

            return $path;
        } catch (\Exception $e) {
            Log::error('Error in compressAndSaveImage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function formatDescription($text)
    {
        // Menghapus karakter kontrol kecuali newline
        $text = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        // Mengganti berbagai jenis newline menjadi \n
        return preg_replace('/\r\n|\r|\n/', "\n", $text);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $path = $this->compressAndSaveImage($request->file('image'));

            // Initialize Google Translate
            $tr = new GoogleTranslate('en');

            $berita = Berita::create([
                'image' => $path,
                'title' => [
                    'id' => $request->title,
                    'en' => $tr->translate($request->title)
                ],
                'description' => [
                    'id' => $this->formatDescription($request->description),
                    'en' => $tr->translate($this->formatDescription($request->description))
                ],
            ]);

            return response()->json(['success' => true, 'message' => 'Berita berhasil ditambahkan']);
        } catch (\Exception $e) {
            Log::error('Error in berita store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $berita = Berita::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            if ($request->hasFile('image')) {
                if ($berita->image) {
                    Storage::disk('public')->delete($berita->image);
                }
                $berita->image = $this->compressAndSaveImage($request->file('image'));
            }

            // Initialize Google Translate
            $tr = new GoogleTranslate('en');

            if ($request->filled('title')) {
                $berita->title = [
                    'id' => $request->title,
                    'en' => $tr->translate($request->title)
                ];
            }

            if ($request->filled('description')) {
                $berita->description = [
                    'id' => $this->formatDescription($request->description),
                    'en' => $tr->translate($this->formatDescription($request->description))
                ];
            }

            $berita->save();

            return response()->json(['success' => true, 'message' => 'Berita berhasil diperbarui']);
        } catch (\Exception $e) {
            Log::error('Error in berita update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $berita = Berita::findOrFail($id);

            if ($berita->image) {
                Storage::disk('public')->delete($berita->image);
            }

            $berita->delete();

            return response()->json(['success' => true, 'message' => 'Berita berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Error in berita destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}