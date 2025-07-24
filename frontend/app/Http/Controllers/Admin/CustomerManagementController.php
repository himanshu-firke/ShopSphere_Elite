<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Get customers list with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['profile'])
            ->withCount(['orders', 'reviews'])
            ->withSum('orders', 'total');

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($q) use ($search) {
                        $q->where('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('min_orders')) {
            $query->has('orders', '>=', $request->min_orders);
        }

        if ($request->has('min_spent')) {
            $query->whereHas('orders', function ($q) use ($request) {
                $q->select('user_id')
                    ->groupBy('user_id')
                    ->havingRaw('SUM(total) >= ?', [$request->min_spent]);
            });
        }

        if ($request->has('registration_date')) {
            $date = explode(',', $request->registration_date);
            if (count($date) === 2) {
                $query->whereBetween('created_at', $date);
            }
        }

        // Sort
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Get customer details
     */
    public function show(User $customer): JsonResponse
    {
        $customer->load([
            'profile',
            'addresses',
            'orders' => function ($query) {
                $query->latest()->limit(5);
            },
            'reviews' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        // Get order statistics
        $orderStats = Order::where('user_id', $customer->id)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total) as total_spent,
                AVG(total) as average_order_value,
                COUNT(DISTINCT CASE WHEN status = "cancelled" THEN id END) as cancelled_orders
            ')
            ->first();

        // Get favorite categories
        $favoriteCategories = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.user_id', $customer->id)
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('
                categories.id,
                categories.name,
                COUNT(*) as total_orders,
                SUM(order_items.quantity) as total_items
            ')
            ->orderByDesc('total_items')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'order_stats' => $orderStats,
                'favorite_categories' => $favoriteCategories
            ]
        ]);
    }

    /**
     * Update customer status
     */
    public function updateStatus(Request $request, User $customer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,blocked'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Customer status updated successfully'
        ]);
    }

    /**
     * Get customer order history
     */
    public function orderHistory(Request $request, User $customer): JsonResponse
    {
        $query = Order::with(['items.product'])
            ->where('user_id', $customer->id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_range')) {
            $dates = explode(',', $request->date_range);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', $dates);
            }
        }

        if ($request->has('min_total')) {
            $query->where('total', '>=', $request->min_total);
        }

        // Sort
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get customer review history
     */
    public function reviewHistory(Request $request, User $customer): JsonResponse
    {
        $query = $customer->reviews()
            ->with(['product', 'images'])
            ->withCount('helpful');

        // Apply filters
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->has('date_range')) {
            $dates = explode(',', $request->date_range);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', $dates);
            }
        }

        if ($request->has('has_images')) {
            $query->has('images');
        }

        // Sort
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $reviews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Get customer activity log
     */
    public function activityLog(Request $request, User $customer): JsonResponse
    {
        $activities = collect();

        // Orders
        $orders = $customer->orders()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'order',
                    'message' => "Placed order #{$order->id}",
                    'amount' => $order->total,
                    'timestamp' => $order->created_at,
                    'url' => "/admin/orders/{$order->id}"
                ];
            });
        $activities = $activities->merge($orders);

        // Reviews
        $reviews = $customer->reviews()
            ->with('product')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($review) {
                return [
                    'type' => 'review',
                    'message' => "Reviewed {$review->product->name}",
                    'rating' => $review->rating,
                    'timestamp' => $review->created_at,
                    'url' => "/admin/products/{$review->product_id}#reviews"
                ];
            });
        $activities = $activities->merge($reviews);

        // Profile updates
        if ($customer->profile) {
            $profileUpdates = [
                [
                    'type' => 'profile',
                    'message' => 'Updated profile information',
                    'timestamp' => $customer->profile->updated_at,
                    'url' => "/admin/customers/{$customer->id}"
                ]
            ];
            $activities = $activities->merge($profileUpdates);
        }

        return response()->json([
            'success' => true,
            'data' => $activities
                ->sortByDesc('timestamp')
                ->values()
        ]);
    }

    /**
     * Export customer data
     */
    public function export(User $customer): JsonResponse
    {
        $data = [
            'customer' => $customer->toArray(),
            'profile' => $customer->profile?->toArray(),
            'addresses' => $customer->addresses->toArray(),
            'orders' => $customer->orders()
                ->with(['items.product'])
                ->get()
                ->toArray(),
            'reviews' => $customer->reviews()
                ->with(['product', 'images'])
                ->get()
                ->toArray()
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
} 