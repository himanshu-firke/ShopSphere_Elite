<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\StreamlinedProductController;
use App\Http\Controllers\StreamlinedCartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Password reset routes (public)
Route::post('/auth/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/auth/reset-password', [PasswordResetController::class, 'reset']);
Route::post('/auth/check-reset-token', [PasswordResetController::class, 'checkToken']);

// Social login routes (public)
Route::get('/auth/google/redirect', [SocialLoginController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialLoginController::class, 'handleGoogleCallback']);
Route::get('/auth/facebook/redirect', [SocialLoginController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialLoginController::class, 'handleFacebookCallback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // Social account management
    Route::post('/auth/social/link', [SocialLoginController::class, 'linkSocialAccount']);
    Route::delete('/auth/social/unlink', [SocialLoginController::class, 'unlinkSocialAccount']);
});

/*
|--------------------------------------------------------------------------
| STREAMLINED API ROUTES - Frontend-Backend Aligned
|--------------------------------------------------------------------------
|
| These routes are specifically designed to match the frontend data structures
| and eliminate conflicts between backend schema and frontend interfaces.
|
*/

// Simple working API routes (no database required)
Route::prefix('simple')->group(function () {
    Route::get('/health', [\App\Http\Controllers\SimpleApiController::class, 'health']);
    Route::get('/products', [\App\Http\Controllers\SimpleApiController::class, 'getProducts']);
    Route::get('/products/featured', [\App\Http\Controllers\SimpleApiController::class, 'getFeaturedProducts']);
    Route::get('/products/{id}', [\App\Http\Controllers\SimpleApiController::class, 'getProduct']);
    Route::get('/categories', [\App\Http\Controllers\SimpleApiController::class, 'getCategories']);
    Route::get('/cart', [\App\Http\Controllers\SimpleApiController::class, 'getCart']);
    Route::post('/cart/items', [\App\Http\Controllers\SimpleApiController::class, 'addToCart']);
});

// Database-backed API routes (MySQL persistent storage)
Route::prefix('db')->group(function () {
    Route::get('/health', [\App\Http\Controllers\DatabaseApiController::class, 'health']);
    Route::get('/products', [\App\Http\Controllers\DatabaseApiController::class, 'getProducts']);
    Route::get('/products/featured', [\App\Http\Controllers\DatabaseApiController::class, 'getFeaturedProducts']);
    Route::get('/products/{id}', [\App\Http\Controllers\DatabaseApiController::class, 'getProduct']);
    Route::get('/categories', [\App\Http\Controllers\DatabaseApiController::class, 'getCategories']);
    Route::get('/cart', [\App\Http\Controllers\DatabaseApiController::class, 'getCart']);
    Route::post('/cart/items', [\App\Http\Controllers\DatabaseApiController::class, 'addToCart']);
});

// Health check endpoint
Route::get('/streamlined/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Streamlined API is working',
        'timestamp' => now()->toISOString()
    ]);
});

// Streamlined product routes - matches frontend ProductService
Route::prefix('streamlined/products')->group(function () {
    Route::get('/', [StreamlinedProductController::class, 'index']);
    Route::get('/featured', [StreamlinedProductController::class, 'featured']);
    Route::get('/bestsellers', [StreamlinedProductController::class, 'bestsellers']);
    Route::get('/search', [StreamlinedProductController::class, 'search']);
    Route::get('/{id}', [StreamlinedProductController::class, 'show']);
    Route::get('/{id}/related', [StreamlinedProductController::class, 'related']);
});

// Streamlined categories routes
Route::get('/streamlined/categories', function () {
    $categories = \App\Models\StreamlinedCategory::active()->get();
    return response()->json([
        'success' => true,
        'data' => $categories->map(function ($category) {
            return $category->toFrontendArray();
        })
    ]);
});

// Streamlined cart routes - matches frontend CartService
Route::prefix('streamlined/cart')->group(function () {
    Route::get('/', [StreamlinedCartController::class, 'index']);
    Route::post('/items', [StreamlinedCartController::class, 'addItem']);
    Route::put('/items/{itemId}', [StreamlinedCartController::class, 'updateItem']);
    Route::delete('/items/{itemId}', [StreamlinedCartController::class, 'removeItem']);
    Route::delete('/', [StreamlinedCartController::class, 'clear']);
    Route::get('/count', [StreamlinedCartController::class, 'count']);
});

