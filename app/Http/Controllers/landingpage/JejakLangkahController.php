<?php

namespace App\Http\Controllers\landingpage;
use App\Models\JejakLangkah;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class JejakLangkahController extends Controller
{
    public function index()
    {
        $jejakLangkah = JejakLangkah::first();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.landingpage.jejaklangkah', compact('jejakLangkah', 'userRole', 'layout'));
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
            $path = 'jejak_langkah/' . $fileName;

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

            JejakLangkah::create([
                'image' => $imagePath,
            ]);

            return response()->json(['success' => true, 'message' => 'Jejak Langkah created successfully.']);
        } catch (\Exception $e) {
            Log::error('Error in JejakLangkah store', [
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

            $jejakLangkah = JejakLangkah::findOrFail($id);

            if ($request->hasFile('image')) {
                // Delete old image
                if ($jejakLangkah->image) {
                    Storage::disk('public')->delete($jejakLangkah->image);
                }

                $imagePath = $this->compressAndSaveImage($request->file('image'));
                $jejakLangkah->image = $imagePath;
            }

            $jejakLangkah->save();

            return response()->json(['success' => true, 'message' => 'Jejak Langkah updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error in JejakLangkah update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $jejakLangkah = JejakLangkah::findOrFail($id);

        // Delete image from storage
        if ($jejakLangkah->image) {
            Storage::disk('public')->delete($jejakLangkah->image);
        }

        $jejakLangkah->delete();

        return response()->json(['success' => true, 'message' => 'Jejak Langkah deleted successfully.']);
    }
}
