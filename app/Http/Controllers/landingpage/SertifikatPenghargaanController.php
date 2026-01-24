<?php

namespace App\Http\Controllers\landingpage;

use App\Models\SertifikatPenghargaan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;

class SertifikatPenghargaanController extends Controller
{
    public function index()
    {
        $data = SertifikatPenghargaan::all();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.landingpage.sertifikat_penghargaan', compact('data', 'userRole', 'layout'));
    }

    private function compressAndSaveImage($file)
    {
        try {
            // Pastikan direktori ada
            $directory = storage_path('app/public/sertifikat_penghargaan');
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            Log::info('Starting image compression', ['original_size' => $file->getSize()]);
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            $image->scaleDown(1200);

            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = in_array($extension, $allowedExtensions) ? $extension : 'jpg';

            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = 'sertifikat_penghargaan/' . $fileName;

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

    public function store(Request $request)
    {
        Log::info('SertifikatPenghargaan store method called', ['request' => $request->all()]);

        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480', // 20MB limit
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['success' => false, 'message' => $validator->errors()], 422);
            }

            if (!$request->hasFile('image')) {
                Log::error('No image file uploaded');
                return response()->json(['success' => false, 'message' => 'No image file uploaded'], 400);
            }

            $file = $request->file('image');
            Log::info('Image file details', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);

            $path = $this->compressAndSaveImage($file);

            $sertifikat = SertifikatPenghargaan::create(['image' => $path]);

            Log::info('SertifikatPenghargaan created successfully', ['sertifikat_id' => $sertifikat->id]);

            return response()->json(['success' => true, 'message' => 'Sertifikat/Penghargaan berhasil ditambahkan']);
        } catch (\Exception $e) {
            Log::error('Error in SertifikatPenghargaan store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $sertifikat = SertifikatPenghargaan::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480', // 20MB limit
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()], 422);
            }

            if ($request->hasFile('image')) {
                if ($sertifikat->image) {
                    Storage::disk('public')->delete($sertifikat->image);
                }
                $sertifikat->image = $this->compressAndSaveImage($request->file('image'));
            }

            $sertifikat->save();

            return response()->json(['success' => true, 'message' => 'Sertifikat/Penghargaan berhasil diperbarui']);
        } catch (\Exception $e) {
            Log::error('Error in SertifikatPenghargaan update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $sertifikat = SertifikatPenghargaan::findOrFail($id);

            if ($sertifikat->image) {
                Storage::disk('public')->delete($sertifikat->image);
            }

            $sertifikat->delete();

            return response()->json(['success' => true, 'message' => 'Sertifikat/Penghargaan berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Error in SertifikatPenghargaan destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}