// Test endpoint to verify frontend-backend data flow
Route::get('/streamlined/test-data-flow', function () {
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

// User profile management routes
Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'getProfile']);
    Route::put('/', [ProfileController::class, 'updateProfile']);
    Route::post('/picture', [ProfileController::class, 'uploadProfilePicture']);
    Route::put('/preferences', [ProfileController::class, 'updatePreferences']);
    
    // Address management
    Route::get('/addresses', [ProfileController::class, 'getAddresses']);
    Route::post('/addresses', [ProfileController::class, 'addAddress']);
    Route::put('/addresses/{address}', [ProfileController::class, 'updateAddress']);
    Route::delete('/addresses/{address}', [ProfileController::class, 'deleteAddress']);
    Route::patch('/addresses/{address}/default', [ProfileController::class, 'setDefaultAddress']);
});

// Admin routes (admin only)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // User management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/users/stats', [AdminController::class, 'getUserStats']);
    Route::patch('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus']);
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole']);

    // Product management
    Route::get('/products', [App\Http\Controllers\Admin\ProductManagementController::class, 'index']);
    Route::post('/products', [App\Http\Controllers\Admin\ProductManagementController::class, 'store']);
    Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductManagementController::class, 'update']);
    Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductManagementController::class, 'destroy']);
    Route::post('/products/bulk-status', [App\Http\Controllers\Admin\ProductManagementController::class, 'bulkUpdateStatus']);
    Route::get('/products/statistics', [App\Http\Controllers\Admin\ProductManagementController::class, 'statistics']);

    // Review moderation
    Route::get('/reviews/pending', [App\Http\Controllers\Admin\ReviewModerationController::class, 'index']);
    Route::post('/reviews/{review}/approve', [App\Http\Controllers\Admin\ReviewModerationController::class, 'approve']);
    Route::post('/reviews/{review}/reject', [App\Http\Controllers\Admin\ReviewModerationController::class, 'reject']);
    Route::post('/reviews/bulk-approve', [App\Http\Controllers\Admin\ReviewModerationController::class, 'bulkApprove']);
    Route::get('/reviews/moderation-stats', [App\Http\Controllers\Admin\ReviewModerationController::class, 'statistics']);

    // Inventory management
    Route::get('/inventory', [App\Http\Controllers\Admin\InventoryController::class, 'index']);
    Route::post('/inventory/{product}/update-stock', [App\Http\Controllers\Admin\InventoryController::class, 'updateStock']);
    Route::post('/inventory/bulk-update', [App\Http\Controllers\Admin\InventoryController::class, 'bulkUpdateStock']);
    Route::get('/inventory/{product}/history', [App\Http\Controllers\Admin\InventoryController::class, 'history']);
    Route::get('/inventory/low-stock-alerts', [App\Http\Controllers\Admin\InventoryController::class, 'lowStockAlerts']);
    Route::put('/inventory/{product}/threshold', [App\Http\Controllers\Admin\InventoryController::class, 'updateThreshold']);
});

// Vendor routes (vendor and admin)
Route::middleware(['auth:sanctum', 'any.role:vendor,admin'])->prefix('vendor')->group(function () {
    // Vendor-specific routes will be added here
});

// Customer routes (authenticated users)
Route::middleware('auth:sanctum')->prefix('customer')->group(function () {
    // Cart routes
    Route::get('/cart', [App\Http\Controllers\CartController::class, 'index']);
    Route::post('/cart/items', [App\Http\Controllers\CartController::class, 'addItem']);
    Route::put('/cart/items/{itemId}', [App\Http\Controllers\CartController::class, 'updateQuantity']);
    Route::delete('/cart/items/{itemId}', [App\Http\Controllers\CartController::class, 'removeItem']);
    Route::delete('/cart', [App\Http\Controllers\CartController::class, 'clear']);
    Route::post('/cart/coupon', [App\Http\Controllers\CartController::class, 'applyCoupon']);
    Route::delete('/cart/coupon', [App\Http\Controllers\CartController::class, 'removeCoupon']);
    Route::post('/cart/notes', [App\Http\Controllers\CartController::class, 'addNotes']);

    // Wishlist routes
    Route::get('/wishlist', [App\Http\Controllers\WishlistController::class, 'index']);
    Route::post('/wishlist/{product}', [App\Http\Controllers\WishlistController::class, 'addItem']);
    Route::delete('/wishlist/{wishlistId}', [App\Http\Controllers\WishlistController::class, 'removeItem']);
    Route::delete('/wishlist', [App\Http\Controllers\WishlistController::class, 'clear']);
    Route::post('/wishlist/{wishlistId}/move-to-cart', [App\Http\Controllers\WishlistController::class, 'moveToCart']);
    Route::get('/wishlist/check/{product}', [App\Http\Controllers\WishlistController::class, 'check']);
});

