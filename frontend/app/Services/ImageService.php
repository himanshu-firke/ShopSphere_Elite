<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Image sizes configuration
     */
    private const IMAGE_SIZES = [
        'thumbnail' => [
            'width' => 150,
            'height' => 150,
            'quality' => 80
        ],
        'medium' => [
            'width' => 400,
            'height' => 400,
            'quality' => 85
        ],
        'large' => [
            'width' => 800,
            'height' => 800,
            'quality' => 90
        ]
    ];

    /**
     * Store product image with multiple sizes
     */
    public function storeProductImage(UploadedFile $file): array
    {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $paths = [];

        // Store original image
        $originalPath = $file->storeAs('products/original', $filename, 'public');
        $paths['original'] = $originalPath;

        // Create and store resized versions
        $image = Image::make($file);

        foreach (self::IMAGE_SIZES as $size => $dimensions) {
            $resized = clone $image;
            
            // Resize maintaining aspect ratio
            $resized->resize($dimensions['width'], $dimensions['height'], function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Save resized image
            $resizedPath = "products/{$size}/{$filename}";
            Storage::disk('public')->put(
                $resizedPath,
                $resized->encode(null, $dimensions['quality'])
            );
            
            $paths[$size] = $resizedPath;
        }

        return $paths;
    }

    /**
     * Delete product images in all sizes
     */
    public function deleteProductImage(string $filename): void
    {
        // Delete original
        Storage::disk('public')->delete("products/original/{$filename}");

        // Delete all sizes
        foreach (array_keys(self::IMAGE_SIZES) as $size) {
            Storage::disk('public')->delete("products/{$size}/{$filename}");
        }
    }

    /**
     * Get image URLs for all sizes
     */
    public function getImageUrls(string $filename): array
    {
        $urls = [
            'original' => Storage::url("products/original/{$filename}")
        ];

        foreach (array_keys(self::IMAGE_SIZES) as $size) {
            $urls[$size] = Storage::url("products/{$size}/{$filename}");
        }

        return $urls;
    }
} 