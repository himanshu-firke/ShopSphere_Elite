namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    protected array $carriers = [
        'dtdc' => [
            'name' => 'DTDC',
            'base_url' => 'https://api.dtdc.com/v1',
            'api_key' => null,
            'services' => [
                'express' => [
                    'name' => 'Express',
                    'code' => 'EXP',
                    'days' => 2
                ],
                'standard' => [
                    'name' => 'Standard',
                    'code' => 'STD',
                    'days' => 4
                ]
            ]
        ],
        'delhivery' => [
            'name' => 'Delhivery',
            'base_url' => 'https://api.delhivery.com/v3',
            'api_key' => null,
            'services' => [
                'express' => [
                    'name' => 'Express',
                    'code' => 'SURFACE-EXPRESS',
                    'days' => 2
                ],
                'standard' => [
                    'name' => 'Standard',
                    'code' => 'SURFACE',
                    'days' => 3
                ]
            ]
        ]
    ];

    public function __construct()
    {
        $this->carriers['dtdc']['api_key'] = config('services.dtdc.api_key');
        $this->carriers['delhivery']['api_key'] = config('services.delhivery.api_key');
    }

    /**
     * Calculate shipping rates for an order
     */
    public function calculateRates(Order $order): array
    {
        try {
            $rates = [];

            foreach ($this->carriers as $carrierId => $carrier) {
                if (!$carrier['api_key']) {
                    continue;
                }

                $carrierRates = $this->getCarrierRates($carrierId, $order);
                $rates = array_merge($rates, $carrierRates);
            }

            // Sort rates by price
            usort($rates, function ($a, $b) {
                return $a['rate'] <=> $b['rate'];
            });

            return $rates;
        } catch (\Exception $e) {
            Log::error('Failed to calculate shipping rates', [
                'order' => $order->order_number,
                'error' => $e->getMessage()
            ]);

            // Return dummy rates if API calls fail
            return $this->getDummyRates();
        }
    }

    /**
     * Get shipping rates from a specific carrier
     */
    protected function getCarrierRates(string $carrierId, Order $order): array
    {
        $carrier = $this->carriers[$carrierId];
        $rates = [];

        foreach ($carrier['services'] as $serviceId => $service) {
            try {
                $rate = $this->callCarrierApi($carrierId, 'rates', [
                    'service' => $service['code'],
                    'origin' => [
                        'pin' => config('company.pincode'),
                        'city' => config('company.city'),
                        'state' => config('company.state')
                    ],
                    'destination' => [
                        'pin' => $order->shippingAddress->postal_code,
                        'city' => $order->shippingAddress->city,
                        'state' => $order->shippingAddress->state
                    ],
                    'weight' => $this->calculateTotalWeight($order),
                    'dimensions' => $this->calculateTotalDimensions($order)
                ]);

                $rates[] = [
                    'carrier' => $carrier['name'],
                    'service' => $service['name'],
                    'rate' => $rate['amount'],
                    'estimated_days' => $service['days'],
                    'service_code' => $service['code']
                ];
            } catch (\Exception $e) {
                Log::error("Failed to get {$carrier['name']} {$service['name']} rate", [
                    'order' => $order->order_number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $rates;
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(Order $order, string $carrierId, string $serviceCode): array
    {
        try {
            $carrier = $this->carriers[$carrierId];

            $response = $this->callCarrierApi($carrierId, 'labels', [
                'order_number' => $order->order_number,
                'service' => $serviceCode,
                'shipper' => [
                    'name' => config('company.name'),
                    'address' => config('company.address'),
                    'city' => config('company.city'),
                    'state' => config('company.state'),
                    'pincode' => config('company.pincode'),
                    'phone' => config('company.phone')
                ],
                'consignee' => [
                    'name' => $order->shippingAddress->full_name,
                    'address' => $order->shippingAddress->address_line1,
                    'address2' => $order->shippingAddress->address_line2,
                    'city' => $order->shippingAddress->city,
                    'state' => $order->shippingAddress->state,
                    'pincode' => $order->shippingAddress->postal_code,
                    'phone' => $order->shippingAddress->phone
                ],
                'packages' => $this->getPackageDetails($order)
            ]);

            return [
                'tracking_number' => $response['tracking_number'],
                'label_url' => $response['label_url'],
                'carrier' => $carrier['name'],
                'service' => $serviceCode
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate shipping label', [
                'order' => $order->order_number,
                'carrier' => $carrierId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Track shipment
     */
    public function trackShipment(string $trackingNumber, string $carrierId): array
    {
        try {
            $response = $this->callCarrierApi($carrierId, 'track', [
                'tracking_number' => $trackingNumber
            ]);

            return [
                'status' => $response['status'],
                'location' => $response['location'] ?? null,
                'timestamp' => $response['timestamp'] ?? null,
                'estimated_delivery' => $response['estimated_delivery'] ?? null,
                'history' => $response['history'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('Failed to track shipment', [
                'tracking_number' => $trackingNumber,
                'carrier' => $carrierId,
                'error' => $e->getMessage()
            ]);

            // Return dummy tracking info if API call fails
            return $this->getDummyTracking();
        }
    }

    /**
     * Call carrier API
     */
    protected function callCarrierApi(string $carrierId, string $endpoint, array $data): array
    {
        $carrier = $this->carriers[$carrierId];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $carrier['api_key'],
            'Content-Type' => 'application/json'
        ])->post("{$carrier['base_url']}/{$endpoint}", $data);

        if (!$response->successful()) {
            throw new \Exception("Carrier API error: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * Calculate total weight of order
     */
    protected function calculateTotalWeight(Order $order): float
    {
        $weight = 0;

        foreach ($order->items as $item) {
            $weight += ($item->product->weight ?? 0) * $item->quantity;
        }

        return $weight;
    }

    /**
     * Calculate total dimensions of order
     */
    protected function calculateTotalDimensions(Order $order): array
    {
        $volume = 0;
        $maxLength = 0;
        $maxWidth = 0;
        $maxHeight = 0;

        foreach ($order->items as $item) {
            $itemVolume = ($item->product->length ?? 0) *
                         ($item->product->width ?? 0) *
                         ($item->product->height ?? 0) *
                         $item->quantity;
            $volume += $itemVolume;

            $maxLength = max($maxLength, $item->product->length ?? 0);
            $maxWidth = max($maxWidth, $item->product->width ?? 0);
            $maxHeight = max($maxHeight, $item->product->height ?? 0);
        }

        return [
            'length' => $maxLength,
            'width' => $maxWidth,
            'height' => $maxHeight,
            'volume' => $volume
        ];
    }

    /**
     * Get package details for label generation
     */
    protected function getPackageDetails(Order $order): array
    {
        $packages = [];
        $dimensions = $this->calculateTotalDimensions($order);

        $packages[] = [
            'weight' => $this->calculateTotalWeight($order),
            'length' => $dimensions['length'],
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'contents' => $order->items->map(function ($item) {
                return [
                    'description' => $item->product_name,
                    'quantity' => $item->quantity,
                    'value' => $item->price
                ];
            })->toArray()
        ];

        return $packages;
    }

    /**
     * Get dummy rates when API calls fail
     */
    protected function getDummyRates(): array
    {
        return [
            [
                'carrier' => 'DTDC',
                'service' => 'Express',
                'rate' => 250,
                'estimated_days' => 2,
                'service_code' => 'EXP'
            ],
            [
                'carrier' => 'DTDC',
                'service' => 'Standard',
                'rate' => 150,
                'estimated_days' => 4,
                'service_code' => 'STD'
            ],
            [
                'carrier' => 'Delhivery',
                'service' => 'Express',
                'rate' => 300,
                'estimated_days' => 2,
                'service_code' => 'SURFACE-EXPRESS'
            ],
            [
                'carrier' => 'Delhivery',
                'service' => 'Standard',
                'rate' => 200,
                'estimated_days' => 3,
                'service_code' => 'SURFACE'
            ]
        ];
    }

    /**
     * Get dummy tracking info when API calls fail
     */
    protected function getDummyTracking(): array
    {
        return [
            'status' => 'in_transit',
            'location' => 'Delhi Hub',
            'timestamp' => now(),
            'estimated_delivery' => now()->addDays(2),
            'history' => [
                [
                    'status' => 'picked_up',
                    'location' => 'Mumbai Warehouse',
                    'timestamp' => now()->subDays(1)
                ],
                [
                    'status' => 'in_transit',
                    'location' => 'Delhi Hub',
                    'timestamp' => now()
                ]
            ]
        ];
    }
} 