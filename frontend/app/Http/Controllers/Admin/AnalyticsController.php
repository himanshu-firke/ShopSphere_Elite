<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Get sales analytics
     */
    public function salesAnalytics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:daily,weekly,monthly'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $groupBy = $request->get('group_by', 'daily');

        $analytics = $this->analyticsService->getSalesAnalytics(
            $startDate,
            $endDate,
            $groupBy
        );

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get revenue by category
     */
    public function revenueByCategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $revenue = $this->analyticsService->getRevenueByCategory(
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => $revenue
        ]);
    }

    /**
     * Get product performance metrics
     */
    public function productPerformance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $performance = $this->analyticsService->getProductPerformance(
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => $performance
        ]);
    }

    /**
     * Get customer insights
     */
    public function customerInsights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $insights = $this->analyticsService->getCustomerInsights(
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => $insights
        ]);
    }

    /**
     * Get payment analytics
     */
    public function paymentAnalytics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $analytics = $this->analyticsService->getPaymentAnalytics(
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Export sales report
     */
    public function exportSalesReport(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $content = $this->analyticsService->exportSalesReport(
            $startDate,
            $endDate
        );

        $filename = "sales-report-{$startDate->format('Y-m-d')}-to-{$endDate->format('Y-m-d')}.csv";

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
} 