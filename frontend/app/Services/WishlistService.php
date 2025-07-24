<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;

class WishlistService
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get user's wishlist
     */
    public function getWishlist(User $user): array
    {
        $items = Wishlist::with(['product.primaryImage'])
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'image' => $item->product->primaryImage?->url,
                        'price' => $item->current_price,
                        'is_on_sale' => $item->isOnSale(),
                        'discount_percentage' => $item->discount_percentage,
                        'in_stock' => $item->isInStock(),
                        'is_active' => $item->isActive()
                    ],
                    'notes' => $item->notes,
                    'added_date' => $item->added_date
                ];
            });

        return [
            'items' => $items,
            'total_items' => $items->count()
        ];
    }

    /**
     * Add product to wishlist
     */
    public function addToWishlist(User $user, Product $product, ?string $notes = null): Wishlist
    {
        // Check if product is already in wishlist
        $existingItem = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingItem) {
            if ($notes) {
                $existingItem->update(['notes' => $notes]);
            }
            return $existingItem;
        }

        // Add new item to wishlist
        return Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'notes' => $notes
        ]);
    }

    /**
     * Remove product from wishlist
     */
    public function removeFromWishlist(User $user, Wishlist $item): bool
    {
        if ($item->user_id !== $user->id) {
            throw new \Exception('Unauthorized');
        }

        return $item->delete();
    }

    /**
     * Move item from wishlist to cart
     */
    public function moveToCart(User $user, Wishlist $item, int $quantity = 1): void
    {
        if ($item->user_id !== $user->id) {
            throw new \Exception('Unauthorized');
        }

        try {
            DB::beginTransaction();

            // Add to cart
            $cart = $this->cartService->getCart($user);
            $this->cartService->addToCart($cart, $item->product, $quantity);

            // Remove from wishlist
            $item->delete();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Clear user's wishlist
     */
    public function clearWishlist(User $user): bool
    {
        return Wishlist::where('user_id', $user->id)->delete();
    }

    /**
     * Check if a product is in user's wishlist
     */
    public function isInWishlist(User $user, Product $product): bool
    {
        return Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * Get wishlist item count for user
     */
    public function getItemCount(User $user): int
    {
        return Wishlist::where('user_id', $user->id)->count();
    }

    /**
     * Update wishlist item notes
     */
    public function updateNotes(User $user, Wishlist $item, string $notes): void
    {
        if ($item->user_id !== $user->id) {
            throw new \Exception('Unauthorized');
        }

        $item->update(['notes' => $notes]);
    }

    /**
     * Move all wishlist items to cart
     */
    public function moveAllToCart(User $user): array
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        $cart = $this->cartService->getCart($user);
        $items = Wishlist::with('product')
            ->where('user_id', $user->id)
            ->get();

        foreach ($items as $item) {
            try {
                $this->cartService->addToCart($cart, $item->product);
                $item->delete();
                $results['success'][] = $item->product_id;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'product_id' => $item->product_id,
                    'reason' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
} 