namespace App\Services;

use App\Models\Order;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected PayPalClient $paypalClient;
    protected PaymentEncryptionService $encryptionService;
    protected PaymentAuditService $auditService;

    public function __construct(
        PaymentEncryptionService $encryptionService,
        PaymentAuditService $auditService
    ) {
        $this->encryptionService = $encryptionService;
        $this->auditService = $auditService;
        $this->paypalClient = new PayPalClient;
        $this->paypalClient->setApiCredentials(config('paypal'));
        $this->paypalClient->setAccessToken($this->paypalClient->getAccessToken());
    }

    /**
     * Create PayPal order
     */
    public function createOrder(Order $order): array
    {
        try {
            $paymentData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $order->order_number,
                        'amount' => [
                            'currency_code' => 'INR',
                            'value' => number_format($order->total_amount, 2, '.', ''),
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => 'INR',
                                    'value' => number_format($order->subtotal, 2, '.', '')
                                ],
                                'tax_total' => [
                                    'currency_code' => 'INR',
                                    'value' => number_format($order->tax, 2, '.', '')
                                ],
                                'shipping' => [
                                    'currency_code' => 'INR',
                                    'value' => number_format($order->shipping_fee, 2, '.', '')
                                ]
                            ]
                        ],
                        'items' => $this->formatOrderItems($order),
                        'shipping' => [
                            'address' => $this->formatShippingAddress($order->shippingAddress)
                        ]
                    ]
                ],
                'application_context' => [
                    'return_url' => route('paypal.success'),
                    'cancel_url' => route('paypal.cancel')
                ]
            ];

            // Log payment attempt
            $this->auditService->logPaymentAttempt($order, 'paypal', $paymentData);

            $paypalOrder = $this->paypalClient->createOrder($paymentData);

            return [
                'success' => true,
                'order_id' => $paypalOrder['id']
            ];
        } catch (\Exception $e) {
            // Log payment failure
            $this->auditService->logPaymentFailure($order, 'paypal', $e->getMessage());

            Log::error('PayPal order creation failed', [
                'error' => $e->getMessage(),
                'order' => $order->order_number
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Capture PayPal payment
     */
    public function capturePayment(string $paypalOrderId, Order $order): array
    {
        try {
            $result = $this->paypalClient->capturePaymentOrder($paypalOrderId);

            if ($result['status'] === 'COMPLETED') {
                $order->payment_status = 'paid';
                $order->status = 'processing';
                $order->save();

                // Record status change
                $order->updateStatus('processing', 'Payment received via PayPal');

                // Log payment success
                $this->auditService->logPaymentSuccess(
                    $order,
                    'paypal',
                    $result['purchase_units'][0]['payments']['captures'][0]['id']
                );

                return [
                    'success' => true,
                    'transaction_id' => $result['purchase_units'][0]['payments']['captures'][0]['id']
                ];
            }

            // Log payment failure
            $this->auditService->logPaymentFailure($order, 'paypal', 'Payment not completed');

            return [
                'success' => false,
                'error' => 'Payment not completed'
            ];
        } catch (\Exception $e) {
            // Log payment failure
            $this->auditService->logPaymentFailure($order, 'paypal', $e->getMessage());

            Log::error('PayPal payment capture failed', [
                'error' => $e->getMessage(),
                'order' => $order->order_number
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle webhook events
     */
    public function handleWebhookEvent(array $payload): array
    {
        try {
            $eventType = $payload['event_type'];
            $resource = $payload['resource'];

            // Log webhook event
            $this->auditService->logWebhookEvent('paypal', $eventType, $payload);

            switch ($eventType) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $orderNumber = $resource['custom_id'] ?? null;
                    if ($orderNumber) {
                        $order = Order::where('order_number', $orderNumber)->first();
                        if ($order && $order->payment_status !== 'paid') {
                            $order->payment_status = 'paid';
                            $order->status = 'processing';
                            $order->save();
                            $order->updateStatus('processing', 'Payment completed via PayPal webhook');

                            // Log payment success
                            $this->auditService->logPaymentSuccess($order, 'paypal', $resource['id']);
                        }
                    }
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.DECLINED':
                    $orderNumber = $resource['custom_id'] ?? null;
                    if ($orderNumber) {
                        $order = Order::where('order_number', $orderNumber)->first();
                        if ($order) {
                            $order->payment_status = 'failed';
                            $order->save();
                            $order->updateStatus('pending', 'Payment failed via PayPal');

                            // Log payment failure
                            $this->auditService->logPaymentFailure($order, 'paypal', 'Payment failed');
                        }
                    }
                    break;
            }

            return [
                'success' => true,
                'message' => 'Webhook handled successfully'
            ];
        } catch (\Exception $e) {
            Log::error('PayPal webhook handling failed', [
                'error' => $e->getMessage(),
                'event' => $payload['event_type'] ?? 'unknown'
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format order items for PayPal
     */
    protected function formatOrderItems(Order $order): array
    {
        return $order->items->map(function ($item) {
            return [
                'name' => $item->product_name,
                'sku' => $item->product_sku,
                'unit_amount' => [
                    'currency_code' => 'INR',
                    'value' => number_format($item->price, 2, '.', '')
                ],
                'quantity' => $item->quantity,
                'category' => 'PHYSICAL_GOODS'
            ];
        })->toArray();
    }

    /**
     * Format shipping address for PayPal
     */
    protected function formatShippingAddress($address): array
    {
        return [
            'address_line_1' => $address->address_line1,
            'address_line_2' => $address->address_line2 ?? '',
            'admin_area_2' => $address->city,
            'admin_area_1' => $address->state,
            'postal_code' => $address->postal_code,
            'country_code' => 'IN', // Assuming India
        ];
    }
} 