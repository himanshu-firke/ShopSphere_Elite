namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderFulfillmentController extends Controller
{
    protected OrderFulfillmentService $fulfillmentService;

    public function __construct(OrderFulfillmentService $fulfillmentService)
    {
        $this->fulfillmentService = $fulfillmentService;
        $this->middleware('auth:admin');
    }

    /**
     * Get orders ready for fulfillment
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()
            ->where('payment_status', 'paid')
            ->where('status', 'pending')
            ->whereNull('fulfillment_data');

        // Apply filters
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
        $orders = $query->with(['items.product', 'shippingAddress'])
            ->paginate($request->get('per_page', 10));

        return response()->json($orders);
    }

    /**
     * Process order for fulfillment
     */
    public function process(string $orderNumber): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            $this->fulfillmentService->processOrder($order);

            return response()->json([
                'message' => 'Order processed for fulfillment successfully',
                'order' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process order', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to process order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get picking list for an order
     */
    public function getPickingList(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'shippingAddress'])
            ->firstOrFail();

        if (!$order->fulfillment_data) {
            return response()->json([
                'error' => 'Order not processed for fulfillment'
            ], 400);
        }

        return response()->json($order->fulfillment_data['picking_list']);
    }

    /**
     * Get packing slip for an order
     */
    public function getPackingSlip(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'shippingAddress'])
            ->firstOrFail();

        if (!$order->fulfillment_data) {
            return response()->json([
                'error' => 'Order not processed for fulfillment'
            ], 400);
        }

        return response()->json($order->fulfillment_data['packing_slip']);
    }

    /**
     * Mark order as shipped
     */
    public function markShipped(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $request->validate([
                'tracking_number' => 'required|string',
                'carrier' => 'required|string',
                'shipping_method' => 'required|string',
                'shipping_cost' => 'required|numeric'
            ]);

            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            $this->fulfillmentService->markAsShipped($order, 
                $request->tracking_number,
                $request->carrier,
                [
                    'method' => $request->shipping_method,
                    'cost' => $request->shipping_cost,
                    'notes' => $request->notes
                ]
            );

            return response()->json([
                'message' => 'Order marked as shipped successfully',
                'order' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark order as shipped', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to mark order as shipped',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark order as delivered
     */
    public function markDelivered(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $request->validate([
                'signed_by' => 'nullable|string',
                'notes' => 'nullable|string'
            ]);

            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            $this->fulfillmentService->markAsDelivered($order, [
                'signed_by' => $request->signed_by,
                'notes' => $request->notes,
                'delivery_photo' => $request->delivery_photo
            ]);

            return response()->json([
                'message' => 'Order marked as delivered successfully',
                'order' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark order as delivered', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to mark order as delivered',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipping rates for an order
     */
    public function getShippingRates(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'shippingAddress'])
            ->firstOrFail();

        if (!$order->fulfillment_data) {
            return response()->json([
                'error' => 'Order not processed for fulfillment'
            ], 400);
        }

        return response()->json($order->fulfillment_data['shipping_rates']);
    }

    /**
     * Process multiple orders for fulfillment
     */
    public function bulkProcess(Request $request): JsonResponse
    {
        $request->validate([
            'order_numbers' => 'required|array',
            'order_numbers.*' => 'required|string'
        ]);

        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($request->order_numbers as $orderNumber) {
            try {
                $order = Order::where('order_number', $orderNumber)->first();
                
                if (!$order) {
                    $results['failed'][] = [
                        'order_number' => $orderNumber,
                        'error' => 'Order not found'
                    ];
                    continue;
                }

                $this->fulfillmentService->processOrder($order);
                $results['success'][] = $orderNumber;
            } catch (\Exception $e) {
                Log::error('Failed to process order in bulk operation', [
                    'order' => $orderNumber,
                    'error' => $e->getMessage()
                ]);

                $results['failed'][] = [
                    'order_number' => $orderNumber,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json($results);
    }
} 