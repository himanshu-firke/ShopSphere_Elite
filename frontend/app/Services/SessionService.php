<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SessionService
{
    /**
     * Generate a new session ID
     */
    public function generateSessionId(): string
    {
        return Str::random(40);
    }

    /**
     * Get cart session ID from Redis
     */
    public function getCartSessionId(?User $user = null, ?string $sessionId = null): ?string
    {
        if ($user) {
            return Redis::get("cart:user:{$user->id}");
        }
        return $sessionId ? Redis::get("cart:session:{$sessionId}") : null;
    }

    /**
     * Store cart session ID in Redis
     */
    public function storeCartSessionId(?User $user = null, ?string $sessionId = null, string $cartId): void
    {
        if ($user) {
            Redis::set("cart:user:{$user->id}", $cartId, 'EX', 60 * 60 * 24 * 7); // 7 days
        } elseif ($sessionId) {
            Redis::set("cart:session:{$sessionId}", $cartId, 'EX', 60 * 60 * 24); // 24 hours
        }
    }

    /**
     * Remove cart session ID from Redis
     */
    public function removeCartSessionId(?User $user = null, ?string $sessionId = null): void
    {
        if ($user) {
            Redis::del("cart:user:{$user->id}");
        } elseif ($sessionId) {
            Redis::del("cart:session:{$sessionId}");
        }
    }

    /**
     * Merge guest cart into user cart
     */
    public function mergeGuestCart(User $user, string $guestSessionId): void
    {
        $guestCartId = $this->getCartSessionId(null, $guestSessionId);
        $userCartId = $this->getCartSessionId($user);

        if (!$guestCartId) {
            return;
        }

        $guestCart = Cart::find($guestCartId);
        if (!$guestCart) {
            $this->removeCartSessionId(null, $guestSessionId);
            return;
        }

        if ($userCartId) {
            $userCart = Cart::find($userCartId);
            if ($userCart) {
                // Merge items from guest cart to user cart
                foreach ($guestCart->items as $item) {
                    try {
                        $userCart->addItem($item->product, $item->quantity, $item->options);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to merge cart item: ' . $e->getMessage(), [
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity
                        ]);
                    }
                }
            }
        } else {
            // Convert guest cart to user cart
            $guestCart->update([
                'user_id' => $user->id,
                'session_id' => null,
                'expires_at' => now()->addDays(7)
            ]);
            $this->storeCartSessionId($user, null, $guestCart->id);
        }

        // Clean up guest cart
        if ($userCartId) {
            $guestCart->delete();
        }
        $this->removeCartSessionId(null, $guestSessionId);
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): void
    {
        // Get all cart session keys
        $userKeys = Redis::keys('cart:user:*');
        $sessionKeys = Redis::keys('cart:session:*');

        // Clean up user cart sessions
        foreach ($userKeys as $key) {
            $cartId = Redis::get($key);
            if ($cartId) {
                $cart = Cart::find($cartId);
                if (!$cart || $cart->hasExpired()) {
                    Redis::del($key);
                }
            }
        }

        // Clean up guest cart sessions
        foreach ($sessionKeys as $key) {
            $cartId = Redis::get($key);
            if ($cartId) {
                $cart = Cart::find($cartId);
                if (!$cart || $cart->hasExpired()) {
                    Redis::del($key);
                }
            }
        }

        // Delete expired carts from database
        Cart::where('expires_at', '<', now())->delete();
    }

    /**
     * Extend session expiration
     */
    public function extendSession(?User $user = null, ?string $sessionId = null): void
    {
        $cartId = $this->getCartSessionId($user, $sessionId);
        if (!$cartId) {
            return;
        }

        $cart = Cart::find($cartId);
        if (!$cart) {
            $this->removeCartSessionId($user, $sessionId);
            return;
        }

        // Extend cart expiration
        $cart->extend($user ? 60 * 24 * 7 : 60 * 24); // 7 days for users, 24 hours for guests

        // Extend Redis expiration
        if ($user) {
            Redis::expire("cart:user:{$user->id}", 60 * 60 * 24 * 7); // 7 days
        } elseif ($sessionId) {
            Redis::expire("cart:session:{$sessionId}", 60 * 60 * 24); // 24 hours
        }
    }
} 