<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Get all categories with hierarchy
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::with(['children', 'products'])->active();

        // Get root categories only if requested
        if ($request->boolean('root_only')) {
            $query->root();
        }

        // Get categories with products count
        if ($request->boolean('with_product_count')) {
            $query->withCount(['products' => function ($query) {
                $query->active()->inStock();
            }]);
        }

        $categories = $query->orderBy('sort_order')->get();

        // Build hierarchical structure
        $hierarchicalCategories = $this->buildHierarchy($categories);

        return response()->json([
            'success' => true,
            'data' => $hierarchicalCategories
        ]);
    }

    /**
     * Get a specific category with its products
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $category = Category::with(['children', 'parent'])->where('slug', $slug)->active()->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Get products in this category and its subcategories
        $productsQuery = Product::with(['category', 'primaryImage', 'approvedReviews'])
            ->where('category_id', $category->id)
            ->active()
            ->inStock();

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['name', 'price', 'created_at', 'average_rating'];
        if (in_array($sortBy, $allowedSortFields)) {
            if ($sortBy === 'average_rating') {
                $productsQuery->withAvg('approvedReviews', 'rating')->orderBy('approved_reviews_avg_rating', $sortOrder);
            } else {
                $productsQuery->orderBy($sortBy, $sortOrder);
            }
        } else {
            $productsQuery->orderBy('created_at', 'desc');
        }

        $perPage = min($request->get('per_page', 12), 50);
        $products = $productsQuery->paginate($perPage);

        // Add computed attributes to products
        $products->getCollection()->transform(function ($product) {
            $product->current_price = $product->current_price;
            $product->discount_percentage = $product->discount_percentage;
            $product->average_rating = $product->average_rating;
            $product->review_count = $product->review_count;
            return $product;
        });

        // Get breadcrumb navigation
        $breadcrumbs = $this->getBreadcrumbs($category);

        // Get subcategories with product counts
        $subcategories = $category->children()
            ->withCount(['products' => function ($query) {
                $query->active()->inStock();
            }])
            ->active()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image,
                    'full_path' => $category->full_path,
                    'level' => $category->level,
                    'has_children' => $category->hasChildren(),
                    'is_leaf' => $category->isLeaf(),
                ],
                'breadcrumbs' => $breadcrumbs,
                'subcategories' => $subcategories,
                'products' => $products,
                'filters' => [
                    'applied' => $request->only(['sort_by', 'sort_order']),
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
            ]
        ]);
    }

    /**
     * Get category tree for navigation
     */
    public function tree(): JsonResponse
    {
        $categories = Category::with(['children'])
            ->active()
            ->root()
            ->orderBy('sort_order')
            ->get();

        $tree = $this->buildHierarchy($categories);

        return response()->json([
            'success' => true,
            'data' => $tree
        ]);
    }

    /**
     * Get category breadcrumbs
     */
    public function breadcrumbs(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->active()->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $breadcrumbs = $this->getBreadcrumbs($category);

        return response()->json([
            'success' => true,
            'data' => $breadcrumbs
        ]);
    }

    /**
     * Search categories
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
        if (empty($search)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $categories = Category::where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->active()
            ->withCount(['products' => function ($query) {
                $query->active()->inStock();
            }])
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get popular categories (categories with most products)
     */
    public function popular(): JsonResponse
    {
        $categories = Category::withCount(['products' => function ($query) {
                $query->active()->inStock();
            }])
            ->active()
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Build hierarchical category structure
     */
    private function buildHierarchy($categories, $parentId = null): array
    {
        $hierarchy = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $categoryData = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image,
                    'level' => $category->level,
                    'has_children' => $category->hasChildren(),
                    'is_leaf' => $category->isLeaf(),
                ];

                // Add product count if available
                if (isset($category->products_count)) {
                    $categoryData['products_count'] = $category->products_count;
                }

                // Add full path if available
                if (isset($category->full_path)) {
                    $categoryData['full_path'] = $category->full_path;
                }

                // Recursively build children
                $children = $this->buildHierarchy($categories, $category->id);
                if (!empty($children)) {
                    $categoryData['children'] = $children;
                }

                $hierarchy[] = $categoryData;
            }
        }

        return $hierarchy;
    }

    /**
     * Get breadcrumb navigation for a category
     */
    private function getBreadcrumbs(Category $category): array
    {
        $breadcrumbs = [
            [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'is_current' => true
            ]
        ];

        $parent = $category->parent;
        while ($parent) {
            array_unshift($breadcrumbs, [
                'id' => $parent->id,
                'name' => $parent->name,
                'slug' => $parent->slug,
                'is_current' => false
            ]);
            $parent = $parent->parent;
        }

        // Add home breadcrumb
        array_unshift($breadcrumbs, [
            'id' => null,
            'name' => 'Home',
            'slug' => '',
            'is_current' => false
        ]);

        return $breadcrumbs;
    }
} 