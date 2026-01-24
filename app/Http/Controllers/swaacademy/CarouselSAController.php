<?php

namespace App\Http\Controllers\swaacademy;

use App\Http\Controllers\Controller;
use App\Models\CarouselSA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;

class CarouselSAController extends Controller
{
    public function index()
    {
        $carousels = CarouselSA::all();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.swaacademy.carouselSA', compact('carousels', 'userRole', 'layout'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
            ]);

            $imagePath = $this->compressAndSaveImage($request->file('image'));
            
            Log::info('Image saved at: ' . $imagePath);

            CarouselSA::create([
                'image' => $imagePath,
            ]);

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
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
            ]);

            $carousel = CarouselSA::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($carousel->image) {
                    Storage::delete('public/' . $carousel->image);
                }
                
                $imagePath = $this->compressAndSaveImage($request->file('image'));
                $carousel->image = $imagePath;
            }

            $carousel->save();

            return response()->json(['success' => true, 'message' => 'Image updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $carousel = CarouselSA::findOrFail($id);
        if ($carousel->image) {
            Storage::delete('public/' . $carousel->image);
        }
        $carousel->delete();

        return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
    }

    private function compressAndSaveImage($file)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        $image->scaleDown(1200);

        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'carousels/swaacademy/' . $fileName;

        Storage::put('public/' . $path, $image->encode());

        return $path;
    }
}