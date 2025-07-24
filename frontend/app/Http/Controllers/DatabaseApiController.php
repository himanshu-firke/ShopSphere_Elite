<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DatabaseApiController extends Controller
{
    /**
     * Get products from database with relationships
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['category', 'images'])
                ->where('is_active', true);

            // Apply filters
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('featured') && $request->featured) {
                $query->where('is_featured', true);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 12);
            $products = $query->paginate($perPage);

            // Transform data to match frontend interface
            $transformedProducts = $products->getCollection()->map(function ($product) {
                return $this->transformProduct($product);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProducts,
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single product from database
     */
    public function getProduct($id): JsonResponse
    {
        try {
            $product = Product::with(['category', 'images'])
                ->where('is_active', true)
                ->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformProduct($product)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured products from database
     */
    public function getFeaturedProducts(): JsonResponse
    {
        try {
            $products = Product::with(['category', 'images'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();

            $transformedProducts = $products->map(function ($product) {
                return $this->transformProduct($product);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProducts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching featured products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories from database
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Category::where('is_active', true)
                ->withCount(['products' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('name')
                ->get();

            $transformedCategories = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image,
                    'status' => $category->is_active ? 'active' : 'inactive',
                    'products_count' => $category->products_count,
                    'created_at' => $category->created_at->toISOString(),
                    'updated_at' => $category->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedCategories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to cart in database
     */
    public function addToCart(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $productId = $request->product_id;
            $quantity = $request->quantity;

            // Get product
            $product = Product::find($productId);
            if (!$product || !$product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not available'
                ], 404);
            }

            // Check stock
            if ($product->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock'
                ], 400);
            }

            // Get or create cart (using session ID for now)
            $sessionId = session()->getId();
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId],
                [
                    'user_id' => auth()->id(),
                    'session_id' => $sessionId,
                    'status' => 'active'
                ]
            );

            // Check if item already exists in cart
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                if ($newQuantity > $product->stock_quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot add more items than available in stock'
                    ], 400);
                }
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->sale_price ?? $product->price
                ]);
            }

            // Refresh cart with items
            $cart = $this->getCartWithItems($cart->id);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => [
                    'cart' => $this->transformCart($cart)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding to cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cart contents from database
     */
    public function getCart(): JsonResponse
    {
        try {
            $sessionId = session()->getId();
            $cart = Cart::where('session_id', $sessionId)
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                // Return empty cart
                return response()->json([
                    'success' => true,
                    'data' => [
                        'cart' => [
                            'items' => [],
                            'item_count' => 0,
                            'subtotal' => 0,
                            'tax' => 0,
                            'total' => 0
                        ]
                    ]
                ]);
            }

            $cart = $this->getCartWithItems($cart->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'cart' => $this->transformCart($cart)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check for database API
     */
    public function health(): JsonResponse
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            
            // Get counts
            $productCount = Product::where('is_active', true)->count();
            $categoryCount = Category::where('is_active', true)->count();
            $cartCount = Cart::where('status', 'active')->count();

            return response()->json([
                'success' => true,
                'message' => 'Database API is working perfectly!',
                'timestamp' => now()->toISOString(),
                'database_status' => 'Connected to MySQL',
                'data_counts' => [
                    'products' => $productCount,
                    'categories' => $categoryCount,
                    'active_carts' => $cartCount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform product for frontend
     */
    private function transformProduct($product)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => (float) $product->price,
            'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
            'sku' => $product->sku,
            'stock_quantity' => $product->stock_quantity,
            'category_id' => $product->category_id,
            'is_featured' => (bool) $product->is_featured,
            'status' => $product->is_active ? 'active' : 'inactive',
            'reviews_count' => $product->reviews_count ?? 0,
            'average_rating' => $product->average_rating ?? 0,
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->image_path,
                    'alt_text' => $image->alt_text ?? $product->name,
                    'is_primary' => (bool) $image->is_primary
                ];
            }),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug
            ] : null,
            'created_at' => $product->created_at->toISOString(),
            'updated_at' => $product->updated_at->toISOString(),
        ];
    }

    /**
     * Transform cart for frontend
     */
    private function transformCart($cart)
    {
        $items = $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
                'image' => $item->product->images->where('is_primary', true)->first()?->image_path ?? '',
                'maxQuantity' => $item->product->stock_quantity,
                'total' => (float) ($item->price * $item->quantity)
            ];
        });

        $subtotal = $items->sum('total');
        $tax = $subtotal * 0.18; // 18% tax
        $total = $subtotal + $tax;

        return [
            'id' => $cart->id,
            'items' => $items,
            'item_count' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ];
    }

    /**
     * Get cart with items and products
     */
    private function getCartWithItems($cartId)
    {
        return Cart::with(['items.product.images'])
            ->find($cartId);
    }
}
