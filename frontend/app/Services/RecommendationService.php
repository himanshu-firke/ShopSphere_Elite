<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Get recommendations based on cart items
     */
    public function getCartRecommendations(Cart $cart, int $limit = 6): Collection
    {
        if ($cart->isEmpty()) {
            return $this->getPopularProducts($limit);
        }

        // Get categories and attributes from cart items
        $cartItems = $cart->items()->with('product')->get();
        $categoryIds = $cartItems->pluck('product.category_id')->unique();
        $productIds = $cartItems->pluck('product_id');

        // Get similar products based on categories and attributes
        $recommendations = Product::query()
            ->with('primaryImage')
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $productIds)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();

        // If not enough recommendations, add popular products
        if ($recommendations->count() < $limit) {
            $additionalCount = $limit - $recommendations->count();
            $popularProducts = $this->getPopularProducts($additionalCount, $productIds);
            $recommendations = $recommendations->merge($popularProducts);
        }

        return $recommendations;
    }

    /**
     * Get recommendations based on wishlist items
     */
    public function getWishlistRecommendations(User $user, int $limit = 6): Collection
    {
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($wishlistItems->isEmpty()) {
            return $this->getPopularProducts($limit);
        }

        // Get categories and attributes from wishlist items
        $categoryIds = $wishlistItems->pluck('product.category_id')->unique();
        $productIds = $wishlistItems->pluck('product_id');

        // Get similar products based on categories and attributes
        $recommendations = Product::query()
            ->with('primaryImage')
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $productIds)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();

        // If not enough recommendations, add popular products
        if ($recommendations->count() < $limit) {
            $additionalCount = $limit - $recommendations->count();
            $popularProducts = $this->getPopularProducts($additionalCount, $productIds);
            $recommendations = $recommendations->merge($popularProducts);
        }

        return $recommendations;
    }

    /**
     * Get recommendations based on product
     */
    public function getProductRecommendations(Product $product, int $limit = 6): Collection
    {
        // Get similar products based on category and attributes
        $recommendations = Product::query()
            ->with('primaryImage')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();

        // If not enough recommendations, add popular products from other categories
        if ($recommendations->count() < $limit) {
            $additionalCount = $limit - $recommendations->count();
            $excludeIds = $recommendations->pluck('id')->push($product->id);
            $popularProducts = $this->getPopularProducts($additionalCount, $excludeIds);
            $recommendations = $recommendations->merge($popularProducts);
        }

        return $recommendations;
    }

    /**
     * Get popular products
     */
    private function getPopularProducts(int $limit = 6, $excludeIds = []): Collection
    {
        return Product::query()
            ->with('primaryImage')
            ->whereNotIn('id', $excludeIds)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('average_rating', 'desc')
            ->orderBy('total_reviews', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get personalized recommendations for user
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 6): Collection
    {
        // Get user's purchase history
        $purchasedProductIds = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $user->id)
            ->pluck('order_items.product_id');

        // Get categories from user's purchase history
        $categoryIds = Product::whereIn('id', $purchasedProductIds)
            ->pluck('category_id')
            ->unique();

        // Get recommendations based on purchase history
        $recommendations = Product::query()
            ->with('primaryImage')
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $purchasedProductIds)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();

        // If not enough recommendations, add popular products
        if ($recommendations->count() < $limit) {
            $additionalCount = $limit - $recommendations->count();
            $excludeIds = $recommendations->pluck('id')->merge($purchasedProductIds);
            $popularProducts = $this->getPopularProducts($additionalCount, $excludeIds);
            $recommendations = $recommendations->merge($popularProducts);
        }

        return $recommendations;
    }

    /**
     * Get trending products
     */
    public function getTrendingProducts(int $limit = 6): Collection
    {
        // Get products with most orders in last 30 days
        $trendingProductIds = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('order_items.product_id')
            ->orderByRaw('SUM(order_items.quantity) DESC')
            ->limit($limit)
            ->pluck('order_items.product_id');

        return Product::query()
            ->with('primaryImage')
            ->whereIn('id', $trendingProductIds)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->sortBy(function ($product) use ($trendingProductIds) {
                return array_search($product->id, $trendingProductIds->toArray());
            })
            ->values();
    }

    /**
     * Get frequently bought together products
     */
    public function getFrequentlyBoughtTogether(Product $product, int $limit = 3): Collection
    {
        // Get products that are often bought with this product
        $relatedProductIds = DB::table('order_items as oi1')
            ->join('order_items as oi2', 'oi1.order_id', '=', 'oi2.order_id')
            ->where('oi1.product_id', $product->id)
            ->where('oi2.product_id', '!=', $product->id)
            ->groupBy('oi2.product_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit($limit)
            ->pluck('oi2.product_id');

        return Product::query()
            ->with('primaryImage')
            ->whereIn('id', $relatedProductIds)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->sortBy(function ($product) use ($relatedProductIds) {
                return array_search($product->id, $relatedProductIds->toArray());
            })
            ->values();
    }
} 