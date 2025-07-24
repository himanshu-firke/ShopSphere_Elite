namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Create payment intent for an order
     */
    public function createPaymentIntent(Request $request): JsonResponse
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

        $result = $this->stripeService->createPaymentIntent($order);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error']
            ], 500);
        }

        return response()->json([
            'clientSecret' => $result['client_secret'],
            'paymentIntentId' => $result['payment_intent_id']
        ]);
    }

    /**
     * Handle successful payment confirmation
     */
    public function handlePaymentSuccess(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
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

            $this->stripeService->handleSuccessfulPayment(
                $request->payment_intent_id,
                $order
            );

            return response()->json([
                'success' => true,
                'order' => $order->fresh()->load('items')
            ]);
        } catch (\Exception $e) {
            Log::error('Payment success handling failed', [
                'error' => $e->getMessage(),
                'order' => $request->order_number
            ]);

            return response()->json([
                'error' => 'Failed to process payment confirmation'
            ], 500);
        }
    }

    /**
     * Handle Stripe webhooks
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        // Verify webhook signature
        $payload = $request->all();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Invalid signature'
            ], 400);
        }

        $result = $this->stripeService->handleWebhookEvent($payload);

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