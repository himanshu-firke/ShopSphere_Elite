<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Get all banners
     */
    public function index(): JsonResponse
    {
        $banners = Banner::ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }

    /**
     * Store a new banner
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'required|image|max:2048', // 2MB max
            'link_url' => 'nullable|url|max:255',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'background_color' => 'nullable|string|max:7',
            'text_color' => 'nullable|string|max:7'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload
        $imagePath = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'image_path' => $imagePath,
            'link_url' => $request->link_url,
            'position' => $request->position ?? 0,
            'is_active' => $request->is_active ?? true,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'background_color' => $request->background_color,
            'text_color' => $request->text_color
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully',
            'data' => $banner
        ], 201);
    }

    /**
     * Update a banner
     */
    public function update(Request $request, Banner $banner): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048', // 2MB max
            'link_url' => 'nullable|url|max:255',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'background_color' => 'nullable|string|max:7',
            'text_color' => 'nullable|string|max:7'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload if new image is provided
        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $imagePath = $request->file('image')->store('banners', 'public');
            $banner->image_path = $imagePath;
        }

        $banner->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'link_url' => $request->link_url,
            'position' => $request->position ?? $banner->position,
            'is_active' => $request->is_active ?? $banner->is_active,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'background_color' => $request->background_color,
            'text_color' => $request->text_color
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'data' => $banner
        ]);
    }

    /**
     * Delete a banner
     */
    public function destroy(Banner $banner): JsonResponse
    {
        // Delete banner image
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully'
        ]);
    }

    /**
     * Update banner positions
     */
    public function updatePositions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:banners,id',
            'positions.*.position' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->positions as $position) {
            Banner::where('id', $position['id'])->update([
                'position' => $position['position']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Banner positions updated successfully'
        ]);
    }

    /**
     * Toggle banner active status
     */
    public function toggleActive(Banner $banner): JsonResponse
    {
        $banner->update([
            'is_active' => !$banner->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner status updated successfully',
            'data' => [
                'is_active' => $banner->is_active
            ]
        ]);
    }
} 