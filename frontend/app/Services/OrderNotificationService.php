namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(Order $order): void
    {
        try {
            $email = $order->user?->email ?? $order->guest_email;

            if (!$email) {
                throw new \Exception('No email address available for order notification');
            }

            Mail::send('emails.orders.confirmation', ['order' => $order], function ($message) use ($order, $email) {
                $message->to($email)
                    ->subject('Order Confirmation - ' . $order->order_number);
            });

            Log::info('Order confirmation email sent', [
                'order' => $order->order_number,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send order shipped email
     */
    public function sendOrderShipped(Order $order): void
    {
        try {
            $email = $order->user?->email ?? $order->guest_email;

            if (!$email) {
                throw new \Exception('No email address available for order notification');
            }

            Mail::send('emails.orders.shipped', ['order' => $order], function ($message) use ($order, $email) {
                $message->to($email)
                    ->subject('Your Order Has Shipped - ' . $order->order_number);
            });

            Log::info('Order shipped email sent', [
                'order' => $order->order_number,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order shipped email', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send order delivered email
     */
    public function sendOrderDelivered(Order $order): void
    {
        try {
            $email = $order->user?->email ?? $order->guest_email;

            if (!$email) {
                throw new \Exception('No email address available for order notification');
            }

            Mail::send('emails.orders.delivered', ['order' => $order], function ($message) use ($order, $email) {
                $message->to($email)
                    ->subject('Your Order Has Been Delivered - ' . $order->order_number);
            });

            Log::info('Order delivered email sent', [
                'order' => $order->order_number,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order delivered email', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send order cancelled email
     */
    public function sendOrderCancelled(Order $order): void
    {
        try {
            $email = $order->user?->email ?? $order->guest_email;

            if (!$email) {
                throw new \Exception('No email address available for order notification');
            }

            Mail::send('emails.orders.cancelled', ['order' => $order], function ($message) use ($order, $email) {
                $message->to($email)
                    ->subject('Order Cancelled - ' . $order->order_number);
            });

            Log::info('Order cancelled email sent', [
                'order' => $order->order_number,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order cancelled email', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send low stock alert to admin
     */
    public function sendLowStockAlert(array $products): void
    {
        try {
            $adminEmail = config('company.admin_email');

            Mail::send('emails.admin.low_stock', ['products' => $products], function ($message) use ($adminEmail) {
                $message->to($adminEmail)
                    ->subject('Low Stock Alert - Action Required');
            });

            Log::info('Low stock alert email sent', [
                'products' => array_map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'stock' => $product->stock
                    ];
                }, $products)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send low stock alert email', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send order refund notification
     */
    public function sendRefundNotification(Order $order, float $amount): void
    {
        try {
            $email = $order->user?->email ?? $order->guest_email;

            if (!$email) {
                throw new \Exception('No email address available for order notification');
            }

            Mail::send('emails.orders.refunded', [
                'order' => $order,
                'amount' => $amount
            ], function ($message) use ($order, $email) {
                $message->to($email)
                    ->subject('Refund Processed - Order ' . $order->order_number);
            });

            Log::info('Refund notification email sent', [
                'order' => $order->order_number,
                'email' => $email,
                'amount' => $amount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send refund notification email', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send order status update notification
     */
    public function sendStatusUpdateNotification(Order $order, string $oldStatus, string $newStatus): void
    {
        try {
            $email = $order->user?->email ?? $order->guest_email;

            if (!$email) {
                throw new \Exception('No email address available for order notification');
            }

            Mail::send('emails.orders.status_update', [
                'order' => $order,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ], function ($message) use ($order, $email) {
                $message->to($email)
                    ->subject('Order Status Update - ' . $order->order_number);
            });

            Log::info('Order status update email sent', [
                'order' => $order->order_number,
                'email' => $email,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send status update email', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 