// Wishlist routes
Route::middleware(['auth:sanctum'])->prefix('wishlist')->group(function () {
    Route::get('/', [WishlistController::class, 'index']);
    Route::post('/products/{product}', [WishlistController::class, 'addItem']);
    Route::delete('/items/{item}', [WishlistController::class, 'removeItem']);
    Route::delete('/', [WishlistController::class, 'clear']);
    Route::post('/items/{item}/move-to-cart', [WishlistController::class, 'moveToCart']);
    Route::post('/move-all-to-cart', [WishlistController::class, 'moveAllToCart']);
    Route::get('/products/{product}/check', [WishlistController::class, 'check']);
    Route::put('/items/{item}/notes', [WishlistController::class, 'updateNotes']);
});

// Public product catalog routes
Route::prefix('products')->group(function () {
    Route::get('/', [App\Http\Controllers\ProductController::class, 'index']);
    Route::get('/featured', [App\Http\Controllers\ProductController::class, 'featured']);
    Route::get('/bestsellers', [App\Http\Controllers\ProductController::class, 'bestsellers']);
    Route::get('/new', [App\Http\Controllers\ProductController::class, 'newProducts']);
    Route::get('/on-sale', [App\Http\Controllers\ProductController::class, 'onSale']);
    Route::get('/{slug}', [App\Http\Controllers\ProductController::class, 'show']);

    // Product reviews (public)
    Route::get('/{product}/reviews', [App\Http\Controllers\ReviewController::class, 'index']);

    // Product reviews (authenticated)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/{product}/reviews', [App\Http\Controllers\ReviewController::class, 'store']);
        Route::put('/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'update']);
        Route::delete('/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'destroy']);
        Route::post('/reviews/{review}/helpful', [App\Http\Controllers\ReviewController::class, 'toggleHelpful']);
    });
    
    // Product image management (vendor/admin only)
    Route::middleware(['auth:sanctum', 'any.role:vendor,admin'])->group(function () {
        Route::get('/{product}/images', [App\Http\Controllers\ProductImageController::class, 'index']);
        Route::post('/{product}/images', [App\Http\Controllers\ProductImageController::class, 'upload']);
        Route::put('/images/{image}', [App\Http\Controllers\ProductImageController::class, 'update']);
        Route::delete('/images/{image}', [App\Http\Controllers\ProductImageController::class, 'destroy']);
        Route::post('/{product}/images/reorder', [App\Http\Controllers\ProductImageController::class, 'reorder']);
    });
});

// Public category routes
Route::prefix('categories')->group(function () {
    Route::get('/', [App\Http\Controllers\CategoryController::class, 'index']);
    Route::get('/tree', [App\Http\Controllers\CategoryController::class, 'tree']);
    Route::get('/popular', [App\Http\Controllers\CategoryController::class, 'popular']);
    Route::get('/search', [App\Http\Controllers\CategoryController::class, 'search']);
    Route::get('/{slug}', [App\Http\Controllers\CategoryController::class, 'show']);
    Route::get('/{slug}/breadcrumbs', [App\Http\Controllers\CategoryController::class, 'breadcrumbs']);
});

// Search routes
Route::prefix('search')->group(function () {
    Route::get('/', [App\Http\Controllers\SearchController::class, 'search']);
    Route::get('/suggestions', [App\Http\Controllers\SearchController::class, 'suggestions']);
    Route::get('/filters', [App\Http\Controllers\SearchController::class, 'filters']);
    Route::get('/analytics', [App\Http\Controllers\SearchController::class, 'analytics']);
});

