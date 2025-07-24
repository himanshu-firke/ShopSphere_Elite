<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get cart contents
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart(
            $request->user(),
            $request->cookie('cart_session')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => $cart->load(['items.product.primaryImage']),
                'item_count' => $cart->item_count,
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'discount' => $cart->discount,
                'total' => $cart->total
            ]
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
            'options' => CartItem::getOptionValidationRules()
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cart = $this->cartService->getCart(
                $request->user(),
                $request->cookie('cart_session')
            );

            $product = Product::findOrFail($request->product_id);
            $item = $this->cartService->addToCart(
                $cart,
                $product,
                $request->quantity,
                $request->options ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => [
                    'item' => $item->load('product.primaryImage'),
                    'cart' => [
                        'item_count' => $cart->item_count,
                        'subtotal' => $cart->subtotal,
                        'tax' => $cart->tax,
                        'discount' => $cart->discount,
                        'total' => $cart->total
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(Request $request, CartItem $item): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0|max:99'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cart = $this->cartService->getCart(
                $request->user(),
                $request->cookie('cart_session')
            );

            // Verify item belongs to cart
            if ($item->cart_id !== $cart->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart'
                ], 404);
            }

            $this->cartService->updateQuantity(
                $cart,
                $item,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated',
                'data' => [
                    'item' => $item->fresh(['product.primaryImage']),
                    'cart' => [
                        'item_count' => $cart->item_count,
                        'subtotal' => $cart->subtotal,
                        'tax' => $cart->tax,
                        'discount' => $cart->discount,
                        'total' => $cart->total
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, CartItem $item): JsonResponse
    {
        try {
            $cart = $this->cartService->getCart(
                $request->user(),
                $request->cookie('cart_session')
            );

            // Verify item belongs to cart
            if ($item->cart_id !== $cart->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart'
                ], 404);
            }

            $this->cartService->removeFromCart($cart, $item);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'data' => [
                    'cart' => [
                        'item_count' => $cart->item_count,
                        'subtotal' => $cart->subtotal,
                        'tax' => $cart->tax,
                        'discount' => $cart->discount,
                        'total' => $cart->total
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $cart = $this->cartService->getCart(
                $request->user(),
                $request->cookie('cart_session')
            );

            $this->cartService->clearCart($cart);

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cart = $this->cartService->getCart(
                $request->user(),
                $request->cookie('cart_session')
            );

            // TODO: Implement coupon validation and application
            // For now, just store the code
            $cart->update(['coupon_code' => $request->code]);
            $cart->calculateTotals();

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied',
                'data' => [
                    'cart' => [
                        'item_count' => $cart->item_count,
                        'subtotal' => $cart->subtotal,
                        'tax' => $cart->tax,
                        'discount' => $cart->discount,
                        'total' => $cart->total
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        try {
            $cart = $this->cartService->getCart(
                $request->user(),
                $request->cookie('cart_session')
            );

            $cart->update(['coupon_code' => null]);
            $cart->calculateTotals();

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed',
                'data' => [
                    'cart' => [
                        'item_count' => $cart->item_count,
                        'subtotal' => $cart->subtotal,
                        'tax' => $cart->tax,
                        'discount' => $cart->discount,
                        'total' => $cart->total
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Add notes to cart
     */
    public function addNotes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cart = $this->cartService->getCart(
                $request->user(),
                $request->cookie('cart_session')
            );

            $cart->update(['notes' => $request->notes]);

            return response()->json([
                'success' => true,
                'message' => 'Notes added to cart'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
} 