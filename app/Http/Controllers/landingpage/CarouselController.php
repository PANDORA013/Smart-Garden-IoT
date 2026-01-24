<?php

namespace App\Http\Controllers\landingpage;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Validation\ValidationException; // Add this line
use Illuminate\Support\Facades\Log;

class CarouselController extends Controller
{
    // Menampilkan halaman utama (landing page)
    public function landingpage()
    {
        $carousels = Carousel::all();
        return view('landingpage', compact('carousels'));
    }

    // Menampilkan halaman carousel CRUD
    public function showCarousel()
    {
        $carousels = Carousel::all();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.landingpage.carousel', compact('carousels', 'userRole', 'layout'));
    }

    // Fungsi untuk mengompres dan menyimpan gambar
    private function compressAndSaveImage($file)
    {
        try {
            Log::info('Starting image compression', ['original_size' => $file->getSize()]);
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            $image->scaleDown(1200);

            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = in_array($extension, $allowedExtensions) ? $extension : 'jpg';

            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = 'carousels/' . $fileName;

            $fullPath = storage_path('app/public/' . $path);
            
            // Create the directory if it doesn't exist
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
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

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        Log::info('Carousel store method called', ['request' => $request->except('image')]);

        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480', // 20MB limit
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
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

            $carousel = Carousel::create([
                'image' => $path,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            Log::info('Carousel created successfully', ['carousel_id' => $carousel->id]);

            return response()->json(['success' => true, 'message' => 'Carousel berhasil ditambahkan']);
        } catch (\Exception $e) {
            Log::error('Error in Carousel store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Memperbarui data yang ada di database
    public function update(Request $request, $id)
    {
        Log::info('Carousel update method called', ['request' => $request->except('image'), 'id' => $id]);

        try {
            $carousel = Carousel::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480', // 20MB limit
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['success' => false, 'message' => $validator->errors()], 422);
            }

            if ($request->hasFile('image')) {
                // Delete old image
                if ($carousel->image) {
                    Storage::disk('public')->delete($carousel->image);
                }
                
                // Compress and save new image
                $carousel->image = $this->compressAndSaveImage($request->file('image'));
            }

            $carousel->title = $request->title ?? $carousel->title;
            $carousel->description = $request->description ?? $carousel->description;
            $carousel->save();

            Log::info('Carousel updated successfully', ['carousel_id' => $carousel->id]);

            return response()->json(['success' => true, 'message' => 'Carousel berhasil diperbarui']);
        } catch (\Exception $e) {
            Log::error('Error in Carousel update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Menghapus data dari database
    public function destroy($id)
    {
        try {
            $carousel = Carousel::findOrFail($id);

            if ($carousel->image) {
                Storage::disk('public')->delete($carousel->image);
            }

            $carousel->delete();

            return response()->json(['success' => true, 'message' => 'Carousel berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Error in Carousel destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
