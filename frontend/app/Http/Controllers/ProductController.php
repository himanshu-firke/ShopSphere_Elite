<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Get products with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'primaryImage', 'approvedReviews'])
            ->active()
            ->inStock();

        // Apply category filter
        if ($request->has('category_id')) {
            $categoryId = $request->category_id;
            $category = Category::find($categoryId);
            
            if ($category) {
                // Get all descendant category IDs
                $categoryIds = $this->getCategoryAndDescendantIds($categoryId);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->search($search);
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
        
        $allowedSortFields = ['name', 'price', 'created_at', 'average_rating'];
        if (in_array($sortBy, $allowedSortFields)) {
            if ($sortBy === 'average_rating') {
                $query->withAvg('approvedReviews', 'rating')->orderBy('approved_reviews_avg_rating', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Apply special filters
        if ($request->boolean('featured')) {
            $query->featured();
        }
        if ($request->boolean('bestseller')) {
            $query->bestseller();
        }
        if ($request->boolean('new')) {
            $query->new();
        }
        if ($request->boolean('on_sale')) {
            $query->onSale();
        }

        $perPage = min($request->get('per_page', 12), 50); // Max 50 items per page
        $products = $query->paginate($perPage);

        // Add computed attributes to each product
        $products->getCollection()->transform(function ($product) {
            $product->current_price = $product->current_price;
            $product->discount_percentage = $product->discount_percentage;
            $product->average_rating = $product->average_rating;
            $product->review_count = $product->review_count;
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => $products,
            'filters' => [
                'applied' => $request->only(['category_id', 'search', 'min_price', 'max_price', 'sort_by', 'sort_order', 'featured', 'bestseller', 'new', 'on_sale']),
                'available' => [
                    'sort_options' => [
                        ['value' => 'name', 'label' => 'Name'],
                        ['value' => 'price', 'label' => 'Price'],
                        ['value' => 'created_at', 'label' => 'Newest'],
                        ['value' => 'average_rating', 'label' => 'Rating'],
                    ],
                    'sort_orders' => [
                        ['value' => 'asc', 'label' => 'Ascending'],
                        ['value' => 'desc', 'label' => 'Descending'],
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get a specific product with full details
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::with([
            'category',
            'images',
            'attributes',
            'approvedReviews.user',
            'vendor'
        ])->where('slug', $slug)->active()->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Add computed attributes
        $product->current_price = $product->current_price;
        $product->discount_percentage = $product->discount_percentage;
        $product->average_rating = $product->average_rating;
        $product->review_count = $product->review_count;

        // Get related products
        $relatedProducts = Product::with(['primaryImage', 'approvedReviews'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->inStock()
            ->limit(8)
            ->get()
            ->map(function ($relatedProduct) {
                $relatedProduct->current_price = $relatedProduct->current_price;
                $relatedProduct->average_rating = $relatedProduct->average_rating;
                return $relatedProduct;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'related_products' => $relatedProducts
            ]
        ]);
    }

    /**
     * Get featured products
     */
    public function featured(): JsonResponse
    {
        $products = Product::with(['category', 'primaryImage', 'approvedReviews'])
            ->featured()
            ->active()
            ->inStock()
            ->limit(8)
            ->get()
            ->map(function ($product) {
                $product->current_price = $product->current_price;
                $product->discount_percentage = $product->discount_percentage;
                $product->average_rating = $product->average_rating;
                return $product;
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get bestseller products
     */
    public function bestsellers(): JsonResponse
    {
        $products = Product::with(['category', 'primaryImage', 'approvedReviews'])
            ->bestseller()
            ->active()
            ->inStock()
            ->limit(8)
            ->get()
            ->map(function ($product) {
                $product->current_price = $product->current_price;
                $product->discount_percentage = $product->discount_percentage;
                $product->average_rating = $product->average_rating;
                return $product;
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get new products
     */
    public function newProducts(): JsonResponse
    {
        $products = Product::with(['category', 'primaryImage', 'approvedReviews'])
            ->new()
            ->active()
            ->inStock()
            ->limit(8)
            ->get()
            ->map(function ($product) {
                $product->current_price = $product->current_price;
                $product->discount_percentage = $product->discount_percentage;
                $product->average_rating = $product->average_rating;
                return $product;
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get products on sale
     */
    public function onSale(): JsonResponse
    {
        $products = Product::with(['category', 'primaryImage', 'approvedReviews'])
            ->onSale()
            ->active()
            ->inStock()
            ->limit(8)
            ->get()
            ->map(function ($product) {
                $product->current_price = $product->current_price;
                $product->discount_percentage = $product->discount_percentage;
                $product->average_rating = $product->average_rating;
                return $product;
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get category and all its descendant IDs
     */
    private function getCategoryAndDescendantIds(int $categoryId): array
    {
        $categoryIds = [$categoryId];
        
        $descendants = Category::where('parent_id', $categoryId)->get();
        foreach ($descendants as $descendant) {
            $categoryIds = array_merge($categoryIds, $this->getCategoryAndDescendantIds($descendant->id));
        }
        
        return $categoryIds;
    }
} 