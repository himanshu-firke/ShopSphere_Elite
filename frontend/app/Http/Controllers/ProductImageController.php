<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Upload product images
     */
    public function upload(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'is_primary' => 'sometimes|boolean',
            'alt_text' => 'sometimes|string|max:255',
            'caption' => 'sometimes|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $uploadedImages = [];
            foreach ($request->file('images') as $index => $image) {
                // Store image in multiple sizes
                $paths = $this->imageService->storeProductImage($image);
                
                // Create image record
                $productImage = $product->images()->create([
                    'image_path' => $paths['original'],
                    'alt_text' => $request->input("alt_text.{$index}") ?? $product->name,
                    'caption' => $request->input("caption.{$index}"),
                    'sort_order' => $product->images()->max('sort_order') + 1,
                    'is_primary' => $request->boolean('is_primary') && $index === 0,
                    'is_active' => true,
                ]);

                // If this is set as primary, update other images
                if ($productImage->is_primary) {
                    $product->images()
                        ->where('id', '!=', $productImage->id)
                        ->update(['is_primary' => false]);
                }

                // Add URLs for all sizes
                $productImage->urls = $this->imageService->getImageUrls(basename($paths['original']));
                $uploadedImages[] = $productImage;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => [
                    'images' => $uploadedImages
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update image details
     */
    public function update(Request $request, ProductImage $image): JsonResponse
    {
        $request->validate([
            'alt_text' => 'sometimes|string|max:255',
            'caption' => 'sometimes|string|max:500',
            'sort_order' => 'sometimes|integer|min:0',
            'is_primary' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            $image->update($request->only([
                'alt_text',
                'caption',
                'sort_order',
                'is_active'
            ]));

            // Handle primary image status
            if ($request->has('is_primary') && $request->boolean('is_primary')) {
                $image->product->images()
                    ->where('id', '!=', $image->id)
                    ->update(['is_primary' => false]);
                
                $image->is_primary = true;
                $image->save();
            }

            DB::commit();

            // Add URLs for all sizes
            $image->urls = $this->imageService->getImageUrls(basename($image->image_path));

            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'data' => [
                    'image' => $image
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product image
     */
    public function destroy(ProductImage $image): JsonResponse
    {
        try {
            DB::beginTransaction();

            // If this was the primary image, make the next available image primary
            if ($image->is_primary) {
                $nextImage = $image->product->images()
                    ->where('id', '!=', $image->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->first();

                if ($nextImage) {
                    $nextImage->update(['is_primary' => true]);
                }
            }

            // Delete image files
            $this->imageService->deleteProductImage(basename($image->image_path));

            // Delete database record
            $image->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder product images
     */
    public function reorder(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:product_images,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->order as $index => $imageId) {
                ProductImage::where('id', $imageId)
                    ->where('product_id', $product->id)
                    ->update(['sort_order' => $index]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder images',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 