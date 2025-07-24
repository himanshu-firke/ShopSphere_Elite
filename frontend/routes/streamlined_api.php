<?php

use App\Http\Controllers\StreamlinedProductController;
use App\Http\Controllers\StreamlinedCartController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Streamlined API Routes - Aligned with Frontend
|--------------------------------------------------------------------------
|
| These routes are specifically designed to match the frontend data structures
| and eliminate conflicts between backend schema and frontend interfaces.
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Streamlined API is working',
        'timestamp' => now()->toISOString()
    ]);
});

// Public product routes - matches frontend ProductService
Route::prefix('products')->group(function () {
    Route::get('/', [StreamlinedProductController::class, 'index']);
    Route::get('/featured', [StreamlinedProductController::class, 'featured']);
    Route::get('/bestsellers', [StreamlinedProductController::class, 'bestsellers']);
    Route::get('/search', [StreamlinedProductController::class, 'search']);
    Route::get('/{id}', [StreamlinedProductController::class, 'show']);
    Route::get('/{id}/related', [StreamlinedProductController::class, 'related']);
});

// Public categories routes
Route::get('/categories', function () {
    $categories = \App\Models\StreamlinedCategory::active()->get();
    return response()->json([
        'success' => true,
        'data' => $categories->map(function ($category) {
            return $category->toFrontendArray();
        })
    ]);
});

// Cart routes - matches frontend CartService
Route::prefix('cart')->group(function () {
    Route::get('/', [StreamlinedCartController::class, 'index']);
    Route::post('/items', [StreamlinedCartController::class, 'addItem']);
    Route::put('/items/{itemId}', [StreamlinedCartController::class, 'updateItem']);
    Route::delete('/items/{itemId}', [StreamlinedCartController::class, 'removeItem']);
    Route::delete('/', [StreamlinedCartController::class, 'clear']);
    Route::get('/count', [StreamlinedCartController::class, 'count']);
});

// Authentication routes - matches frontend AuthService
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// Protected user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });
});

// Test endpoint to verify frontend-backend data flow
Route::get('/test-data-flow', function () {
    $products = \App\Models\StreamlinedProduct::with(['category', 'images'])->take(3)->get();
    $categories = \App\Models\StreamlinedCategory::active()->take(3)->get();
    
    return response()->json([
        'success' => true,
        'message' => 'Data flow test successful',
        'data' => [
            'products_count' => $products->count(),
            'categories_count' => $categories->count(),
            'sample_product' => $products->first()?->toFrontendArray(),
            'sample_category' => $categories->first()?->toFrontendArray(),
        ]
    ]);
});
