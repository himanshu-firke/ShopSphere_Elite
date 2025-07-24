<?php

namespace App\Http\Controllers;

use App\Models\StreamlinedCart;
use App\Models\StreamlinedCartItem;
use App\Models\StreamlinedProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class StreamlinedCartController extends Controller
{
    /**
     * Get cart contents - matches frontend CartService
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => $cart->toFrontendArray()
            ]
        ]);
    }

    /**
     * Add item to cart - matches frontend CartService
     */
    public function addItem(Request $request): JsonResponse
    {
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

        $product = StreamlinedProduct::find($request->product_id);
        
        if (!$product || $product->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Product not available'
            ], 404);
        }

        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock'
            ], 400);
        }

        $cart = $this->getOrCreateCart($request);
        
        // Check if item already exists in cart
        $existingItem = $cart->items()->where('product_id', $request->product_id)->first();
        
        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $request->quantity;
            
            if ($newQuantity > $product->stock_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more items than available in stock'
                ], 400);
            }
            
            $existingItem->quantity = $newQuantity;
            $existingItem->save();
        } else {
            StreamlinedCartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $product->sale_price ?? $product->price
            ]);
        }

        $cart->load('items.product.primaryImage');
        $cart->calculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => [
                'cart' => $cart->toFrontendArray()
            ]
        ]);
    }

    /**
     * Update cart item quantity - matches frontend CartService
     */
    public function updateItem(Request $request, $itemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cart = $this->getOrCreateCart($request);
        $cartItem = $cart->items()->find($itemId);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        if ($cartItem->product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock'
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        $cart->load('items.product.primaryImage');
        $cart->calculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => [
                'cart' => $cart->toFrontendArray()
            ]
        ]);
    }

    /**
     * Remove item from cart - matches frontend CartService
     */
    public function removeItem(Request $request, $itemId): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cartItem = $cart->items()->find($itemId);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $cartItem->delete();

        $cart->load('items.product.primaryImage');
        $cart->calculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'data' => [
                'cart' => $cart->toFrontendArray()
            ]
        ]);
    }

    /**
     * Clear entire cart - matches frontend CartService
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();
        $cart->calculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared'
        ]);
    }

    /**
     * Get cart count - matches frontend CartService
     */
    public function count(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $cart->item_count
            ]
        ]);
    }

    /**
     * Get or create cart for user/session
     */
    private function getOrCreateCart(Request $request): StreamlinedCart
    {
        $user = $request->user();
        $sessionId = $request->cookie('cart_session', session()->getId());

        if ($user) {
            $cart = StreamlinedCart::where('user_id', $user->id)->first();
        } else {
            $cart = StreamlinedCart::where('session_id', $sessionId)->first();
        }

        if (!$cart) {
            $cart = StreamlinedCart::create([
                'user_id' => $user?->id,
                'session_id' => $user ? null : $sessionId,
                'item_count' => 0,
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'total' => 0
            ]);
        }

        $cart->load('items.product.primaryImage');
        return $cart;
    }
}
