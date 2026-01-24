<?php

namespace App\Http\Controllers\landingpage;

use App\Http\Controllers\Controller;
use App\Models\FotoLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FotoLayananController extends Controller
{
    // Menampilkan halaman utama (landing page)
    public function landingpage()
    {
        $fotoLayanans = FotoLayanan::all();
        return view('landingpage', compact('fotoLayanans'));
    }

    // Menampilkan halaman foto layanan CRUD
    public function index()
    {
        $fotoLayanan = FotoLayanan::first(); // Mengambil satu record pertama
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.landingpage.foto_layanan', compact('fotoLayanan', 'userRole', 'layout'));
    }

    // Fungsi untuk mengompres dan menyimpan gambar
    private function compressAndSaveImage($file)
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            Log::info('Original image size: ' . $file->getSize() . ' bytes');

            // Resize gambar ke lebar maksimum 1200px dengan mempertahankan aspek ratio
            $image->scaleDown(1200);

            // Tentukan format output berdasarkan ekstensi file asli
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = in_array($extension, $allowedExtensions) ? $extension : 'jpg';

            // Generate nama file unik
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = 'foto-layanan/' . $fileName;

            // Pastikan direktori ada
            $directory = storage_path('app/public/foto-layanan');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Kompres dan simpan gambar
            switch ($extension) {
                case 'gif':
                    $image->toGif()->save(storage_path('app/public/' . $path));
                    break;
                case 'png':
                    $image->toPng()->save(storage_path('app/public/' . $path));
                    break;
                default:
                    $image->toJpeg(80)->save(storage_path('app/public/' . $path));
            }

            Log::info('Compressed image size: ' . filesize(storage_path('app/public/' . $path)) . ' bytes');

            return $path;
        } catch (\Exception $e) {
            Log::error('Error in compressAndSaveImage: ' . $e->getMessage());
            throw $e;
        }
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
                'imageType' => 'required|in:gambar_direksi_1,gambar_direksi_2,jejak_langkah',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $fotoLayanan = FotoLayanan::firstOrNew();
            $imageType = $request->input('imageType');

            if ($request->hasFile('image')) {
                $fotoLayanan->$imageType = $this->compressAndSaveImage($request->file('image'));
            }

            $fotoLayanan->save();

            return response()->json(['success' => true, 'message' => 'Foto Layanan berhasil ditambahkan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Memperbarui data yang ada di database
    public function update(Request $request, $id)
    {
        try {
            $fotoLayanan = FotoLayanan::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
                'imageType' => 'required|in:gambar_direksi_1,gambar_direksi_2,jejak_langkah',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $imageType = $request->input('imageType');

            if ($request->hasFile('image')) {
                if ($fotoLayanan->$imageType) {
                    Storage::disk('public')->delete($fotoLayanan->$imageType);
                }
                $fotoLayanan->$imageType = $this->compressAndSaveImage($request->file('image'));
            }

            $fotoLayanan->save();

            return response()->json(['success' => true, 'message' => 'Foto Layanan berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Menghapus data dari database
    public function destroy($id)
    {
        try {
            $fotoLayanan = FotoLayanan::findOrFail($id);

            if ($fotoLayanan->gambar_direksi_1) {
                Storage::disk('public')->delete($fotoLayanan->gambar_direksi_1);
            }

            if ($fotoLayanan->gambar_direksi_2) {
                Storage::disk('public')->delete($fotoLayanan->gambar_direksi_2);
            }
            if ($fotoLayanan->jejak_langkah) {
                Storage::disk('public')->delete($fotoLayanan->jejak_langkah);
            }

            $fotoLayanan->delete();

            return response()->json(['success' => true, 'message' => 'Foto Layanan berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
};