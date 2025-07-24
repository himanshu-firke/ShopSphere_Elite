namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderFulfillmentService
{
    protected OrderTrackingService $trackingService;

    public function __construct(OrderTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Process order for fulfillment
     */
    public function processOrder(Order $order): void
    {
        try {
            // Verify order is ready for fulfillment
            if (!$this->canProcessOrder($order)) {
                throw new \Exception('Order is not ready for fulfillment');
            }

            // Update order status
            $this->trackingService->updateOrderStatus($order, 'processing', 'Order is being prepared for shipping');

            // Generate picking list
            $pickingList = $this->generatePickingList($order);

            // Generate packing slip
            $packingSlip = $this->generatePackingSlip($order);

            // Calculate shipping rates
            $shippingRates = $this->calculateShippingRates($order);

            // Store fulfillment details
            $order->fulfillment_data = [
                'picking_list' => $pickingList,
                'packing_slip' => $packingSlip,
                'shipping_rates' => $shippingRates,
                'processed_at' => now()
            ];
            $order->save();

            // Log fulfillment start
            Log::info('Order processed for fulfillment', [
                'order' => $order->order_number,
                'shipping_rates' => $shippingRates
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process order for fulfillment', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check if order can be processed for fulfillment
     */
    protected function canProcessOrder(Order $order): bool
    {
        return $order->payment_status === 'paid' &&
               $order->status === 'pending' &&
               !$order->fulfillment_data;
    }

    /**
     * Generate picking list for warehouse staff
     */
    protected function generatePickingList(Order $order): array
    {
        $pickingList = [];

        foreach ($order->items as $item) {
            $pickingList[] = [
                'product_id' => $item->product_id,
                'sku' => $item->product_sku,
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'location' => $item->product->warehouse_location ?? 'Unknown',
                'barcode' => $item->product->barcode ?? null,
                'notes' => $item->product_options ?? null
            ];
        }

        return [
            'order_number' => $order->order_number,
            'created_at' => now(),
            'items' => $pickingList,
            'special_instructions' => $order->notes
        ];
    }

    /**
     * Generate packing slip for the order
     */
    protected function generatePackingSlip(Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'created_at' => now(),
            'shipping_address' => [
                'name' => $order->shippingAddress->full_name,
                'address_line1' => $order->shippingAddress->address_line1,
                'address_line2' => $order->shippingAddress->address_line2,
                'city' => $order->shippingAddress->city,
                'state' => $order->shippingAddress->state,
                'postal_code' => $order->shippingAddress->postal_code,
                'country' => $order->shippingAddress->country,
                'phone' => $order->shippingAddress->phone
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'sku' => $item->product_sku,
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'options' => $item->product_options
                ];
            })->toArray(),
            'special_instructions' => $order->notes,
            'fragile' => $this->hasFragileItems($order)
        ];
    }

    /**
     * Calculate shipping rates for the order
     */
    protected function calculateShippingRates(Order $order): array
    {
        // TODO: Integrate with actual shipping carriers
        // For now, return dummy rates
        return [
            [
                'carrier' => 'DTDC',
                'service' => 'Express',
                'rate' => 250,
                'estimated_days' => 2
            ],
            [
                'carrier' => 'DTDC',
                'service' => 'Standard',
                'rate' => 150,
                'estimated_days' => 4
            ],
            [
                'carrier' => 'Delhivery',
                'service' => 'Express',
                'rate' => 300,
                'estimated_days' => 2
            ],
            [
                'carrier' => 'Delhivery',
                'service' => 'Standard',
                'rate' => 200,
                'estimated_days' => 3
            ]
        ];
    }

    /**
     * Check if order has fragile items
     */
    protected function hasFragileItems(Order $order): bool
    {
        foreach ($order->items as $item) {
            if ($item->product->is_fragile ?? false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped(Order $order, string $trackingNumber, string $carrier, array $shippingDetails = []): void
    {
        try {
            // Update tracking information
            $this->trackingService->updateTrackingNumber($order, $trackingNumber, $carrier);

            // Update shipping details
            $order->shipping_details = array_merge([
                'carrier' => $carrier,
                'tracking_number' => $trackingNumber,
                'shipped_at' => now(),
                'shipping_method' => $shippingDetails['method'] ?? 'standard',
                'estimated_delivery' => $this->trackingService->calculateEstimatedDelivery($order)
            ], $shippingDetails);

            $order->save();

            // Log shipping
            Log::info('Order marked as shipped', [
                'order' => $order->order_number,
                'carrier' => $carrier,
                'tracking' => $trackingNumber
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark order as shipped', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(Order $order, array $deliveryDetails = []): void
    {
        try {
            // Update order status
            $this->trackingService->updateOrderStatus($order, 'delivered', 'Order has been delivered');

            // Update delivery details
            $order->delivery_details = array_merge([
                'delivered_at' => now(),
                'signed_by' => $deliveryDetails['signed_by'] ?? null,
                'delivery_notes' => $deliveryDetails['notes'] ?? null
            ], $deliveryDetails);

            $order->save();

            // Log delivery
            Log::info('Order marked as delivered', [
                'order' => $order->order_number,
                'details' => $deliveryDetails
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark order as delivered', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 