// Checkout Routes
Route::prefix('checkout')->group(function () {
    Route::get('initialize', [CheckoutController::class, 'initialize']);
    Route::post('validate-address', [CheckoutController::class, 'validateAddress']);
    Route::post('create-order', [CheckoutController::class, 'createOrder']);
    Route::get('order/{orderNumber}', [CheckoutController::class, 'getOrderSummary']);
});

// Stripe Payment Routes
Route::prefix('stripe')->middleware('payment')->group(function () {
    Route::post('create-payment-intent', [StripeController::class, 'createPaymentIntent']);
    Route::post('payment-success', [StripeController::class, 'handlePaymentSuccess']);
    Route::post('webhook', [StripeController::class, 'handleWebhook']);
});

// PayPal Payment Routes
Route::prefix('paypal')->middleware('payment')->group(function () {
    Route::post('create-order', [PayPalController::class, 'createOrder']);
    Route::post('success', [PayPalController::class, 'handleSuccess'])->name('paypal.success');
    Route::post('cancel', [PayPalController::class, 'handleCancel'])->name('paypal.cancel');
    Route::post('webhook', [PayPalController::class, 'handleWebhook']);
});

// Invoice Routes
Route::prefix('invoices')->group(function () {
    Route::get('{orderNumber}/generate', [InvoiceController::class, 'generate']);
    Route::get('{orderNumber}/download', [InvoiceController::class, 'download']);
});

// Order Management Routes
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('{orderNumber}', [OrderController::class, 'show']);
    Route::get('{orderNumber}/tracking', [OrderController::class, 'tracking']);
    Route::post('{orderNumber}/cancel', [OrderController::class, 'cancel']);
});

// Admin Order Fulfillment Routes
Route::prefix('admin/fulfillment')->middleware('auth:admin')->group(function () {
    Route::get('orders', [OrderFulfillmentController::class, 'index']);
    Route::post('orders/{orderNumber}/process', [OrderFulfillmentController::class, 'process']);
    Route::get('orders/{orderNumber}/picking-list', [OrderFulfillmentController::class, 'getPickingList']);
    Route::get('orders/{orderNumber}/packing-slip', [OrderFulfillmentController::class, 'getPackingSlip']);
    Route::get('orders/{orderNumber}/shipping-rates', [OrderFulfillmentController::class, 'getShippingRates']);
    Route::post('orders/{orderNumber}/mark-shipped', [OrderFulfillmentController::class, 'markShipped']);
    Route::post('orders/{orderNumber}/mark-delivered', [OrderFulfillmentController::class, 'markDelivered']);
    Route::post('orders/bulk-process', [OrderFulfillmentController::class, 'bulkProcess']);
});

// Return Management Routes
Route::prefix('returns')->group(function () {
    Route::get('history', [ReturnController::class, 'history']);
    Route::get('orders/{orderNumber}/eligibility', [ReturnController::class, 'checkEligibility']);
    Route::post('orders/{orderNumber}/request', [ReturnController::class, 'createRequest']);
    Route::get('orders/{orderNumber}/returns/{returnId}', [ReturnController::class, 'show']);
    Route::get('orders/{orderNumber}/returns/{returnId}/label', [ReturnController::class, 'getLabel']);
});

// Admin Order Management Routes
Route::prefix('admin/orders')->middleware('auth:admin')->group(function () {
    Route::get('/', [OrderManagementController::class, 'index']);
    Route::get('analytics', [OrderManagementController::class, 'analytics']);
    Route::get('export', [OrderManagementController::class, 'export']);
    Route::get('{orderNumber}', [OrderManagementController::class, 'show']);
    Route::post('{orderNumber}/status', [OrderManagementController::class, 'updateStatus']);
    Route::post('{orderNumber}/returns/{returnId}/process', [OrderManagementController::class, 'processReturn']);
});

