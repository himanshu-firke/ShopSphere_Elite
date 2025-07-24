<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class MergeGuestCart
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (!config('cart.merge_cart_on_login', true)) {
            return;
        }

        $sessionId = session()->getId();
        $userId = $event->user->id;

        // Get guest cart
        $guestCart = DB::table('carts')
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->first();

        if (!$guestCart) {
            return;
        }

        // Get or create user cart
        $userCart = DB::table('carts')
            ->where('user_id', $userId)
            ->first();

        if (!$userCart) {
            // Convert guest cart to user cart
            DB::table('carts')
                ->where('id', $guestCart->id)
                ->update([
                    'user_id' => $userId,
                    'session_id' => null
                ]);
            return;
        }

        // Merge cart items
        $guestItems = DB::table('cart_items')
            ->where('cart_id', $guestCart->id)
            ->get();

        foreach ($guestItems as $guestItem) {
            $existingItem = DB::table('cart_items')
                ->where('cart_id', $userCart->id)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                // Update quantity of existing item
                DB::table('cart_items')
                    ->where('id', $existingItem->id)
                    ->update([
                        'quantity' => $existingItem->quantity + $guestItem->quantity,
                        'updated_at' => now()
                    ]);
            } else {
                // Move item to user cart
                DB::table('cart_items')
                    ->where('id', $guestItem->id)
                    ->update([
                        'cart_id' => $userCart->id,
                        'updated_at' => now()
                    ]);
            }
        }

        // Delete guest cart
        DB::table('carts')
            ->where('id', $guestCart->id)
            ->delete();
    }
} 