<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Get or create a cart for the current session/user
     */
    public function getCart(?User $user = null, ?string $sessionId = null): Cart
    {
        if ($user) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'session_id' => $sessionId,
                    'expires_at' => now()->addDays(7)
                ]
            );
        } else {
            $sessionId = $sessionId ?? $this->generateSessionId();
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId],
                ['expires_at' => now()->addDays(1)]
            );
        }

        // Store cart ID in Redis for quick access
        $key = $this->getRedisKey($user?->id, $sessionId);
        Redis::set($key, $cart->id, 'EX', 86400); // 24 hours

        return $cart;
    }

    /**
     * Add a product to the cart
     */
    public function addToCart(Cart $cart, Product $product, int $quantity = 1, array $options = []): CartItem
    {
        // Validate stock availability
        if ($product->stock_quantity < $quantity) {
            throw new \Exception('Insufficient stock available');
        }

        // Validate product status
        if (!$product->is_active) {
            throw new \Exception('Product is not available');
        }

        // Add or update cart item
        $item = $cart->addItem($product, $quantity, $options);

        // Update cart in Redis
        $this->updateCartInRedis($cart);

        return $item;
    }

    /**
     * Update item quantity in cart
     */
    public function updateQuantity(Cart $cart, CartItem $item, int $quantity): void
    {
        // Validate stock availability
        if ($quantity > 0 && $item->product->stock_quantity < $quantity) {
            throw new \Exception('Insufficient stock available');
        }

        // Update quantity
        $cart->updateItemQuantity($item, $quantity);

        // Update cart in Redis
        $this->updateCartInRedis($cart);
    }

    /**
     * Remove an item from cart
     */
    public function removeFromCart(Cart $cart, CartItem $item): void
    {
        $cart->removeItem($item);

        // Update cart in Redis
        $this->updateCartInRedis($cart);
    }

    /**
     * Clear the cart
     */
    public function clearCart(Cart $cart): void
    {
        $cart->clear();

        // Update cart in Redis
        $this->updateCartInRedis($cart);
    }

    /**
     * Merge guest cart into user cart
     */
    public function mergeGuestCart(Cart $guestCart, User $user): Cart
    {
        $userCart = $this->getCart($user);

        // Move items from guest cart to user cart
        foreach ($guestCart->items as $item) {
            try {
                $this->addToCart(
                    $userCart,
                    $item->product,
                    $item->quantity,
                    $item->options
                );
            } catch (\Exception $e) {
                // Log error but continue with other items
                \Log::warning('Failed to merge cart item: ' . $e->getMessage(), [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity
                ]);
            }
        }

        // Delete guest cart
        $guestCart->delete();

        // Remove guest cart from Redis
        Redis::del($this->getRedisKey(null, $guestCart->session_id));

        return $userCart;
    }

    /**
     * Get cart from Redis or database
     */
    public function findCart(?User $user = null, ?string $sessionId = null): ?Cart
    {
        $key = $this->getRedisKey($user?->id, $sessionId);
        $cartId = Redis::get($key);

        if ($cartId) {
            $cart = Cart::find($cartId);
            if ($cart && !$cart->hasExpired()) {
                return $cart;
            }
        }

        // Try to find cart in database
        $query = Cart::query();
        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return null;
        }

        $cart = $query->first();

        // Store in Redis if found
        if ($cart && !$cart->hasExpired()) {
            Redis::set($key, $cart->id, 'EX', 86400); // 24 hours
            return $cart;
        }

        return null;
    }

    /**
     * Delete expired carts
     */
    public function deleteExpiredCarts(): int
    {
        return Cart::where('expires_at', '<', now())->delete();
    }

    /**
     * Generate a unique session ID
     */
    private function generateSessionId(): string
    {
        return Str::random(40);
    }

    /**
     * Get Redis key for cart
     */
    private function getRedisKey(?int $userId = null, ?string $sessionId = null): string
    {
        if ($userId) {
            return "cart:user:{$userId}";
        }
        return "cart:session:{$sessionId}";
    }

    /**
     * Update cart data in Redis
     */
    private function updateCartInRedis(Cart $cart): void
    {
        $key = $this->getRedisKey($cart->user_id, $cart->session_id);
        Redis::set($key, $cart->id, 'EX', 86400); // 24 hours

        // Extend cart expiration
        if ($cart->user_id) {
            $cart->extend(60 * 24 * 7); // 7 days for users
        } else {
            $cart->extend(60 * 24); // 24 hours for guests
        }
    }
} 