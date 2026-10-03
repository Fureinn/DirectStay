<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UnitManagementController extends Controller
{
    /**
     * Display list of units for host management.
     */
    public function index(): View
    {
        $units = Unit::with(['building', 'blockedDates'])->latest()->get();

        return view('host.units.index', [
            'units' => $units,
        ]);
    }

    /**
     * Show form to create a new unit.
     */
    public function create(): View
    {
        $buildings = Building::all();

        return view('host.units.create', [
            'buildings' => $buildings,
        ]);
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'unit_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'base_price_per_night' => ['required', 'numeric', 'min:0', 'max:100000'],
            'advance_deposit_required' => ['required', 'numeric', 'min:0', 'max:100000'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string', 'max:5000'],
            'inventory_items' => ['nullable', 'string'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $inventoryArray = [];
        if (! empty($validated['inventory_items'])) {
            $inventoryArray = array_values(array_filter(array_map('trim', explode(',', $validated['inventory_items']))));
        }

        $unit = Unit::create([
            'building_id' => $validated['building_id'],
            'user_id' => auth()->id(),
            'unit_number' => $validated['unit_number'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'base_price_per_night' => $validated['base_price_per_night'],
            'advance_deposit_required' => $validated['advance_deposit_required'],
            'max_guests' => $validated['max_guests'],
            'inventory_items' => $inventoryArray,
            'is_active' => true,
        ]);

        $uploadedImages = [];
        $destinationPath = public_path('images/units/'.$unit->id);
        if (! File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        if ($request->hasFile('cover_photo')) {
            $cover = $request->file('cover_photo');
            $filename = 'cover_'.$unit->id.'_'.Str::random(8).'.'.$cover->getClientOriginalExtension();
            $cover->move($destinationPath, $filename);
            $coverPath = 'images/units/'.$unit->id.'/'.$filename;
            $uploadedImages[] = $coverPath;
            $unit->cover_image = $coverPath;
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = 'unit_'.$unit->id.'_'.Str::random(8).'.'.$photo->getClientOriginalExtension();
                $photo->move($destinationPath, $filename);
                $uploadedImages[] = 'images/units/'.$unit->id.'/'.$filename;
            }
        }

        if (! empty($uploadedImages)) {
            $unit->images = $uploadedImages;
            if (! $unit->cover_image) {
                $unit->cover_image = $uploadedImages[0];
            }
        } else {
            // Default placeholder image
            $unit->cover_image = 'images/units/unit_n412.jpg';
            $unit->images = ['images/units/unit_n412.jpg'];
        }

        $unit->save();

        return redirect()->route('host.units.index')->with('success', "Unit {$unit->unit_number} ({$unit->title}) created successfully!");
    }

    /**
     * Show edit form and photo manager for a unit.
     */
    public function edit(Unit $unit): View
    {
        $unit->load(['building', 'blockedDates' => fn ($q) => $q->latest()]);
        $buildings = Building::all();

        return view('host.units.edit', [
            'unit' => $unit,
            'buildings' => $buildings,
        ]);
    }

    /**
     * Update unit details.
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'unit_number' => ['required', 'string', 'max:50'],
            'base_price_per_night' => ['required', 'numeric', 'min:0', 'max:100000'],
            'advance_deposit_required' => ['required', 'numeric', 'min:0', 'max:100000'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $unit->update($validated);

        return back()->with('success', 'Unit details updated successfully!');
    }

    /**
     * Upload new photos for the unit.
     */
    public function uploadPhotos(Request $request, Unit $unit): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $destinationPath = public_path('images/units/'.$unit->id);
        if (! File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        $currentImages = $unit->images ?? [];

        foreach ($request->file('photos') as $photo) {
            $filename = 'unit_'.$unit->id.'_'.Str::random(8).'.'.$photo->getClientOriginalExtension();
            $photo->move($destinationPath, $filename);
            $webPath = 'images/units/'.$unit->id.'/'.$filename;
            $currentImages[] = $webPath;
        }

        // If cover_image is not set, set it to the first uploaded photo
        $coverImage = $unit->cover_image ?: $currentImages[0];

        $unit->update([
            'cover_image' => $coverImage,
            'images' => $currentImages,
        ]);

        return back()->with('success', count($request->file('photos')).' photo(s) uploaded successfully!');
    }

    /**
     * Set a photo as the primary cover image.
     */
    public function setCover(Request $request, Unit $unit): RedirectResponse
    {
        $image = $request->input('image_path') ?? $request->input('image');

        if (! $image || ! in_array($image, $unit->images ?? [], true)) {
            return back()->withErrors(['image' => 'The selected photo does not belong to this unit.']);
        }

        $unit->update([
            'cover_image' => $image,
        ]);

        return back()->with('success', 'Cover photo updated!');
    }

    /**
     * Delete a photo from the unit gallery.
     */
    public function deletePhoto(Request $request, Unit $unit): RedirectResponse
    {
        $target = $request->input('image_path') ?? $request->input('image');

        if (! $target || ! in_array($target, $unit->images ?? [], true)) {
            return back()->withErrors(['image' => 'The specified photo is invalid or does not belong to this unit.']);
        }

        $currentImages = $unit->images ?? [];
        $updatedImages = array_values(array_filter($currentImages, fn ($img) => $img !== $target));

        // Delete file on disk if exists and safely within unit's image folder
        if (str_starts_with($target, 'images/units/'.$unit->id.'/')) {
            $filePath = public_path($target);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $cover = $unit->cover_image;
        if ($cover === $target) {
            $cover = count($updatedImages) > 0 ? $updatedImages[0] : null;
        }

        $unit->update([
            'cover_image' => $cover,
            'images' => $updatedImages,
        ]);

        return back()->with('success', 'Photo deleted successfully!');
    }
}
