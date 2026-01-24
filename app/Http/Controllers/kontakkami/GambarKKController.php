<?php

namespace App\Http\Controllers\kontakkami;

use App\Http\Controllers\Controller;
use App\Models\GambarKK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GambarKKController extends Controller
{
    public function index()
    {
        $gambarKK = GambarKK::first();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.kontakkami.gambarkk', compact('gambarKK', 'userRole', 'layout'));
    }

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
            $path = 'gambar_kk/' . $fileName;

            $fullPath = storage_path('app/public/' . $path);
            
            // Ensure the directory exists
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

    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480', // 20MB limit
            ]);

            $imagePath = $this->compressAndSaveImage($request->file('image'));

            GambarKK::create([
                'image' => $imagePath,
            ]);

            return response()->json(['success' => true, 'message' => 'Gambar KK created successfully.']);
        } catch (\Exception $e) {
            Log::error('Error in GambarKK store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480', // 20MB limit
            ]);

            $gambarKK = GambarKK::findOrFail($id);

            if ($request->hasFile('image')) {
                // Delete old image
                if ($gambarKK->image) {
                    Storage::disk('public')->delete($gambarKK->image);
                }

                $imagePath = $this->compressAndSaveImage($request->file('image'));
                $gambarKK->image = $imagePath;
            }

            $gambarKK->save();

            return response()->json(['success' => true, 'message' => 'Gambar KK updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error in GambarKK update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $gambarKK = GambarKK::findOrFail($id);

        // Delete image from storage
        if ($gambarKK->image) {
            Storage::disk('public')->delete($gambarKK->image);
        }

        $gambarKK->delete();

        return response()->json(['success' => true, 'message' => 'Gambar KK deleted successfully.']);
    }
}