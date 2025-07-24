namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
        'product_name',
        'product_sku',
        'product_options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'product_options' => 'array',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Methods
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->product_name) {
                $item->product_name = $item->product->name;
            }
            if (!$item->product_sku) {
                $item->product_sku = $item->product->sku;
            }
            $item->subtotal = $item->price * $item->quantity;
        });

        static::updating(function ($item) {
            $item->subtotal = $item->price * $item->quantity;
        });
    }
} 