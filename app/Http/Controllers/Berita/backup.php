<?php

namespace App\Http\Controllers\Berita;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class BeritaController extends Controller
{
    // Menampilkan daftar berita
    public function index()
    {
        $berita = Berita::all(); // Ensure the variable name is singular
        return view('admin.berita.berita', compact('berita')); // Pass the correct variable
    }

    // Menampilkan halaman CRUD berita untuk admin
    public function showBeritaAdmin()
    {
        $berita = Berita::all();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.admin' : 'layouts.user';
        return view('admin.berita.berita.index', compact('berita', 'userRole', 'layout'));
    }

    // Fungsi untuk mengompres dan menyimpan gambar
    private function compressAndSaveImage($file)
    {
        try {
            Log::info('Starting image compression', ['original_size' => $file->getSize()]);

            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getPathname());

            // Skala down gambar jika lebih besar dari 1200px
            $image->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = in_array($extension, $allowedExtensions) ? $extension : 'jpg';

            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = 'berita/' . $fileName;

            $fullPath = storage_path('app/public/' . $path);

            switch ($extension) {
                case 'gif':
                    $image->save($fullPath, null, 'gif');
                    break;
                case 'png':
                    $image->save($fullPath, null, 'png');
                    break;
                default:
                    $image->save($fullPath, 80, 'jpeg');
            }

            if (!file_exists($fullPath)) {
                throw new \Exception("Gagal menyimpan gambar ke {$fullPath}");
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

    // Menyimpan berita baru ke database
    public function store(Request $request)
    {
        Log::info('Berita store method called', ['request' => $request->except('image')]);

        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480', // Maks 20MB
                'title' => 'required|string|max:255',
                'description' => 'required|string', // Pastikan ini sesuai dengan field di form
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['success' => false, 'message' => $validator->errors()], 422);
            }

            if (!$request->hasFile('image')) {
                Log::error('No image file uploaded');
                return response()->json(['success' => false, 'message' => 'Tidak ada file gambar yang diunggah'], 400);
            }

            $file = $request->file('image');
            Log::info('Image file details', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);

            $path = $this->compressAndSaveImage($file);

            $berita = Berita::create([
                'image' => $path,
                'title' => $request->title,
                'content' => $request->content,
            ]);

            Log::info('Berita created successfully', ['berita_id' => $berita->id]);

            return response()->json(['success' => true, 'message' => 'Berita berhasil ditambahkan']);
        } catch (\Exception $e) {
            Log::error('Error in Berita store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Memperbarui berita yang ada di database
    public function update(Request $request, $id)
    {
        Log::info('Berita update method called', ['request' => $request->except('image'), 'id' => $id]);

        try {
            $berita = Berita::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480', // Maks 20MB
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['success' => false, 'message' => $validator->errors()], 422);
            }

            if ($request->hasFile('image')) {
                // Hapus gambar lama
                if ($berita->image) {
                    Storage::disk('public')->delete($berita->image);
                }

                // Kompres dan simpan gambar baru
                $berita->image = $this->compressAndSaveImage($request->file('image'));
            }

            $berita->title = $request->title;
            $berita->content = $request->content;
            $berita->save();

            Log::info('Berita updated successfully', ['berita_id' => $berita->id]);

            return response()->json(['success' => true, 'message' => 'Berita berhasil diperbarui']);
        } catch (\Exception $e) {
            Log::error('Error in Berita update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Menghapus berita dari database
    public function destroy($id)
    {
        try {
            $berita = Berita::findOrFail($id);

            if ($berita->image) {
                Storage::disk('public')->delete($berita->image);
            }

            $berita->delete();

            Log::info('Berita deleted successfully', ['berita_id' => $id]);

            return response()->json(['success' => true, 'message' => 'Berita berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Error in Berita destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}