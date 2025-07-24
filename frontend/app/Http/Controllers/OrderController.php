namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderTrackingService $trackingService;

    public function __construct(OrderTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Get user's order history
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query();

        // Filter by user if authenticated
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } elseif ($request->has('guest_email')) {
            $query->where('guest_email', $request->guest_email);
        } else {
            return response()->json([
                'error' => 'Authentication required'
            ], 401);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort orders
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Paginate results
        $orders = $query->with(['items', 'shippingAddress'])
            ->paginate($request->get('per_page', 10));

        return response()->json($orders);
    }

    /**
     * Get order details
     */
    public function show(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'shippingAddress', 'billingAddress', 'statusHistory'])
            ->firstOrFail();

        // Verify order belongs to authenticated user if not guest
        if (auth()->check() && $order->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        // Get order timeline
        $timeline = $this->trackingService->getOrderTimeline($order);

        return response()->json([
            'order' => $order,
            'timeline' => $timeline
        ]);
    }

    /**
     * Get order tracking information
     */
    public function tracking(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['statusHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();

        // Verify order belongs to authenticated user if not guest
        if (auth()->check() && $order->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $tracking = [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'tracking_number' => $order->tracking_number,
            'estimated_delivery' => $order->estimated_delivery_date,
            'shipping_address' => $order->shippingAddress,
            'timeline' => $this->trackingService->getOrderTimeline($order)
        ];

        // If order is shipped, get real-time tracking info from shipping carrier
        if ($order->status === 'shipped' && $order->tracking_number) {
            try {
                $carrierTracking = $this->getCarrierTracking($order->tracking_number);
                $tracking['carrier_info'] = $carrierTracking;
            } catch (\Exception $e) {
                \Log::error('Failed to get carrier tracking', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json($tracking);
    }

    /**
     * Cancel order
     */
    public function cancel(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Verify order belongs to authenticated user if not guest
        if (auth()->check() && $order->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return response()->json([
                'error' => 'Order cannot be cancelled'
            ], 400);
        }

        try {
            \DB::transaction(function () use ($order) {
                // Update order status
                $this->trackingService->updateOrderStatus($order, 'cancelled', 'Order cancelled by customer');

                // Restore product stock
                foreach ($order->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }

                // If payment was made, initiate refund
                if ($order->payment_status === 'paid') {
                    // Handle refund based on payment method
                    switch ($order->payment_method) {
                        case 'stripe':
                            // Initiate Stripe refund
                            break;
                        case 'paypal':
                            // Initiate PayPal refund
                            break;
                    }
                }
            });

            return response()->json([
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to cancel order', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to cancel order'
            ], 500);
        }
    }

    /**
     * Get tracking information from shipping carrier
     */
    protected function getCarrierTracking(string $trackingNumber): array
    {
        // TODO: Implement carrier-specific tracking
        // This will be implemented when we integrate with specific carriers
        return [
            'status' => 'in_transit',
            'location' => 'Delhi Hub',
            'last_update' => now(),
            'estimated_delivery' => now()->addDays(2)
        ];
    }
} 