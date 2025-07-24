namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Return extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reason',
        'status',
        'refund_amount',
        'actual_refund_amount',
        'requested_by',
        'return_label',
        'tracking_number',
        'completed_at'
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'actual_refund_amount' => 'decimal:2',
        'return_label' => 'array',
        'completed_at' => 'datetime'
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Methods
    public function canBeProcessed(): bool
    {
        return $this->status === 'pending';
    }

    public function generateTrackingNumber(): void
    {
        if (!$this->tracking_number) {
            $this->tracking_number = 'RET-' . strtoupper(uniqid());
            $this->save();
        }
    }

    public function getTotalQuantity(): int
    {
        return $this->items->sum('quantity');
    }

    public function getReceivedQuantity(): int
    {
        return $this->items->sum('received_quantity');
    }

    public function isFullyReceived(): bool
    {
        return $this->getTotalQuantity() === $this->getReceivedQuantity();
    }
} 