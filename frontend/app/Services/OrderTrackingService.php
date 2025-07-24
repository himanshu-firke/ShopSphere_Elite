namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class OrderTrackingService
{
    /**
     * Update order status and tracking information
     */
    public function updateOrderStatus(Order $order, string $status, ?string $comment = null): void
    {
        $order->updateStatus($status, $comment);

        // Update estimated delivery date based on status
        switch ($status) {
            case 'processing':
                $order->estimated_delivery_date = Carbon::now()->addDays(5);
                break;
            case 'shipped':
                $order->estimated_delivery_date = Carbon::now()->addDays(3);
                break;
            case 'out_for_delivery':
                $order->estimated_delivery_date = Carbon::now()->addHours(12);
                break;
        }

        $order->save();
    }

    /**
     * Update order tracking number
     */
    public function updateTrackingNumber(Order $order, string $trackingNumber, string $carrier = null): void
    {
        $order->tracking_number = $trackingNumber;
        $order->shipping_carrier = $carrier;
        $order->save();

        $order->updateStatus('shipped', 'Order shipped with tracking number: ' . $trackingNumber);
    }

    /**
     * Get estimated delivery date
     */
    public function calculateEstimatedDelivery(Order $order): Carbon
    {
        // Base delivery time (3-5 business days)
        $estimatedDays = 4;

        // Add extra days based on location and shipping method
        if ($order->shippingAddress) {
            // Add extra days for remote locations
            if ($this->isRemoteLocation($order->shippingAddress)) {
                $estimatedDays += 2;
            }

            // Adjust based on shipping method
            switch ($order->shipping_method) {
                case 'express':
                    $estimatedDays = max(2, $estimatedDays - 2);
                    break;
                case 'standard':
                    // No adjustment
                    break;
                case 'economy':
                    $estimatedDays += 2;
                    break;
            }
        }

        return Carbon::now()->addWeekdays($estimatedDays);
    }

    /**
     * Check if location is considered remote
     */
    protected function isRemoteLocation($address): bool
    {
        // List of remote locations/states that require extra delivery time
        $remoteLocations = [
            'Andaman and Nicobar Islands',
            'Lakshadweep',
            'Ladakh',
            'Arunachal Pradesh',
            'Sikkim',
            'Nagaland',
            'Manipur',
            'Mizoram'
        ];

        return in_array($address->state, $remoteLocations);
    }

    /**
     * Get order timeline events
     */
    public function getOrderTimeline(Order $order): array
    {
        $timeline = [];

        // Add order placed event
        $timeline[] = [
            'status' => 'order_placed',
            'title' => 'Order Placed',
            'description' => 'Your order has been placed successfully',
            'timestamp' => $order->created_at,
            'completed' => true
        ];

        // Add payment event
        if ($order->payment_status === 'paid') {
            $timeline[] = [
                'status' => 'payment_confirmed',
                'title' => 'Payment Confirmed',
                'description' => 'Payment has been received',
                'timestamp' => $order->updated_at,
                'completed' => true
            ];
        }

        // Add processing event
        if (in_array($order->status, ['processing', 'shipped', 'delivered'])) {
            $timeline[] = [
                'status' => 'processing',
                'title' => 'Order Processing',
                'description' => 'Your order is being prepared',
                'timestamp' => $order->statusHistory()
                    ->where('to_status', 'processing')
                    ->first()?->created_at,
                'completed' => true
            ];
        }

        // Add shipped event
        if (in_array($order->status, ['shipped', 'delivered'])) {
            $timeline[] = [
                'status' => 'shipped',
                'title' => 'Order Shipped',
                'description' => 'Your order has been shipped',
                'timestamp' => $order->statusHistory()
                    ->where('to_status', 'shipped')
                    ->first()?->created_at,
                'tracking_number' => $order->tracking_number,
                'completed' => true
            ];
        }

        // Add out for delivery event
        if (in_array($order->status, ['out_for_delivery', 'delivered'])) {
            $timeline[] = [
                'status' => 'out_for_delivery',
                'title' => 'Out for Delivery',
                'description' => 'Your order is out for delivery',
                'timestamp' => $order->statusHistory()
                    ->where('to_status', 'out_for_delivery')
                    ->first()?->created_at,
                'completed' => true
            ];
        }

        // Add delivered event
        if ($order->status === 'delivered') {
            $timeline[] = [
                'status' => 'delivered',
                'title' => 'Order Delivered',
                'description' => 'Your order has been delivered',
                'timestamp' => $order->statusHistory()
                    ->where('to_status', 'delivered')
                    ->first()?->created_at,
                'completed' => true
            ];
        }

        // Add cancelled event if applicable
        if ($order->status === 'cancelled') {
            $timeline[] = [
                'status' => 'cancelled',
                'title' => 'Order Cancelled',
                'description' => 'Your order has been cancelled',
                'timestamp' => $order->statusHistory()
                    ->where('to_status', 'cancelled')
                    ->first()?->created_at,
                'completed' => true
            ];
        }

        // Sort timeline by timestamp
        usort($timeline, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        return $timeline;
    }
} 