namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'order_item_id',
        'quantity',
        'received_quantity',
        'reason',
        'condition',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer'
    ];

    // Relationships
    public function return(): BelongsTo
    {
        return $this->belongsTo(Return::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    // Methods
    public function isFullyReceived(): bool
    {
        return $this->quantity === $this->received_quantity;
    }

    public function getRefundAmount(): float
    {
        $amount = $this->orderItem->price * $this->received_quantity;
        return round($amount, 2);
    }

    public function canBeProcessed(): bool
    {
        return $this->received_quantity > 0 && $this->condition !== 'damaged';
    }
} 