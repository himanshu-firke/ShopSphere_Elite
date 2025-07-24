namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    protected ReturnService $returnService;

    public function __construct(ReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Get return eligibility for an order
     */
    public function checkEligibility(string $orderNumber): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)
                ->with(['items', 'returns'])
                ->firstOrFail();

            // Verify order belongs to authenticated user if not guest
            if (auth()->check() && $order->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $eligible = $this->returnService->canBeReturned($order);

            return response()->json([
                'eligible' => $eligible,
                'order' => $order,
                'return_window' => now()->diffInDays($order->delivery_details['delivered_at']),
                'return_window_remaining' => max(0, 30 - now()->diffInDays($order->delivery_details['delivered_at']))
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check return eligibility', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to check return eligibility'
            ], 500);
        }
    }

    /**
     * Create return request
     */
    public function createRequest(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|exists:order_items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.reason' => 'nullable|string',
                'reason' => 'required|string'
            ]);

            $order = Order::where('order_number', $orderNumber)
                ->with(['items', 'returns'])
                ->firstOrFail();

            // Verify order belongs to authenticated user if not guest
            if (auth()->check() && $order->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $result = $this->returnService->processReturnRequest(
                $order,
                $request->items,
                $request->reason
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to create return request', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get return details
     */
    public function show(string $orderNumber, string $returnId): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            // Verify order belongs to authenticated user if not guest
            if (auth()->check() && $order->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $return = $order->returns()
                ->with(['items.orderItem', 'requestedBy'])
                ->findOrFail($returnId);

            return response()->json($return);
        } catch (\Exception $e) {
            Log::error('Failed to get return details', [
                'order' => $orderNumber,
                'return' => $returnId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get return details'
            ], 500);
        }
    }

    /**
     * Get return label
     */
    public function getLabel(string $orderNumber, string $returnId): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            // Verify order belongs to authenticated user if not guest
            if (auth()->check() && $order->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $return = $order->returns()->findOrFail($returnId);

            if (!$return->return_label) {
                return response()->json([
                    'error' => 'Return label not found'
                ], 404);
            }

            return response()->json($return->return_label);
        } catch (\Exception $e) {
            Log::error('Failed to get return label', [
                'order' => $orderNumber,
                'return' => $returnId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get return label'
            ], 500);
        }
    }

    /**
     * Get return history for a user
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $query = Order::query()
                ->whereHas('returns')
                ->with(['returns.items', 'shippingAddress']);

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
                $query->whereHas('returns', function ($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            if ($request->has('date_from')) {
                $query->whereHas('returns', function ($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->date_from);
                });
            }

            if ($request->has('date_to')) {
                $query->whereHas('returns', function ($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->date_to);
                });
            }

            // Sort returns
            $sortField = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Paginate results
            $returns = $query->paginate($request->get('per_page', 10));

            return response()->json($returns);
        } catch (\Exception $e) {
            Log::error('Failed to get return history', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get return history'
            ], 500);
        }
    }
} 