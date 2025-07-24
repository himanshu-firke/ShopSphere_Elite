<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\ImageService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductManagementController extends Controller
{
    protected $imageService;
    protected $inventoryService;

    public function __construct(
        ImageService $imageService,
        InventoryService $inventoryService
    ) {
        $this->imageService = $imageService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get products with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'primaryImage', 'vendor'])
            ->withCount(['reviews', 'orderItems']);

        // Apply filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'low_stock':
                    $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                        ->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', 0);
                    break;
            }
        }

        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        if ($request->has('bestseller')) {
            $query->where('is_bestseller', $request->boolean('bestseller'));
        }

        if ($request->has('on_sale')) {
            $query->where('is_on_sale', $request->boolean('on_sale'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'sales':
                $query->orderBy('order_items_count', $sortOrder);
                break;
            case 'rating':
                $query->withAvg('approvedReviews', 'rating')
                    ->orderBy('approved_reviews_avg_rating', $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'filters' => [
                    'categories' => Category::select('id', 'name')->get(),
                    'status_options' => [
                        ['value' => 'active', 'label' => 'Active'],
                        ['value' => 'inactive', 'label' => 'Inactive'],
                        ['value' => 'low_stock', 'label' => 'Low Stock'],
                        ['value' => 'out_of_stock', 'label' => 'Out of Stock']
                    ],
                    'sort_options' => [
                        ['value' => 'created_at', 'label' => 'Date Added'],
                        ['value' => 'name', 'label' => 'Name'],
                        ['value' => 'price', 'label' => 'Price'],
                        ['value' => 'sales', 'label' => 'Sales'],
                        ['value' => 'rating', 'label' => 'Rating']
                    ]
                ]
            ]
        ]);
    }

    /**
     * Create a new product
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products',
            'barcode' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_new' => 'boolean',
            'is_on_sale' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
            'sale_start_date' => 'nullable|date',
            'sale_end_date' => 'nullable|date|after:sale_start_date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'tags' => 'nullable|array',
            'attributes' => 'nullable|array',
            'attributes.*.name' => 'required|string|max:255',
            'attributes.*.value' => 'required|string',
            'attributes.*.type' => 'required|string|in:text,number,boolean,currency,percentage',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create product
            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'short_description' => $request->short_description,
                'category_id' => $request->category_id,
                'vendor_id' => auth()->id(),
                'price' => $request->price,
                'compare_price' => $request->compare_price,
                'cost_price' => $request->cost_price,
                'sku' => $request->sku,
                'barcode' => $request->barcode,
                'weight' => $request->weight,
                'dimensions' => $request->dimensions,
                'stock_quantity' => $request->stock_quantity,
                'low_stock_threshold' => $request->low_stock_threshold,
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured'),
                'is_bestseller' => $request->boolean('is_bestseller'),
                'is_new' => $request->boolean('is_new'),
                'is_on_sale' => $request->boolean('is_on_sale'),
                'sale_price' => $request->sale_price,
                'sale_start_date' => $request->sale_start_date,
                'sale_end_date' => $request->sale_end_date,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'tags' => $request->tags
            ]);

            // Create attributes
            if ($request->has('attributes')) {
                foreach ($request->attributes as $index => $attribute) {
                    $product->attributes()->create([
                        'name' => $attribute['name'],
                        'value' => $attribute['value'],
                        'type' => $attribute['type'],
                        'sort_order' => $index,
                        'is_visible' => true,
                        'is_searchable' => true,
                        'is_filterable' => true
                    ]);
                }
            }

            // Handle images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $paths = $this->imageService->storeProductImage($image);
                    
                    $product->images()->create([
                        'image_path' => $paths['original'],
                        'alt_text' => $product->name,
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                        'is_active' => true
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => [
                    'product' => $product->load(['category', 'images', 'attributes'])
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'short_description' => 'sometimes|string|max:500',
            'category_id' => 'sometimes|exists:categories,id',
            'price' => 'sometimes|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'sometimes|string|max:100|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'stock_quantity' => 'sometimes|integer|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_new' => 'boolean',
            'is_on_sale' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
            'sale_start_date' => 'nullable|date',
            'sale_end_date' => 'nullable|date|after:sale_start_date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'tags' => 'nullable|array',
            'attributes' => 'nullable|array',
            'attributes.*.id' => 'sometimes|exists:product_attributes,id',
            'attributes.*.name' => 'required|string|max:255',
            'attributes.*.value' => 'required|string',
            'attributes.*.type' => 'required|string|in:text,number,boolean,currency,percentage',
            'new_images' => 'nullable|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'exists:product_images,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update product
            if ($request->has('name')) {
                $request->merge(['slug' => Str::slug($request->name)]);
            }

            $product->update($request->only([
                'name', 'slug', 'description', 'short_description', 'category_id',
                'price', 'compare_price', 'cost_price', 'sku', 'barcode',
                'weight', 'dimensions', 'stock_quantity', 'low_stock_threshold',
                'is_active', 'is_featured', 'is_bestseller', 'is_new',
                'is_on_sale', 'sale_price', 'sale_start_date', 'sale_end_date',
                'meta_title', 'meta_description', 'meta_keywords', 'tags'
            ]));

            // Update attributes
            if ($request->has('attributes')) {
                // Delete removed attributes
                $attributeIds = collect($request->attributes)
                    ->pluck('id')
                    ->filter()
                    ->toArray();
                $product->attributes()
                    ->whereNotIn('id', $attributeIds)
                    ->delete();

                // Update or create attributes
                foreach ($request->attributes as $index => $attribute) {
                    $product->attributes()->updateOrCreate(
                        ['id' => $attribute['id'] ?? null],
                        [
                            'name' => $attribute['name'],
                            'value' => $attribute['value'],
                            'type' => $attribute['type'],
                            'sort_order' => $index,
                            'is_visible' => true,
                            'is_searchable' => true,
                            'is_filterable' => true
                        ]
                    );
                }
            }

            // Handle image removals
            if ($request->has('remove_image_ids')) {
                foreach ($request->remove_image_ids as $imageId) {
                    $image = $product->images()->find($imageId);
                    if ($image) {
                        $this->imageService->deleteProductImage(basename($image->image_path));
                        $image->delete();
                    }
                }
            }

            // Handle new images
            if ($request->hasFile('new_images')) {
                $currentSortOrder = $product->images()->max('sort_order') ?? -1;

                foreach ($request->file('new_images') as $image) {
                    $paths = $this->imageService->storeProductImage($image);
                    
                    $currentSortOrder++;
                    $product->images()->create([
                        'image_path' => $paths['original'],
                        'alt_text' => $product->name,
                        'sort_order' => $currentSortOrder,
                        'is_primary' => !$product->primaryImage()->exists(),
                        'is_active' => true
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'product' => $product->fresh(['category', 'images', 'attributes'])
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a product
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Delete product images
            foreach ($product->images as $image) {
                $this->imageService->deleteProductImage(basename($image->image_path));
            }

            // Delete product and related data
            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update product status
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            Product::whereIn('id', $request->product_ids)
                ->update(['is_active' => $request->status]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->product_ids) . ' products updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
            'low_stock' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->where('stock_quantity', '>', 0)
                ->count(),
            'featured_products' => Product::where('is_featured', true)->count(),
            'bestsellers' => Product::where('is_bestseller', true)->count(),
            'on_sale' => Product::where('is_on_sale', true)->count(),
            'category_distribution' => $this->getCategoryDistribution(),
            'price_ranges' => $this->getPriceRangeDistribution(),
            'top_selling' => $this->getTopSellingProducts(),
            'top_rated' => $this->getTopRatedProducts()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get category distribution
     */
    private function getCategoryDistribution(): array
    {
        return Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->get()
            ->map(function ($category) {
                return [
                    'category' => $category->name,
                    'count' => $category->products_count
                ];
            })
            ->toArray();
    }

    /**
     * Get price range distribution
     */
    private function getPriceRangeDistribution(): array
    {
        $ranges = [
            ['min' => 0, 'max' => 50, 'label' => 'Under $50'],
            ['min' => 50, 'max' => 100, 'label' => '$50 - $100'],
            ['min' => 100, 'max' => 200, 'label' => '$100 - $200'],
            ['min' => 200, 'max' => 500, 'label' => '$200 - $500'],
            ['min' => 500, 'max' => null, 'label' => 'Over $500']
        ];

        $distribution = [];
        foreach ($ranges as $range) {
            $query = Product::where('price', '>=', $range['min']);
            if ($range['max']) {
                $query->where('price', '<', $range['max']);
            }
            
            $distribution[] = [
                'range' => $range['label'],
                'count' => $query->count()
            ];
        }

        return $distribution;
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts(int $limit = 10): array
    {
        return Product::withCount('orderItems')
            ->with(['category', 'primaryImage'])
            ->orderBy('order_items_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name,
                    'image' => $product->primaryImage?->url,
                    'sales_count' => $product->order_items_count
                ];
            })
            ->toArray();
    }

    /**
     * Get top rated products
     */
    private function getTopRatedProducts(int $limit = 10): array
    {
        return Product::withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->with(['category', 'primaryImage'])
            ->having('approved_reviews_count', '>=', 5)
            ->orderBy('approved_reviews_avg_rating', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name,
                    'image' => $product->primaryImage?->url,
                    'rating' => round($product->approved_reviews_avg_rating, 1),
                    'review_count' => $product->approved_reviews_count
                ];
            })
            ->toArray();
    }
} 