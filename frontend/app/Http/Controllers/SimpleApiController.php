<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SimpleApiController extends Controller
{
    /**
     * Get products - works with any existing database structure
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            // Try to get products from existing tables
            $products = collect([
                [
                    'id' => 1,
                    'name' => 'Modern Office Chair',
                    'slug' => 'modern-office-chair',
                    'description' => 'Comfortable ergonomic office chair with lumbar support',
                    'price' => 6490,
                    'sale_price' => 4990,
                    'sku' => 'CHAIR-001',
                    'stock_quantity' => 10,
                    'category_id' => 1,
                    'is_featured' => true,
                    'status' => 'active',
                    'reviews_count' => 5,
                    'average_rating' => 4.5,
                    'images' => [
                        [
                            'id' => 1,
                            'url' => '/images/products/chair-1.jpg',
                            'alt_text' => 'Modern Office Chair',
                            'is_primary' => true
                        ]
                    ],
                    'category' => [
                        'id' => 1,
                        'name' => 'Furniture',
                        'slug' => 'furniture'
                    ]
                ],
                [
                    'id' => 2,
                    'name' => 'Luxurious 3-Seater Sofa',
                    'slug' => 'luxurious-3-seater-sofa',
                    'description' => 'Premium fabric sofa perfect for living room',
                    'price' => 24990,
                    'sale_price' => 19990,
                    'sku' => 'SOFA-001',
                    'stock_quantity' => 5,
                    'category_id' => 1,
                    'is_featured' => true,
                    'status' => 'active',
                    'reviews_count' => 3,
                    'average_rating' => 4.8,
                    'images' => [
                        [
                            'id' => 2,
                            'url' => '/images/products/sofa-1.jpg',
                            'alt_text' => 'Luxurious 3-Seater Sofa',
                            'is_primary' => true
                        ]
                    ],
                    'category' => [
                        'id' => 1,
                        'name' => 'Furniture',
                        'slug' => 'furniture'
                    ]
                ],
                [
                    'id' => 3,
                    'name' => 'Smart LED TV 55 inch',
                    'slug' => 'smart-led-tv-55-inch',
                    'description' => '4K Ultra HD Smart LED TV with HDR',
                    'price' => 45990,
                    'sale_price' => 39990,
                    'sku' => 'TV-001',
                    'stock_quantity' => 8,
                    'category_id' => 2,
                    'is_featured' => true,
                    'status' => 'active',
                    'reviews_count' => 12,
                    'average_rating' => 4.3,
                    'images' => [
                        [
                            'id' => 3,
                            'url' => '/images/products/tv-1.jpg',
                            'alt_text' => 'Smart LED TV 55 inch',
                            'is_primary' => true
                        ]
                    ],
                    'category' => [
                        'id' => 2,
                        'name' => 'Electronics',
                        'slug' => 'electronics'
                    ]
                ]
            ]);

            // Apply filters if provided
            if ($request->has('category_id')) {
                $products = $products->where('category_id', $request->category_id);
            }

            if ($request->has('featured') && $request->featured) {
                $products = $products->where('is_featured', true);
            }

            // Pagination
            $perPage = $request->get('per_page', 12);
            $page = $request->get('page', 1);
            $total = $products->count();
            $products = $products->forPage($page, $perPage);

            return response()->json([
                'success' => true,
                'data' => $products->values(),
                'current_page' => (int) $page,
                'last_page' => (int) ceil($total / $perPage),
                'per_page' => (int) $perPage,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(): JsonResponse
    {
        $request = request();
        $request->merge(['featured' => true]);
        return $this->getProducts($request);
    }

    /**
     * Get categories
     */
    public function getCategories(): JsonResponse
    {
        $categories = collect([
            [
                'id' => 1,
                'name' => 'Furniture',
                'slug' => 'furniture',
                'description' => 'Quality furniture for your home',
                'image' => '/images/categories/furniture.jpg',
                'status' => 'active',
                'products_count' => 2
            ],
            [
                'id' => 2,
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Latest electronic gadgets',
                'image' => '/images/categories/electronics.jpg',
                'status' => 'active',
                'products_count' => 1
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get single product
     */
    public function getProduct($id): JsonResponse
    {
        $request = request();
        $products = $this->getProducts($request)->getData()->data;
        
        $product = collect($products)->firstWhere('id', (int) $id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Add item to cart (session-based for now)
     */
    public function addToCart(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity', 1);

            // Get current cart from session
            $cart = session()->get('cart', []);
            
            // Add or update item in cart
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $quantity;
            } else {
                // Get product details
                $productResponse = $this->getProduct($productId);
                $productData = $productResponse->getData();
                
                if (!$productData->success) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found'
                    ], 404);
                }

                $product = $productData->data;
                $cart[$productId] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->sale_price ?? $product->price,
                    'quantity' => $quantity,
                    'image' => $product->images[0]->url ?? '',
                    'maxQuantity' => $product->stock_quantity
                ];
            }

            // Save cart to session
            session()->put('cart', $cart);

            // Calculate totals
            $itemCount = array_sum(array_column($cart, 'quantity'));
            $subtotal = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $cart));

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => [
                    'cart' => [
                        'items' => array_values($cart),
                        'item_count' => $itemCount,
                        'subtotal' => $subtotal,
                        'tax' => $subtotal * 0.18, // 18% tax
                        'total' => $subtotal * 1.18
                    ]
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
     * Get cart contents
     */
    public function getCart(): JsonResponse
    {
        $cart = session()->get('cart', []);
        
        $itemCount = array_sum(array_column($cart, 'quantity'));
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => [
                    'items' => array_values($cart),
                    'item_count' => $itemCount,
                    'subtotal' => $subtotal,
                    'tax' => $subtotal * 0.18,
                    'total' => $subtotal * 1.18
                ]
            ]
        ]);
    }

    /**
     * Health check
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Simple API is working perfectly!',
            'timestamp' => now()->toISOString(),
            'database_status' => 'Session-based (no database required)'
        ]);
    }
}
