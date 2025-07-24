namespace App\Services;

use App\Models\Order;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    protected PaymentEncryptionService $encryptionService;
    protected PaymentAuditService $auditService;

    public function __construct(
        PaymentEncryptionService $encryptionService,
        PaymentAuditService $auditService
    ) {
        $this->encryptionService = $encryptionService;
        $this->auditService = $auditService;
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent for an order
     */
    public function createPaymentIntent(Order $order): array
    {
        try {
            $paymentData = [
                'amount' => $order->total_amount * 100, // Convert to cents
                'currency' => 'inr',
                'metadata' => [
                    'order_number' => $order->order_number,
                    'user_id' => $order->user_id ?? 'guest'
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ];

            // Log payment attempt
            $this->auditService->logPaymentAttempt($order, 'stripe', $paymentData);

            $paymentIntent = PaymentIntent::create($paymentData);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ];
        } catch (ApiErrorException $e) {
            // Log payment failure
            $this->auditService->logPaymentFailure($order, 'stripe', $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle successful payment
     */
    public function handleSuccessfulPayment(string $paymentIntentId, Order $order): void
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                $order->payment_status = 'paid';
                $order->status = 'processing';
                $order->save();

                // Record status change
                $order->updateStatus('processing', 'Payment received via Stripe');

                // Log payment success
                $this->auditService->logPaymentSuccess($order, 'stripe', $paymentIntentId);
            }
        } catch (ApiErrorException $e) {
            // Log payment failure
            $this->auditService->logPaymentFailure($order, 'stripe', $e->getMessage());

            \Log::error('Stripe payment verification failed', [
                'error' => $e->getMessage(),
                'order' => $order->order_number
            ]);
            throw $e;
        }
    }

    /**
     * Handle webhook events
     */
    public function handleWebhookEvent(array $payload): array
    {
        try {
            $event = \Stripe\Event::constructFrom($payload);

            // Log webhook event
            $this->auditService->logWebhookEvent('stripe', $event->type, $payload);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $orderNumber = $paymentIntent->metadata['order_number'];
                    
                    $order = Order::where('order_number', $orderNumber)->first();
                    if ($order) {
                        $this->handleSuccessfulPayment($paymentIntent->id, $order);
                    }
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    $orderNumber = $paymentIntent->metadata['order_number'];
                    
                    $order = Order::where('order_number', $orderNumber)->first();
                    if ($order) {
                        $order->payment_status = 'failed';
                        $order->save();
                        $order->updateStatus('pending', 'Payment failed via Stripe');

                        // Log payment failure
                        $this->auditService->logPaymentFailure($order, 'stripe', 'Payment failed');
                    }
                    break;
            }

            return [
                'success' => true,
                'message' => 'Webhook handled successfully'
            ];
        } catch (\Exception $e) {
            \Log::error('Stripe webhook handling failed', [
                'error' => $e->getMessage(),
                'event' => $payload['type'] ?? 'unknown'
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 