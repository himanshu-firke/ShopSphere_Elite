namespace App\Models;

use App\Services\OrderNotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total_amount',
        'subtotal',
        'tax',
        'shipping_fee',
        'shipping_address_id',
        'billing_address_id',
        'payment_method',
        'payment_status',
        'notes',
        'tracking_number',
        'estimated_delivery_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'estimated_delivery_date' => 'datetime',
        'fulfillment_data' => 'array',
        'shipping_details' => 'array',
        'delivery_details' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'billing_address_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    // Methods
    public function updateStatus(string $status, ?string $comment = null): void
    {
        $oldStatus = $this->status;
        $this->status = $status;
        $this->save();

        // Record status change in history
        $this->statusHistory()->create([
            'from_status' => $oldStatus,
            'to_status' => $status,
            'comment' => $comment,
            'changed_by_id' => auth()->id(),
        ]);

        // Send notifications based on status change
        $notificationService = app(OrderNotificationService::class);

        try {
            switch ($status) {
                case 'processing':
                    $notificationService->sendOrderConfirmation($this);
                    break;
                case 'shipped':
                    $notificationService->sendOrderShipped($this);
                    break;
                case 'delivered':
                    $notificationService->sendOrderDelivered($this);
                    break;
                case 'cancelled':
                    $notificationService->sendOrderCancelled($this);
                    break;
                case 'refunded':
                    $notificationService->sendRefundNotification($this, $this->total_amount);
                    break;
                default:
                    $notificationService->sendStatusUpdateNotification($this, $oldStatus, $status);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send order notification', [
                'order' => $this->order_number,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function recalculateTotal(): void
    {
        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $this->subtotal = $subtotal;
        $this->tax = $subtotal * 0.18; // 18% GST
        $this->total_amount = $this->subtotal + $this->tax + ($this->shipping_fee ?? 0);
        $this->save();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['delivered', 'shipped']) && 
               $this->payment_status === 'paid' &&
               now()->diffInDays($this->created_at) <= 30; // 30-day refund window
    }

    public function generateOrderNumber(): void
    {
        if (!$this->order_number) {
            $this->order_number = 'ORD-' . strtoupper(uniqid());
            $this->save();
        }
    }

    public function updateTrackingNumber(string $trackingNumber): void
    {
        $this->tracking_number = $trackingNumber;
        $this->status = 'shipped';
        $this->save();

        $this->updateStatus('shipped', 'Order shipped with tracking number: ' . $trackingNumber);
    }

    public function markAsDelivered(): void
    {
        $this->status = 'delivered';
        $this->save();

        $this->updateStatus('delivered', 'Order marked as delivered');
    }

    public function processRefund(?string $reason = null): void
    {
        if (!$this->canBeRefunded()) {
            throw new \Exception('Order cannot be refunded');
        }

        $this->status = 'refunded';
        $this->payment_status = 'refunded';
        $this->save();

        $this->updateStatus('refunded', 'Order refunded. Reason: ' . ($reason ?? 'No reason provided'));
    }
} 