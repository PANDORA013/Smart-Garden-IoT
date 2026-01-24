<?php

namespace App\Http\Controllers\Swasegar;

use App\Models\SwasegarCarousel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;

class SwasegarCarouselController extends Controller
{
    public function index()
    {
        $carousels = SwasegarCarousel::all();
        $userRole = auth()->user()->role;
        $layout = $userRole === 'admin' ? 'layouts.app' : 'layouts.ppa';
        return view('admin.swasegar.carousel', compact('carousels', 'userRole', 'layout'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'nullable|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
                'description' => 'nullable|string',
            ]);

            $imagePath = $this->compressAndSaveImage($request->file('image'));

            $carousel = SwasegarCarousel::create([
                'title' => $request->title,
                'image' => $imagePath,
                'description' => $request->description,
            ]);

            return response()->json(['success' => true, 'message' => 'Carousel item added successfully!', 'carousel' => $carousel]);
        } catch (\Exception $e) {
            Log::error('Error saving carousel item: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $carousel = SwasegarCarousel::findOrFail($id);

            $validatedData = $request->validate([
                'title' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
                'description' => 'nullable|string',
            ]);

            if ($request->hasFile('image')) {
                if ($carousel->image) {
                    Storage::delete('public/' . $carousel->image);
                }
                $imagePath = $this->compressAndSaveImage($request->file('image'));
                $carousel->image = $imagePath;
            }

            $carousel->title = $request->title;
            $carousel->description = $request->description;
            $carousel->save();

            return response()->json(['success' => true, 'message' => 'Carousel item updated successfully!', 'carousel' => $carousel]);
        } catch (\Exception $e) {
            Log::error('Error updating carousel item: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $carousel = SwasegarCarousel::findOrFail($id);
            if ($carousel->image) {
                Storage::delete('public/' . $carousel->image);
            }
            $carousel->delete();

            return response()->json(['success' => true, 'message' => 'Carousel item deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Error deleting carousel item: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function compressAndSaveImage($file)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        $image->scaleDown(1200);

        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'swasegarCarousel/' . $fileName;

        Storage::put('public/' . $path, $image->encode());

        return $path;
    }
}

