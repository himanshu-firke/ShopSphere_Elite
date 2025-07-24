namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnService
{
    protected OrderNotificationService $notificationService;
    protected StripeService $stripeService;
    protected PayPalService $paypalService;

    public function __construct(
        OrderNotificationService $notificationService,
        StripeService $stripeService,
        PayPalService $paypalService
    ) {
        $this->notificationService = $notificationService;
        $this->stripeService = $stripeService;
        $this->paypalService = $paypalService;
    }

    /**
     * Process return request
     */
    public function processReturnRequest(Order $order, array $items, string $reason): array
    {
        try {
            // Validate return eligibility
            if (!$this->canBeReturned($order)) {
                throw new \Exception('Order is not eligible for return');
            }

            // Start transaction
            return DB::transaction(function () use ($order, $items, $reason) {
                // Calculate refund amount
                $refundAmount = $this->calculateRefundAmount($order, $items);

                // Create return record
                $return = $order->returns()->create([
                    'reason' => $reason,
                    'status' => 'pending',
                    'refund_amount' => $refundAmount,
                    'requested_by' => auth()->id()
                ]);

                // Create return items
                foreach ($items as $item) {
                    $return->items()->create([
                        'order_item_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'reason' => $item['reason'] ?? $reason
                    ]);
                }

                // Generate return label
                $returnLabel = $this->generateReturnLabel($order, $return);

                // Update return with label
                $return->update([
                    'return_label' => $returnLabel,
                    'tracking_number' => $returnLabel['tracking_number']
                ]);

                return [
                    'success' => true,
                    'return' => $return->fresh(),
                    'refund_amount' => $refundAmount,
                    'return_label' => $returnLabel
                ];
            });
        } catch (\Exception $e) {
            Log::error('Failed to process return request', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Process refund for returned items
     */
    public function processRefund(Order $order, array $items, string $reason): array
    {
        try {
            // Calculate refund amount
            $refundAmount = $this->calculateRefundAmount($order, $items);

            // Process refund based on payment method
            switch ($order->payment_method) {
                case 'stripe':
                    $refundResult = $this->stripeService->processRefund($order, $refundAmount);
                    break;
                case 'paypal':
                    $refundResult = $this->paypalService->processRefund($order, $refundAmount);
                    break;
                default:
                    throw new \Exception('Unsupported payment method');
            }

            if (!$refundResult['success']) {
                throw new \Exception($refundResult['error'] ?? 'Failed to process refund');
            }

            // Update order status
            $order->processRefund($reason);

            // Send refund notification
            $this->notificationService->sendRefundNotification($order, $refundAmount);

            return [
                'success' => true,
                'refund_amount' => $refundAmount,
                'transaction_id' => $refundResult['transaction_id']
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process refund', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Check if order can be returned
     */
    protected function canBeReturned(Order $order): bool
    {
        // Check if order is delivered
        if ($order->status !== 'delivered') {
            return false;
        }

        // Check if within return window (30 days)
        if (now()->diffInDays($order->delivery_details['delivered_at']) > 30) {
            return false;
        }

        // Check if already returned
        if ($order->returns()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Calculate refund amount
     */
    protected function calculateRefundAmount(Order $order, array $items): float
    {
        $refundAmount = 0;

        foreach ($items as $item) {
            $orderItem = $order->items->firstWhere('id', $item['id']);
            if (!$orderItem) {
                throw new \Exception('Invalid order item');
            }

            if ($item['quantity'] > $orderItem->quantity) {
                throw new \Exception('Invalid return quantity');
            }

            $refundAmount += $orderItem->price * $item['quantity'];
        }

        // Add proportional tax
        $refundAmount += $refundAmount * 0.18; // 18% GST

        // Add shipping fee if all items are being returned
        $totalReturnQuantity = array_sum(array_column($items, 'quantity'));
        $totalOrderQuantity = $order->items->sum('quantity');
        if ($totalReturnQuantity === $totalOrderQuantity) {
            $refundAmount += $order->shipping_fee;
        }

        return round($refundAmount, 2);
    }

    /**
     * Generate return shipping label
     */
    protected function generateReturnLabel(Order $order, $return): array
    {
        // TODO: Integrate with shipping carrier API
        // For now, return dummy label data
        return [
            'tracking_number' => 'RET-' . strtoupper(uniqid()),
            'carrier' => 'DTDC',
            'label_url' => 'https://example.com/labels/dummy.pdf',
            'instructions' => 'Please pack items securely and attach this label to the package.',
            'return_address' => [
                'name' => config('company.name'),
                'address' => config('company.address'),
                'phone' => config('company.phone')
            ]
        ];
    }

    /**
     * Process return receipt
     */
    public function processReturnReceipt(Order $order, string $returnId, array $receivedItems): array
    {
        try {
            $return = $order->returns()->findOrFail($returnId);

            // Start transaction
            return DB::transaction(function () use ($order, $return, $receivedItems) {
                // Update received items
                foreach ($receivedItems as $item) {
                    $returnItem = $return->items()->where('order_item_id', $item['id'])->first();
                    if (!$returnItem) {
                        throw new \Exception('Invalid return item');
                    }

                    $returnItem->update([
                        'received_quantity' => $item['quantity'],
                        'condition' => $item['condition'],
                        'notes' => $item['notes'] ?? null
                    ]);

                    // Restore product stock
                    $orderItem = OrderItem::find($item['id']);
                    $orderItem->product->increment('stock', $item['quantity']);
                }

                // Calculate actual refund amount based on received items
                $refundAmount = $this->calculateRefundAmount($order, $receivedItems);

                // Process refund
                $refundResult = $this->processRefund($order, $receivedItems, 'Return received and verified');

                // Update return status
                $return->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'actual_refund_amount' => $refundAmount
                ]);

                return [
                    'success' => true,
                    'return' => $return->fresh(),
                    'refund_amount' => $refundAmount,
                    'refund_transaction' => $refundResult['transaction_id']
                ];
            });
        } catch (\Exception $e) {
            Log::error('Failed to process return receipt', [
                'order' => $order->order_number,
                'return' => $returnId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
} 