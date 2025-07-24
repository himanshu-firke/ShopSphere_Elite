<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Advanced search with multiple filters
     */
    public function search(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'primaryImage', 'approvedReviews', 'attributes'])
            ->active()
            ->inStock();

        // Text search across multiple fields
        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('short_description', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%")
                  ->orWhere('tags', 'like', "%{$searchTerm}%")
                  ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                      $categoryQuery->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('attributes', function ($attrQuery) use ($searchTerm) {
                      $attrQuery->where('name', 'like', "%{$searchTerm}%")
                               ->orWhere('value', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Category filtering
        if ($request->has('category_id')) {
            $categoryId = $request->category_id;
            $category = Category::find($categoryId);
            
            if ($category) {
                // Get all descendant category IDs
                $categoryIds = $this->getCategoryAndDescendantIds($categoryId);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Price range filtering
        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        // Rating filtering
        if ($request->has('min_rating') && is_numeric($request->min_rating)) {
            $query->withAvg('approvedReviews', 'rating')
                  ->having('approved_reviews_avg_rating', '>=', $request->min_rating);
        }

        // Stock availability filtering
        if ($request->has('in_stock')) {
            if ($request->boolean('in_stock')) {
                $query->where('stock_quantity', '>', 0);
            } else {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        // Special filters
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

        // Attribute-based filtering
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attributeName => $attributeValue) {
                if (!empty($attributeValue)) {
                    $query->whereHas('attributes', function ($attrQuery) use ($attributeName, $attributeValue) {
                        $attrQuery->where('name', $attributeName)
                                 ->where('value', 'like', "%{$attributeValue}%");
                    });
                }
            }
        }

        // Vendor filtering
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Weight range filtering
        if ($request->has('min_weight') && is_numeric($request->min_weight)) {
            $query->where('weight', '>=', $request->min_weight);
        }
        if ($request->has('max_weight') && is_numeric($request->max_weight)) {
            $query->where('weight', '<=', $request->max_weight);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'relevance');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $this->applySorting($query, $sortBy, $sortOrder, $request->get('q'));

        // Pagination
        $perPage = min($request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        // Add computed attributes to each product
        $products->getCollection()->transform(function ($product) {
            $product->current_price = $product->current_price;
            $product->discount_percentage = $product->discount_percentage;
            $product->average_rating = $product->average_rating;
            $product->review_count = $product->review_count;
            return $product;
        });

        // Get search suggestions
        $suggestions = $this->getSearchSuggestions($request->get('q', ''));

        // Get available filters
        $availableFilters = $this->getAvailableFilters($request);

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'suggestions' => $suggestions,
                'filters' => [
                    'applied' => $request->only([
                        'q', 'category_id', 'min_price', 'max_price', 'min_rating',
                        'in_stock', 'featured', 'bestseller', 'new', 'on_sale',
                        'attributes', 'vendor_id', 'min_weight', 'max_weight',
                        'sort_by', 'sort_order'
                    ]),
                    'available' => $availableFilters
                ]
            ]
        ]);
    }

    /**
     * Get search suggestions based on query
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $suggestions = $this->getSearchSuggestions($query);

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Get search filters and options
     */
    public function filters(Request $request): JsonResponse
    {
        $availableFilters = $this->getAvailableFilters($request);

        return response()->json([
            'success' => true,
            'data' => $availableFilters
        ]);
    }

    /**
     * Get search analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        // Get search result count
        $resultCount = Product::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            })
            ->count();

        // Get category distribution
        $categoryDistribution = Product::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            })
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($products) {
                return $products->count();
            })
            ->sortDesc();

        // Get price range
        $priceRange = Product::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $query,
                'result_count' => $resultCount,
                'category_distribution' => $categoryDistribution,
                'price_range' => [
                    'min' => $priceRange->min_price ?? 0,
                    'max' => $priceRange->max_price ?? 0,
                ]
            ]
        ]);
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting($query, string $sortBy, string $sortOrder, ?string $searchTerm = null): void
    {
        switch ($sortBy) {
            case 'relevance':
                if ($searchTerm) {
                    // Sort by relevance (exact matches first, then partial matches)
                    $query->orderByRaw("
                        CASE 
                            WHEN name LIKE ? THEN 1
                            WHEN name LIKE ? THEN 2
                            WHEN description LIKE ? THEN 3
                            ELSE 4
                        END", ["{$searchTerm}", "%{$searchTerm}%", "%{$searchTerm}%"])
                        ->orderBy('name', 'asc');
                } else {
                    $query->orderBy('created_at', 'desc');
                }
                break;
                
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
                
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
                
            case 'rating':
                $query->withAvg('approvedReviews', 'rating')
                      ->orderBy('approved_reviews_avg_rating', $sortOrder);
                break;
                
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
                
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
                
            case 'popularity':
                $query->withCount('approvedReviews')
                      ->orderBy('approved_reviews_count', 'desc');
                break;
                
            default:
                $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get search suggestions
     */
    private function getSearchSuggestions(string $query): array
    {
        $suggestions = [];

        // Product name suggestions
        $productNames = Product::active()
            ->where('name', 'like', "%{$query}%")
            ->select('name')
            ->distinct()
            ->limit(5)
            ->pluck('name')
            ->toArray();

        $suggestions['products'] = $productNames;

        // Category suggestions
        $categories = Category::active()
            ->where('name', 'like', "%{$query}%")
            ->select('name')
            ->distinct()
            ->limit(3)
            ->pluck('name')
            ->toArray();

        $suggestions['categories'] = $categories;

        // Popular search terms (based on tags)
        $tags = Product::active()
            ->where('tags', 'like', "%{$query}%")
            ->pluck('tags')
            ->flatten()
            ->filter(function ($tag) use ($query) {
                return stripos($tag, $query) !== false;
            })
            ->unique()
            ->take(3)
            ->values()
            ->toArray();

        $suggestions['tags'] = $tags;

        return $suggestions;
    }

    /**
     * Get available filters
     */
    private function getAvailableFilters(Request $request): array
    {
        $filters = [];

        // Categories
        $filters['categories'] = Category::active()
            ->withCount(['products' => function ($query) {
                $query->active()->inStock();
            }])
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'products_count']);

        // Price ranges
        $priceStats = Product::active()->inStock()->selectRaw('
            MIN(price) as min_price,
            MAX(price) as max_price,
            AVG(price) as avg_price
        ')->first();

        $filters['price_ranges'] = [
            'min' => $priceStats->min_price ?? 0,
            'max' => $priceStats->max_price ?? 1000,
            'avg' => $priceStats->avg_price ?? 0,
            'ranges' => [
                ['min' => 0, 'max' => 50, 'label' => 'Under $50'],
                ['min' => 50, 'max' => 100, 'label' => '$50 - $100'],
                ['min' => 100, 'max' => 250, 'label' => '$100 - $250'],
                ['min' => 250, 'max' => 500, 'label' => '$250 - $500'],
                ['min' => 500, 'max' => null, 'label' => 'Over $500'],
            ]
        ];

        // Ratings
        $filters['ratings'] = [
            ['value' => 5, 'label' => '5 Stars'],
            ['value' => 4, 'label' => '4+ Stars'],
            ['value' => 3, 'label' => '3+ Stars'],
            ['value' => 2, 'label' => '2+ Stars'],
            ['value' => 1, 'label' => '1+ Stars'],
        ];

        // Sort options
        $filters['sort_options'] = [
            ['value' => 'relevance', 'label' => 'Relevance'],
            ['value' => 'name', 'label' => 'Name'],
            ['value' => 'price', 'label' => 'Price'],
            ['value' => 'rating', 'label' => 'Rating'],
            ['value' => 'newest', 'label' => 'Newest'],
            ['value' => 'oldest', 'label' => 'Oldest'],
            ['value' => 'popularity', 'label' => 'Popularity'],
        ];

        // Special filters
        $filters['special_filters'] = [
            ['key' => 'featured', 'label' => 'Featured'],
            ['key' => 'bestseller', 'label' => 'Bestsellers'],
            ['key' => 'new', 'label' => 'New Arrivals'],
            ['key' => 'on_sale', 'label' => 'On Sale'],
            ['key' => 'in_stock', 'label' => 'In Stock'],
        ];

        return $filters;
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