<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get inventory list with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category', 'primaryImage'])
            ->select('products.*')
            ->selectRaw('CASE 
                WHEN stock_quantity <= low_stock_threshold THEN "low_stock"
                WHEN stock_quantity = 0 THEN "out_of_stock"
                ELSE "in_stock"
            END as stock_status');

        // Apply filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                        ->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', 0);
                    break;
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0)
                        ->whereColumn('stock_quantity', '>', 'low_stock_threshold');
                    break;
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'stock_quantity');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min($request->get('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Update stock quantity for a product
     */
    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity_change' => 'required|integer',
            'reason' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->inventoryService->updateStock(
            $product,
            $request->quantity_change,
            $request->reason,
            $request->reference
        );

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => [
                'product' => $product->fresh()
            ]
        ]);
    }

    /**
     * Bulk update stock for multiple products
     */
    public function bulkUpdateStock(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'updates' => 'required|array',
            'updates.*.product_id' => 'required|exists:products,id',
            'updates.*.quantity_change' => 'required|integer',
            'updates.*.reason' => 'required|string|max:255',
            'updates.*.reference' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = $this->inventoryService->bulkUpdateStock($request->updates);

        return response()->json([
            'success' => true,
            'message' => 'Bulk stock update completed',
            'data' => [
                'results' => $results
            ]
        ]);
    }

    /**
     * Get inventory history for a product
     */
    public function history(Product $product): JsonResponse
    {
        $history = $this->inventoryService->getInventoryHistory($product);

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'history' => $history
            ]
        ]);
    }

    /**
     * Get low stock alerts
     */
    public function lowStockAlerts(): JsonResponse
    {
        $lowStockProducts = $this->inventoryService->getLowStockProducts();

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $lowStockProducts
            ]
        ]);
    }

    /**
     * Update low stock threshold for a product
     */
    public function updateThreshold(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'threshold' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->inventoryService->updateLowStockThreshold(
            $product,
            $request->threshold
        );

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update threshold'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Threshold updated successfully',
            'data' => [
                'product' => $product->fresh()
            ]
        ]);
    }
} 