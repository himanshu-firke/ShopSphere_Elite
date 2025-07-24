namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /**
     * Initialize checkout process
     */
    public function initialize(Request $request): JsonResponse
    {
        $cart = Cart::getCurrentCart();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'error' => 'Cart is empty'
            ], 400);
        }

        // Validate stock availability
        foreach ($cart->items as $item) {
            if ($item->quantity > $item->product->stock) {
                return response()->json([
                    'error' => "Insufficient stock for {$item->product->name}",
                    'available' => $item->product->stock
                ], 400);
            }
        }

        // Get user's addresses if logged in
        $addresses = [];
        if (auth()->check()) {
            $addresses = auth()->user()->addresses;
        }

        return response()->json([
            'cart' => $cart->load('items.product'),
            'addresses' => $addresses,
            'checkout_token' => encrypt($cart->id)
        ]);
    }

    /**
     * Validate shipping address
     */
    public function validateAddress(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'address_id' => 'nullable|exists:user_addresses,id',
                'new_address' => 'required_without:address_id',
                'new_address.full_name' => 'required_with:new_address',
                'new_address.phone' => 'required_with:new_address',
                'new_address.address_line1' => 'required_with:new_address',
                'new_address.city' => 'required_with:new_address',
                'new_address.state' => 'required_with:new_address',
                'new_address.postal_code' => 'required_with:new_address',
                'new_address.country' => 'required_with:new_address',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // If using existing address
            if ($request->address_id) {
                $address = UserAddress::findOrFail($request->address_id);
                if (auth()->check() && $address->user_id !== auth()->id()) {
                    return response()->json([
                        'error' => 'Invalid address'
                    ], 403);
                }
            }
            // If creating new address
            else {
                $address = new UserAddress($request->new_address);
                if (auth()->check()) {
                    $address->user_id = auth()->id();
                    $address->save();
                }
            }

            return response()->json([
                'success' => true,
                'address' => $address
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Create order from cart
     */
    public function createOrder(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'shipping_address_id' => 'required|exists:user_addresses,id',
                'billing_address_id' => 'required|exists:user_addresses,id',
                'payment_method' => 'required|in:stripe,paypal',
                'notes' => 'nullable|string|max:500',
                'checkout_token' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // Decrypt cart ID from checkout token
            try {
                $cartId = decrypt($request->checkout_token);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Invalid checkout token'
                ], 400);
            }

            $cart = Cart::with('items.product')->findOrFail($cartId);

            // Start transaction
            return DB::transaction(function () use ($request, $cart) {
                // Create order
                $order = new Order([
                    'user_id' => auth()->id(),
                    'shipping_address_id' => $request->shipping_address_id,
                    'billing_address_id' => $request->billing_address_id,
                    'payment_method' => $request->payment_method,
                    'notes' => $request->notes,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'shipping_fee' => 0, // Calculate based on your logic
                ]);

                $order->save();
                $order->generateOrderNumber();

                // Create order items
                foreach ($cart->items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price,
                        'product_name' => $item->product->name,
                        'product_sku' => $item->product->sku,
                        'product_options' => $item->options ?? null,
                    ]);

                    // Decrease product stock
                    $item->product->decrement('stock', $item->quantity);
                }

                // Calculate order total
                $order->recalculateTotal();

                // Clear the cart
                $cart->delete();

                return response()->json([
                    'success' => true,
                    'order' => $order->load('items', 'shippingAddress', 'billingAddress')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order summary
     */
    public function getOrderSummary(string $orderNumber): JsonResponse
    {
        $order = Order::with(['items', 'shippingAddress', 'billingAddress'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Check access
        if (auth()->check() && $order->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'order' => $order
        ]);
    }
} 