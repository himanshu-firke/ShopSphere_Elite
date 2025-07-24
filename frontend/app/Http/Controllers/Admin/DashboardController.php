<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Get dashboard overview data
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $this->dashboardService->getKeyMetrics(),
                'inventory_alerts' => $this->dashboardService->getInventoryAlerts(),
                'recent_activity' => $this->dashboardService->getRecentActivity()
            ]
        ]);
    }

    /**
     * Get sales trend data
     */
    public function salesTrend(Request $request): JsonResponse
    {
        $period = $request->get('period', 'daily');
        $limit = $request->get('limit', 30);

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getSalesTrend($period, $limit)
        ]);
    }

    /**
     * Get top selling products
     */
    public function topProducts(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getTopProducts($limit)
        ]);
    }

    /**
     * Get customer growth data
     */
    public function customerGrowth(Request $request): JsonResponse
    {
        $period = $request->get('period', 'daily');
        $limit = $request->get('limit', 30);

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getCustomerGrowth($period, $limit)
        ]);
    }

    /**
     * Get inventory alerts
     */
    public function inventoryAlerts(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getInventoryAlerts()
        ]);
    }

    /**
     * Get recent activity feed
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getRecentActivity($limit)
        ]);
    }
} 