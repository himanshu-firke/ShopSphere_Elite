namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use App\Services\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderManagementController extends Controller
{
    protected OrderFulfillmentService $fulfillmentService;
    protected ReturnService $returnService;

    public function __construct(
        OrderFulfillmentService $fulfillmentService,
        ReturnService $returnService
    ) {
        $this->fulfillmentService = $fulfillmentService;
        $this->returnService = $returnService;
        $this->middleware('auth:admin');
    }

    /**
     * Get orders with filters and sorting
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::query();

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('shippingAddress', function ($q) use ($search) {
                            $q->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            }

            // Sort orders
            $sortField = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Paginate results
            $orders = $query->with([
                'user',
                'items.product',
                'shippingAddress',
                'billingAddress',
                'statusHistory' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])->paginate($request->get('per_page', 10));

            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('Failed to get orders', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get orders'
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function show(string $orderNumber): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)
                ->with([
                    'user',
                    'items.product',
                    'shippingAddress',
                    'billingAddress',
                    'statusHistory' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    },
                    'returns.items'
                ])
                ->firstOrFail();

            return response()->json($order);
        } catch (\Exception $e) {
            Log::error('Failed to get order details', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get order details'
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string',
                'comment' => 'nullable|string'
            ]);

            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            $order->updateStatus($request->status, $request->comment);

            return response()->json([
                'message' => 'Order status updated successfully',
                'order' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to update order status'
            ], 500);
        }
    }

    /**
     * Process return
     */
    public function processReturn(Request $request, string $orderNumber, string $returnId): JsonResponse
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|exists:order_items,id',
                'items.*.quantity' => 'required|integer|min:0',
                'items.*.condition' => 'required|string',
                'items.*.notes' => 'nullable|string'
            ]);

            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            $result = $this->returnService->processReturnReceipt(
                $order,
                $returnId,
                $request->items
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to process return', [
                'order' => $orderNumber,
                'return' => $returnId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to process return'
            ], 500);
        }
    }

    /**
     * Get order analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(30));
            $endDate = $request->get('end_date', now());

            $analytics = DB::table('orders')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('AVG(total_amount) as average_order_value'),
                    DB::raw('COUNT(DISTINCT user_id) as unique_customers'),
                    DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders'),
                    DB::raw('SUM(CASE WHEN status = "refunded" THEN 1 ELSE 0 END) as refunded_orders')
                )
                ->first();

            // Get daily orders and revenue
            $dailyStats = DB::table('orders')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get top selling products
            $topProducts = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_quantity')
                ->limit(10)
                ->get();

            return response()->json([
                'summary' => $analytics,
                'daily_stats' => $dailyStats,
                'top_products' => $topProducts
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get order analytics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get order analytics'
            ], 500);
        }
    }

    /**
     * Export orders
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $query = Order::query();

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

            $orders = $query->with([
                'user',
                'items.product',
                'shippingAddress',
                'billingAddress'
            ])->get();

            // Format orders for export
            $exportData = $orders->map(function ($order) {
                return [
                    'Order Number' => $order->order_number,
                    'Date' => $order->created_at->format('Y-m-d H:i:s'),
                    'Status' => $order->status,
                    'Customer' => $order->user ? $order->user->name : 'Guest',
                    'Email' => $order->user ? $order->user->email : $order->guest_email,
                    'Items' => $order->items->count(),
                    'Total Amount' => $order->total_amount,
                    'Payment Status' => $order->payment_status,
                    'Payment Method' => $order->payment_method,
                    'Shipping Address' => implode(', ', [
                        $order->shippingAddress->full_name,
                        $order->shippingAddress->address_line1,
                        $order->shippingAddress->city,
                        $order->shippingAddress->state,
                        $order->shippingAddress->postal_code
                    ])
                ];
            });

            return response()->json([
                'data' => $exportData,
                'filename' => 'orders_' . now()->format('Y-m-d_H-i-s') . '.csv'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export orders', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to export orders'
            ], 500);
        }
    }
} 