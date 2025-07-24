namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentAuditService
{
    protected PaymentEncryptionService $encryptionService;

    public function __construct(PaymentEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * Log payment attempt
     */
    public function logPaymentAttempt(Order $order, string $paymentMethod, array $paymentData): void
    {
        $maskedData = $this->encryptionService->maskSensitiveData($paymentData);

        Log::channel('payment')->info('Payment attempt', [
            'order_number' => $order->order_number,
            'user_id' => $order->user_id ?? 'guest',
            'payment_method' => $paymentMethod,
            'amount' => $order->total_amount,
            'currency' => 'INR',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payment_data' => $maskedData,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Log payment success
     */
    public function logPaymentSuccess(Order $order, string $paymentMethod, string $transactionId): void
    {
        Log::channel('payment')->info('Payment successful', [
            'order_number' => $order->order_number,
            'user_id' => $order->user_id ?? 'guest',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'amount' => $order->total_amount,
            'currency' => 'INR',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Log payment failure
     */
    public function logPaymentFailure(Order $order, string $paymentMethod, string $errorMessage): void
    {
        Log::channel('payment')->error('Payment failed', [
            'order_number' => $order->order_number,
            'user_id' => $order->user_id ?? 'guest',
            'payment_method' => $paymentMethod,
            'amount' => $order->total_amount,
            'currency' => 'INR',
            'error' => $errorMessage,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(Order $order, string $reason, array $context = []): void
    {
        Log::channel('payment')->warning('Suspicious payment activity detected', [
            'order_number' => $order->order_number,
            'user_id' => $order->user_id ?? 'guest',
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $context,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Log webhook event
     */
    public function logWebhookEvent(string $provider, string $eventType, array $payload): void
    {
        $maskedPayload = $this->maskWebhookPayload($payload);

        Log::channel('payment')->info('Payment webhook received', [
            'provider' => $provider,
            'event_type' => $eventType,
            'payload' => $maskedPayload,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Mask sensitive data in webhook payload
     */
    protected function maskWebhookPayload(array $payload): array
    {
        // Remove or mask sensitive data
        $sensitiveKeys = [
            'card',
            'source',
            'payment_method_details',
            'billing_details',
            'customer',
            'payer'
        ];

        return array_filter($payload, function ($key) use ($sensitiveKeys) {
            return !in_array($key, $sensitiveKeys);
        }, ARRAY_FILTER_USE_KEY);
    }
} 