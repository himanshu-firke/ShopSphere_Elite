<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get sales analytics by date range
     */
    public function getSalesAnalytics(Carbon $startDate, Carbon $endDate, ?string $groupBy = 'daily'): array
    {
        $format = match ($groupBy) {
            'monthly' => '%Y-%m',
            'weekly' => '%Y-%u',
            default => '%Y-%m-%d'
        };

        $sales = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw("
                DATE_FORMAT(created_at, '{$format}') as date,
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as paid_sales,
                AVG(total) as average_order_value,
                COUNT(DISTINCT user_id) as unique_customers
            ")
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$format}')"))
            ->orderBy('date')
            ->get();

        return [
            'summary' => [
                'total_orders' => $sales->sum('total_orders'),
                'total_sales' => $sales->sum('total_sales'),
                'paid_sales' => $sales->sum('paid_sales'),
                'average_order_value' => $sales->avg('average_order_value'),
                'unique_customers' => $sales->sum('unique_customers')
            ],
            'trend' => [
                'dates' => $sales->pluck('date'),
                'orders' => $sales->pluck('total_orders'),
                'sales' => $sales->pluck('total_sales'),
                'paid_sales' => $sales->pluck('paid_sales'),
                'average_order_value' => $sales->pluck('average_order_value'),
                'unique_customers' => $sales->pluck('unique_customers')
            ]
        ];
    }

    /**
     * Get revenue analysis by category
     */
    public function getRevenueByCategory(Carbon $startDate, Carbon $endDate): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('
                categories.id,
                categories.name,
                COUNT(DISTINCT orders.id) as total_orders,
                SUM(order_items.quantity) as total_items,
                SUM(order_items.quantity * order_items.price) as total_revenue,
                AVG(order_items.price) as average_price
            ')
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * Get product performance metrics
     */
    public function getProductPerformance(Carbon $startDate, Carbon $endDate): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name', 'products.stock_quantity')
            ->selectRaw('
                products.id,
                products.name,
                products.stock_quantity,
                COUNT(DISTINCT orders.id) as total_orders,
                SUM(order_items.quantity) as total_quantity,
                SUM(order_items.quantity * order_items.price) as total_revenue,
                AVG(order_items.price) as average_price,
                (SUM(order_items.quantity) / DATEDIFF(?, ?)) as daily_sales_rate
            ', [$endDate, $startDate])
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * Get customer insights
     */
    public function getCustomerInsights(Carbon $startDate, Carbon $endDate): array
    {
        // Customer segments by order value
        $segments = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                user_id,
                COUNT(*) as total_orders,
                SUM(total) as total_spent,
                AVG(total) as average_order_value
            ')
            ->groupBy('user_id')
            ->get()
            ->groupBy(function ($customer) {
                return match (true) {
                    $customer->total_spent >= 1000 => 'high_value',
                    $customer->total_spent >= 500 => 'medium_value',
                    default => 'low_value'
                };
            });

        // Customer retention
        $retention = DB::table('orders as o1')
            ->join('orders as o2', function ($join) use ($startDate, $endDate) {
                $join->on('o1.user_id', '=', 'o2.user_id')
                    ->whereRaw('o2.created_at > o1.created_at')
                    ->whereBetween('o2.created_at', [$startDate, $endDate]);
            })
            ->where('o1.status', '!=', 'cancelled')
            ->where('o2.status', '!=', 'cancelled')
            ->selectRaw('
                COUNT(DISTINCT o1.user_id) as returning_customers,
                AVG(DATEDIFF(o2.created_at, o1.created_at)) as average_days_between_orders
            ')
            ->first();

        // New vs returning customers
        $customerTypes = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                user_id,
                MIN(created_at) as first_order_date,
                COUNT(*) as total_orders,
                SUM(total) as total_spent
            ')
            ->groupBy('user_id')
            ->get()
            ->groupBy(function ($customer) use ($startDate) {
                return $customer->first_order_date < $startDate ? 'returning' : 'new';
            });

        return [
            'segments' => [
                'high_value' => [
                    'count' => $segments->get('high_value', collect())->count(),
                    'total_revenue' => $segments->get('high_value', collect())->sum('total_spent'),
                    'average_order_value' => $segments->get('high_value', collect())->avg('average_order_value')
                ],
                'medium_value' => [
                    'count' => $segments->get('medium_value', collect())->count(),
                    'total_revenue' => $segments->get('medium_value', collect())->sum('total_spent'),
                    'average_order_value' => $segments->get('medium_value', collect())->avg('average_order_value')
                ],
                'low_value' => [
                    'count' => $segments->get('low_value', collect())->count(),
                    'total_revenue' => $segments->get('low_value', collect())->sum('total_spent'),
                    'average_order_value' => $segments->get('low_value', collect())->avg('average_order_value')
                ]
            ],
            'retention' => [
                'returning_customers' => $retention->returning_customers ?? 0,
                'average_days_between_orders' => round($retention->average_days_between_orders ?? 0, 1)
            ],
            'customer_types' => [
                'new' => [
                    'count' => $customerTypes->get('new', collect())->count(),
                    'total_revenue' => $customerTypes->get('new', collect())->sum('total_spent'),
                    'average_orders' => $customerTypes->get('new', collect())->avg('total_orders')
                ],
                'returning' => [
                    'count' => $customerTypes->get('returning', collect())->count(),
                    'total_revenue' => $customerTypes->get('returning', collect())->sum('total_spent'),
                    'average_orders' => $customerTypes->get('returning', collect())->avg('total_orders')
                ]
            ]
        ];
    }

    /**
     * Get payment analytics
     */
    public function getPaymentAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $payments = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                payment_method,
                payment_status,
                COUNT(*) as total_orders,
                SUM(total) as total_amount
            ')
            ->groupBy('payment_method', 'payment_status')
            ->get();

        $byMethod = $payments->groupBy('payment_method')
            ->map(function ($group) {
                return [
                    'total_orders' => $group->sum('total_orders'),
                    'total_amount' => $group->sum('total_amount'),
                    'average_amount' => $group->avg('total_amount')
                ];
            });

        $byStatus = $payments->groupBy('payment_status')
            ->map(function ($group) {
                return [
                    'total_orders' => $group->sum('total_orders'),
                    'total_amount' => $group->sum('total_amount'),
                    'percentage' => $group->sum('total_orders') / $payments->sum('total_orders') * 100
                ];
            });

        return [
            'by_method' => $byMethod,
            'by_status' => $byStatus,
            'summary' => [
                'total_orders' => $payments->sum('total_orders'),
                'total_amount' => $payments->sum('total_amount'),
                'average_amount' => $payments->avg('total_amount')
            ]
        ];
    }

    /**
     * Export sales report to CSV
     */
    public function exportSalesReport(Carbon $startDate, Carbon $endDate): string
    {
        $orders = Order::with(['user', 'items.product'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->get();

        $csv = fopen('php://temp', 'r+');

        // Headers
        fputcsv($csv, [
            'Order ID',
            'Date',
            'Customer',
            'Status',
            'Payment Method',
            'Payment Status',
            'Items',
            'Subtotal',
            'Tax',
            'Shipping',
            'Total'
        ]);

        // Data
        foreach ($orders as $order) {
            fputcsv($csv, [
                $order->id,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->user->name,
                $order->status,
                $order->payment_method,
                $order->payment_status,
                $order->items->sum('quantity'),
                $order->subtotal,
                $order->tax,
                $order->shipping,
                $order->total
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return $content;
    }
} 