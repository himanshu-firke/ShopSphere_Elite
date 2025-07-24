<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    protected $recommendationService;
    protected $cartService;

    public function __construct(
        RecommendationService $recommendationService,
        CartService $cartService
    ) {
        $this->recommendationService = $recommendationService;
        $this->cartService = $cartService;
    }

    /**
     * Get cart recommendations
     */
    public function cartRecommendations(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart(
            $request->user(),
            $request->cookie('cart_session')
        );

        $recommendations = $this->recommendationService->getCartRecommendations(
            $cart,
            $request->get('limit', 6)
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    /**
     * Get wishlist recommendations
     */
    public function wishlistRecommendations(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $recommendations = $this->recommendationService->getWishlistRecommendations(
            $request->user(),
            $request->get('limit', 6)
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    /**
     * Get product recommendations
     */
    public function productRecommendations(Product $product, Request $request): JsonResponse
    {
        $recommendations = $this->recommendationService->getProductRecommendations(
            $product,
            $request->get('limit', 6)
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    /**
     * Get personalized recommendations
     */
    public function personalizedRecommendations(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $recommendations = $this->recommendationService->getPersonalizedRecommendations(
            $request->user(),
            $request->get('limit', 6)
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    /**
     * Get trending products
     */
    public function trendingProducts(Request $request): JsonResponse
    {
        $recommendations = $this->recommendationService->getTrendingProducts(
            $request->get('limit', 6)
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    /**
     * Get frequently bought together products
     */
    public function frequentlyBoughtTogether(Product $product, Request $request): JsonResponse
    {
        $recommendations = $this->recommendationService->getFrequentlyBoughtTogether(
            $product,
            $request->get('limit', 3)
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }
} 