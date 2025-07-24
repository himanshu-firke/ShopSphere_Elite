namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PayPalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalController extends Controller
{
    protected PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Create PayPal order
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string|exists:orders,order_number'
        ]);

        $order = Order::where('order_number', $request->order_number)->firstOrFail();

        // Verify order belongs to authenticated user if not guest
        if (auth()->check() && $order->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        // Verify order is in pending status
        if ($order->payment_status !== 'pending') {
            return response()->json([
                'error' => 'Order is already paid or cancelled'
            ], 400);
        }

        $result = $this->paypalService->createOrder($order);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error']
            ], 500);
        }

        return response()->json([
            'success' => true,
            'order_id' => $result['order_id']
        ]);
    }

    /**
     * Handle successful payment
     */
    public function handleSuccess(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string',
            'order_number' => 'required|string|exists:orders,order_number'
        ]);

        try {
            $order = Order::where('order_number', $request->order_number)->firstOrFail();

            // Verify order belongs to authenticated user if not guest
            if (auth()->check() && $order->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $result = $this->paypalService->capturePayment(
                $request->order_id,
                $order
            );

            if (!$result['success']) {
                return response()->json([
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'order' => $order->fresh()->load('items'),
                'transaction_id' => $result['transaction_id']
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal success handling failed', [
                'error' => $e->getMessage(),
                'order' => $request->order_number
            ]);

            return response()->json([
                'error' => 'Failed to process payment'
            ], 500);
        }
    }

    /**
     * Handle cancelled payment
     */
    public function handleCancel(Request $request): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string|exists:orders,order_number'
        ]);

        $order = Order::where('order_number', $request->order_number)->firstOrFail();

        // Update order status
        $order->payment_status = 'cancelled';
        $order->save();
        $order->updateStatus('pending', 'Payment cancelled by user');

        return response()->json([
            'success' => true,
            'message' => 'Payment cancelled'
        ]);
    }

    /**
     * Handle PayPal webhooks
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        // Verify webhook signature
        $webhookId = config('paypal.webhook_id');
        try {
            $this->paypalService->paypalClient->verifyWebhook(
                $request->headers->all(),
                $request->getContent(),
                $webhookId
            );
        } catch (\Exception $e) {
            Log::error('PayPal webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Invalid signature'
            ], 400);
        }

        $result = $this->paypalService->handleWebhookEvent($request->all());

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error']
            ], 500);
        }

        return response()->json([
            'success' => true
        ]);
    }
} 