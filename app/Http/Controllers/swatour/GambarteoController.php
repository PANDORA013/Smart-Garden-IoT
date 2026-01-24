<?php

namespace App\Http\Controllers\swatour;

use App\Models\Gambarteo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;

class GambarteoController extends Controller
{
    public function index()
    {
        $gambar = Gambarteo::first();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.swatour.gambarteo', compact('gambar','userRole','layout'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
                'gambarType' => 'required|in:gambar1,gambar2',
            ]);

            $gambar = Gambarteo::firstOrNew();
            $gambarType = $request->input('gambarType');

            if ($request->hasFile('image')) {
                $imagePath = $this->compressAndSaveImage($request->file('image'));
                $gambar->$gambarType = $imagePath;
            }

            $gambar->save();

            return response()->json(['success' => true, 'message' => 'Image added successfully.']);
        } catch (\Exception $e) {
            Log::error('Error saving image: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
                'gambarType' => 'required|in:gambar1,gambar2',
            ]);

            $gambar = Gambarteo::findOrFail($id);
            $gambarType = $request->input('gambarType');

            if ($request->hasFile('image')) {
                // Delete old image
                if ($gambar->$gambarType) {
                    Storage::delete('public/' . $gambar->$gambarType);
                }
                
                $imagePath = $this->compressAndSaveImage($request->file('image'));
                $gambar->$gambarType = $imagePath;
            }

            $gambar->save();

            return response()->json(['success' => true, 'message' => 'Image updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error updating image: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $request->validate([
                'gambarType' => 'required|in:gambar1,gambar2',
            ]);

            $gambar = Gambarteo::findOrFail($id);
            $gambarType = $request->input('gambarType');

            if ($gambar->$gambarType) {
                Storage::delete('public/' . $gambar->$gambarType);
                $gambar->$gambarType = null;
                $gambar->save();
            }

            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function compressAndSaveImage($file)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        $image->scaleDown(1200);

        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'gambarteo/' . $fileName;

        Storage::put('public/' . $path, $image->encode());

        return $path;
    }
}