// Shipping Routes
Route::prefix('shipping')->group(function () {
    Route::get('zones', [ShippingController::class, 'getZones']);
    Route::get('methods', [ShippingController::class, 'getMethods']);
    Route::get('orders/{orderNumber}/rates', [ShippingController::class, 'calculateRates']);
    Route::post('orders/{orderNumber}/label', [ShippingController::class, 'generateLabel']);
    Route::get('track/{carrier}/{trackingNumber}', [ShippingController::class, 'trackShipment']);
});

// Recommendation routes
Route::prefix('recommendations')->group(function () {
    Route::get('/cart', [RecommendationController::class, 'cartRecommendations']);
    Route::get('/wishlist', [RecommendationController::class, 'wishlistRecommendations']);
    Route::get('/products/{product}', [RecommendationController::class, 'productRecommendations']);
    Route::get('/personalized', [RecommendationController::class, 'personalizedRecommendations']);
    Route::get('/trending', [RecommendationController::class, 'trendingProducts']);
    Route::get('/products/{product}/frequently-bought-together', [RecommendationController::class, 'frequentlyBoughtTogether']);
});

// Admin dashboard routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/sales-trend', [DashboardController::class, 'salesTrend']);
    Route::get('/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/customer-growth', [DashboardController::class, 'customerGrowth']);
    Route::get('/inventory-alerts', [DashboardController::class, 'inventoryAlerts']);
    Route::get('/recent-activity', [DashboardController::class, 'recentActivity']);
});

// Admin analytics routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/analytics')->group(function () {
    Route::get('/sales', [AnalyticsController::class, 'salesAnalytics']);
    Route::get('/revenue-by-category', [AnalyticsController::class, 'revenueByCategory']);
    Route::get('/product-performance', [AnalyticsController::class, 'productPerformance']);
    Route::get('/customer-insights', [AnalyticsController::class, 'customerInsights']);
    Route::get('/payment-analytics', [AnalyticsController::class, 'paymentAnalytics']);
    Route::get('/export-sales-report', [AnalyticsController::class, 'exportSalesReport']);
});

// Admin customer management routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/customers')->group(function () {
    Route::get('/', [CustomerManagementController::class, 'index']);
    Route::get('/{customer}', [CustomerManagementController::class, 'show']);
    Route::put('/{customer}/status', [CustomerManagementController::class, 'updateStatus']);
    Route::get('/{customer}/orders', [CustomerManagementController::class, 'orderHistory']);
    Route::get('/{customer}/reviews', [CustomerManagementController::class, 'reviewHistory']);
    Route::get('/{customer}/activity', [CustomerManagementController::class, 'activityLog']);
    Route::get('/{customer}/export', [CustomerManagementController::class, 'export']);
});

// Admin banner management routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
    Route::post('/', [BannerController::class, 'store']);
    Route::put('/{banner}', [BannerController::class, 'update']);
    Route::delete('/{banner}', [BannerController::class, 'destroy']);
    Route::post('/positions', [BannerController::class, 'updatePositions']);
    Route::put('/{banner}/toggle-active', [BannerController::class, 'toggleActive']);
});

// Admin page management routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/pages')->group(function () {
    Route::get('/', [PageController::class, 'index']);
    Route::post('/', [PageController::class, 'store']);
    Route::get('/{page}', [PageController::class, 'show']);
    Route::put('/{page}', [PageController::class, 'update']);
    Route::delete('/{page}', [PageController::class, 'destroy']);
    Route::post('/positions', [PageController::class, 'updatePositions']);
    Route::get('/{page}/revisions', [PageController::class, 'revisions']);
    Route::get('/{page}/revisions/compare/{revision1}/{revision2}', [PageController::class, 'compareRevisions']);
    Route::post('/{page}/revisions/{revision}/restore', [PageController::class, 'restoreRevision']);
    Route::put('/{page}/toggle-active', [PageController::class, 'toggleActive']);
});

// Admin settings management routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/settings')->group(function () {
    Route::get('/', [SettingController::class, 'index']);
    Route::get('/{key}', [SettingController::class, 'show']);
    Route::put('/', [SettingController::class, 'update']);
    Route::delete('/{key}', [SettingController::class, 'destroy']);
    Route::get('/group/{group}', [SettingController::class, 'getGroup']);
    Route::post('/cache/clear', [SettingController::class, 'clearCache']);
    Route::post('/initialize', [SettingController::class, 'initializeDefaults']);
});