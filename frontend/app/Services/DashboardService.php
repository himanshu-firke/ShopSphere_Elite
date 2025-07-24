<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get key metrics for dashboard
     */
    public function getKeyMetrics(): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        return [
            'sales' => [
                'today' => $this->getSalesMetrics($today, now()),
                'yesterday' => $this->getSalesMetrics($yesterday, $today),
                'this_month' => $this->getSalesMetrics($thisMonth, now()),
                'last_month' => $this->getSalesMetrics($lastMonth, $thisMonth)
            ],
            'orders' => [
                'today' => $this->getOrderMetrics($today, now()),
                'yesterday' => $this->getOrderMetrics($yesterday, $today),
                'this_month' => $this->getOrderMetrics($thisMonth, now()),
                'last_month' => $this->getOrderMetrics($lastMonth, $thisMonth)
            ],
            'customers' => [
                'total' => User::count(),
                'new_today' => User::where('created_at', '>=', $today)->count(),
                'new_this_month' => User::where('created_at', '>=', $thisMonth)->count()
            ],
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'out_of_stock' => Product::where('stock_quantity', 0)->count(),
                'low_stock' => Product::where('stock_quantity', '>', 0)
                    ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
                    ->count()
            ]
        ];
    }

    /**
     * Get sales metrics for a period
     */
    private function getSalesMetrics(Carbon $start, Carbon $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_sales,
                AVG(total) as average_order_value
            ')
            ->first();

        return [
            'total_orders' => $orders->total_orders ?? 0,
            'total_sales' => $orders->total_sales ?? 0,
            'paid_sales' => $orders->paid_sales ?? 0,
            'average_order_value' => $orders->average_order_value ?? 0
        ];
    }

    /**
     * Get order metrics for a period
     */
    private function getOrderMetrics(Carbon $start, Carbon $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = "shipped" THEN 1 ELSE 0 END) as shipped,
                SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled
            ')
            ->first();

        return [
            'total' => $orders->total ?? 0,
            'pending' => $orders->pending ?? 0,
            'processing' => $orders->processing ?? 0,
            'shipped' => $orders->shipped ?? 0,
            'delivered' => $orders->delivered ?? 0,
            'cancelled' => $orders->cancelled ?? 0
        ];
    }

    /**
     * Get sales trend data
     */
    public function getSalesTrend(string $period = 'daily', int $limit = 30): array
    {
        $format = $period === 'monthly' ? '%Y-%m' : '%Y-%m-%d';
        $groupBy = $period === 'monthly' ? 'DATE_FORMAT(created_at, "%Y-%m")' : 'DATE(created_at)';
        $startDate = $period === 'monthly' 
            ? now()->subMonths($limit)->startOfMonth() 
            : now()->subDays($limit)->startOfDay();

        $sales = Order::where('created_at', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->selectRaw("
                {$groupBy} as date,
                COUNT(*) as orders,
                SUM(total) as total_sales,
                AVG(total) as average_order_value
            ")
            ->groupBy(DB::raw($groupBy))
            ->orderBy('date')
            ->get();

        return [
            'labels' => $sales->pluck('date'),
            'orders' => $sales->pluck('orders'),
            'total_sales' => $sales->pluck('total_sales'),
            'average_order_value' => $sales->pluck('average_order_value')
        ];
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(int $limit = 10): Collection
    {
        return DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('products.id', 'products.name', 'products.stock_quantity')
            ->selectRaw('
                products.id,
                products.name,
                products.stock_quantity,
                SUM(order_items.quantity) as total_quantity,
                SUM(order_items.quantity * order_items.price) as total_sales
            ')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get customer growth data
     */
    public function getCustomerGrowth(string $period = 'daily', int $limit = 30): array
    {
        $format = $period === 'monthly' ? '%Y-%m' : '%Y-%m-%d';
        $groupBy = $period === 'monthly' ? 'DATE_FORMAT(created_at, "%Y-%m")' : 'DATE(created_at)';
        $startDate = $period === 'monthly'
            ? now()->subMonths($limit)->startOfMonth()
            : now()->subDays($limit)->startOfDay();

        $growth = User::where('created_at', '>=', $startDate)
            ->selectRaw("
                {$groupBy} as date,
                COUNT(*) as new_customers
            ")
            ->groupBy(DB::raw($groupBy))
            ->orderBy('date')
            ->get();

        return [
            'labels' => $growth->pluck('date'),
            'new_customers' => $growth->pluck('new_customers')
        ];
    }

    /**
     * Get inventory alerts
     */
    public function getInventoryAlerts(): Collection
    {
        return Product::where('is_active', true)
            ->where(function ($query) {
                $query->where('stock_quantity', 0)
                    ->orWhere('stock_quantity', '<=', DB::raw('low_stock_threshold'));
            })
            ->select([
                'id',
                'name',
                'stock_quantity',
                'low_stock_threshold',
                DB::raw('CASE WHEN stock_quantity = 0 THEN "out_of_stock" ELSE "low_stock" END as alert_type')
            ])
            ->orderBy('stock_quantity')
            ->get();
    }

    /**
     * Get recent activity feed
     */
    public function getRecentActivity(int $limit = 10): Collection
    {
        $activities = collect();

        // Recent orders
        $orders = Order::with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'order',
                    'message' => "New order #{$order->id} from {$order->user->name}",
                    'amount' => $order->total,
                    'timestamp' => $order->created_at,
                    'url' => "/admin/orders/{$order->id}"
                ];
            });
        $activities = $activities->merge($orders);

        // Recent customers
        $customers = User::latest()
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'customer',
                    'message' => "New customer registered: {$user->name}",
                    'timestamp' => $user->created_at,
                    'url' => "/admin/customers/{$user->id}"
                ];
            });
        $activities = $activities->merge($customers);

        // Recent product updates
        $products = Product::where('updated_at', '>', now()->subDays(7))
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'type' => 'product',
                    'message' => "Product updated: {$product->name}",
                    'timestamp' => $product->updated_at,
                    'url' => "/admin/products/{$product->id}"
                ];
            });
        $activities = $activities->merge($products);

        return $activities
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();
    }
} 