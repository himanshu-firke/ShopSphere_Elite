<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Update product stock quantity
     */
    public function updateStock(Product $product, int $quantity, string $reason = '', ?string $reference = null): bool
    {
        try {
            DB::beginTransaction();

            // Calculate new stock quantity
            $newQuantity = $product->stock_quantity + $quantity;

            // Log stock change
            $this->logStockChange($product, $quantity, $newQuantity, $reason, $reference);

            // Update product stock
            $product->update([
                'stock_quantity' => $newQuantity
            ]);

            // Check low stock threshold
            if ($this->isLowStock($product)) {
                $this->handleLowStockAlert($product);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update stock: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'reason' => $reason
            ]);
            return false;
        }
    }

    /**
     * Check if product is low in stock
     */
    public function isLowStock(Product $product): bool
    {
        return $product->stock_quantity <= $product->low_stock_threshold;
    }

    /**
     * Handle low stock alert
     */
    private function handleLowStockAlert(Product $product): void
    {
        // Log low stock alert
        Log::warning('Product low in stock', [
            'product_id' => $product->id,
            'name' => $product->name,
            'current_stock' => $product->stock_quantity,
            'threshold' => $product->low_stock_threshold
        ]);

        // TODO: Implement notification system for low stock alerts
        // This could include:
        // - Email notifications to admin/vendor
        // - Push notifications
        // - Dashboard alerts
    }

    /**
     * Log stock change in inventory history
     */
    private function logStockChange(
        Product $product,
        int $quantity,
        int $newQuantity,
        string $reason,
        ?string $reference
    ): void {
        DB::table('inventory_history')->insert([
            'product_id' => $product->id,
            'quantity_change' => $quantity,
            'new_quantity' => $newQuantity,
            'reason' => $reason,
            'reference' => $reference,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get inventory history for a product
     */
    public function getInventoryHistory(Product $product, int $limit = 50): array
    {
        return DB::table('inventory_history')
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $limit = 50): array
    {
        return Product::query()
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->with(['category', 'primaryImage'])
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Update low stock threshold for a product
     */
    public function updateLowStockThreshold(Product $product, int $threshold): bool
    {
        try {
            $product->update([
                'low_stock_threshold' => $threshold
            ]);

            // Check if product is now considered low in stock with new threshold
            if ($this->isLowStock($product)) {
                $this->handleLowStockAlert($product);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update low stock threshold: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'threshold' => $threshold
            ]);
            return false;
        }
    }

    /**
     * Reserve stock for an order
     */
    public function reserveStock(Product $product, int $quantity): bool
    {
        if ($product->stock_quantity < $quantity) {
            return false;
        }

        return $this->updateStock(
            $product,
            -$quantity,
            'Stock reserved for order',
            'order_pending'
        );
    }

    /**
     * Release reserved stock (e.g., when order is cancelled)
     */
    public function releaseReservedStock(Product $product, int $quantity): bool
    {
        return $this->updateStock(
            $product,
            $quantity,
            'Reserved stock released',
            'order_cancelled'
        );
    }

    /**
     * Bulk update stock for multiple products
     */
    public function bulkUpdateStock(array $updates): array
    {
        $results = [];

        foreach ($updates as $update) {
            $product = Product::find($update['product_id']);
            if (!$product) {
                $results[$update['product_id']] = false;
                continue;
            }

            $results[$update['product_id']] = $this->updateStock(
                $product,
                $update['quantity'],
                $update['reason'] ?? '',
                $update['reference'] ?? null
            );
        }

        return $results;
    }
} 