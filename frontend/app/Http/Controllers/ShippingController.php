namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    protected ShippingService $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Calculate shipping rates for an order
     */
    public function calculateRates(string $orderNumber): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)
                ->with(['items.product', 'shippingAddress'])
                ->firstOrFail();

            // Verify order belongs to authenticated user if not guest
            if (auth()->check() && $order->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $rates = $this->shippingService->calculateRates($order);

            return response()->json([
                'rates' => $rates,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to calculate shipping rates', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to calculate shipping rates'
            ], 500);
        }
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $request->validate([
                'carrier' => 'required|string',
                'service_code' => 'required|string'
            ]);

            $order = Order::where('order_number', $orderNumber)
                ->with(['items.product', 'shippingAddress'])
                ->firstOrFail();

            // Verify order belongs to authenticated user if not guest
            if (auth()->check() && $order->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $label = $this->shippingService->generateLabel(
                $order,
                $request->carrier,
                $request->service_code
            );

            return response()->json($label);
        } catch (\Exception $e) {
            Log::error('Failed to generate shipping label', [
                'order' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to generate shipping label'
            ], 500);
        }
    }

    /**
     * Track shipment
     */
    public function trackShipment(string $trackingNumber, string $carrier): JsonResponse
    {
        try {
            $tracking = $this->shippingService->trackShipment($trackingNumber, $carrier);

            return response()->json($tracking);
        } catch (\Exception $e) {
            Log::error('Failed to track shipment', [
                'tracking_number' => $trackingNumber,
                'carrier' => $carrier,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to track shipment'
            ], 500);
        }
    }

    /**
     * Get shipping zones
     */
    public function getZones(): JsonResponse
    {
        try {
            // TODO: Implement dynamic shipping zones from database
            // For now, return static zones
            $zones = [
                [
                    'id' => 1,
                    'name' => 'North India',
                    'states' => [
                        'Delhi',
                        'Haryana',
                        'Punjab',
                        'Uttar Pradesh',
                        'Uttarakhand',
                        'Himachal Pradesh',
                        'Jammu and Kashmir'
                    ],
                    'base_rate' => 100,
                    'per_kg_rate' => 20
                ],
                [
                    'id' => 2,
                    'name' => 'South India',
                    'states' => [
                        'Karnataka',
                        'Kerala',
                        'Tamil Nadu',
                        'Andhra Pradesh',
                        'Telangana'
                    ],
                    'base_rate' => 150,
                    'per_kg_rate' => 25
                ],
                [
                    'id' => 3,
                    'name' => 'East India',
                    'states' => [
                        'West Bengal',
                        'Bihar',
                        'Jharkhand',
                        'Odisha',
                        'Assam'
                    ],
                    'base_rate' => 200,
                    'per_kg_rate' => 30
                ],
                [
                    'id' => 4,
                    'name' => 'West India',
                    'states' => [
                        'Maharashtra',
                        'Gujarat',
                        'Rajasthan',
                        'Goa'
                    ],
                    'base_rate' => 150,
                    'per_kg_rate' => 25
                ],
                [
                    'id' => 5,
                    'name' => 'Remote Areas',
                    'states' => [
                        'Andaman and Nicobar Islands',
                        'Lakshadweep',
                        'Ladakh',
                        'Arunachal Pradesh',
                        'Sikkim',
                        'Nagaland',
                        'Manipur',
                        'Mizoram'
                    ],
                    'base_rate' => 500,
                    'per_kg_rate' => 50
                ]
            ];

            return response()->json($zones);
        } catch (\Exception $e) {
            Log::error('Failed to get shipping zones', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get shipping zones'
            ], 500);
        }
    }

    /**
     * Get shipping methods
     */
    public function getMethods(): JsonResponse
    {
        try {
            // TODO: Implement dynamic shipping methods from database
            // For now, return static methods
            $methods = [
                [
                    'id' => 'express',
                    'name' => 'Express Delivery',
                    'description' => '2-3 business days',
                    'rate_multiplier' => 1.5,
                    'available_carriers' => ['dtdc', 'delhivery']
                ],
                [
                    'id' => 'standard',
                    'name' => 'Standard Delivery',
                    'description' => '4-5 business days',
                    'rate_multiplier' => 1.0,
                    'available_carriers' => ['dtdc', 'delhivery']
                ],
                [
                    'id' => 'economy',
                    'name' => 'Economy Delivery',
                    'description' => '5-7 business days',
                    'rate_multiplier' => 0.8,
                    'available_carriers' => ['dtdc']
                ]
            ];

            return response()->json($methods);
        } catch (\Exception $e) {
            Log::error('Failed to get shipping methods', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get shipping methods'
            ], 500);
        }
    }
} 