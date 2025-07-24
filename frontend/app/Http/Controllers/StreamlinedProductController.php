<?php

namespace App\Http\Controllers;

use App\Models\StreamlinedProduct;
use App\Models\StreamlinedCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StreamlinedProductController extends Controller
{
    /**
     * Get products with pagination and filtering - matches frontend ProductService
     */
    public function index(Request $request): JsonResponse
    {
        $query = StreamlinedProduct::with(['category', 'images', 'primaryImage'])
            ->active()
            ->inStock();

        // Apply category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Apply price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['name', 'price', 'created_at', 'average_rating'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total()
        ]);
    }

    /**
     * Get single product by ID - matches frontend ProductService
     */
    public function show($id): JsonResponse
    {
        $product = StreamlinedProduct::with(['category', 'images'])
            ->active()
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product->toFrontendArray()
        ]);
    }

    /**
     * Get featured products - matches frontend ProductService
     */
    public function featured(): JsonResponse
    {
        $products = StreamlinedProduct::with(['category', 'images', 'primaryImage'])
            ->active()
            ->featured()
            ->inStock()
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return $product->toFrontendArray();
            })
        ]);
    }

    /**
     * Get bestseller products - matches frontend ProductService
     */
    public function bestsellers(): JsonResponse
    {
        $products = StreamlinedProduct::with(['category', 'images', 'primaryImage'])
            ->active()
            ->inStock()
            ->orderBy('reviews_count', 'desc')
            ->orderBy('average_rating', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return $product->toFrontendArray();
            })
        ]);
    }

    /**
     * Get related products - matches frontend ProductService
     */
    public function related($id): JsonResponse
    {
        $product = StreamlinedProduct::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $relatedProducts = StreamlinedProduct::with(['category', 'images', 'primaryImage'])
            ->active()
            ->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $relatedProducts->map(function ($product) {
                return $product->toFrontendArray();
            })
        ]);
    }

    /**
     * Search products - matches frontend ProductService
     */
    public function search(Request $request): JsonResponse
    {
        $query = StreamlinedProduct::with(['category', 'images', 'primaryImage'])
            ->active()
            ->inStock();

        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Apply additional filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['name', 'price', 'created_at', 'average_rating'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total()
        ]);
    }
}
