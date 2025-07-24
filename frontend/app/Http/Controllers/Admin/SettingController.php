<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Get all settings
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        $settings = $query->get();

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Get a specific setting
     */
    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'settings.*.type' => 'nullable|string|in:string,boolean,integer,float,array',
            'settings.*.group' => 'nullable|string',
            'settings.*.description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->settings as $settingData) {
            Setting::set(
                $settingData['key'],
                $settingData['value'],
                $settingData['type'] ?? null,
                $settingData['group'] ?? null,
                $settingData['description'] ?? null
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    }

    /**
     * Delete a setting
     */
    public function destroy(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        $setting->delete();

        // Clear cache for this setting
        Setting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully'
        ]);
    }

    /**
     * Get settings by group
     */
    public function getGroup(string $group): JsonResponse
    {
        $settings = Setting::getGroup($group);

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): JsonResponse
    {
        Setting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Settings cache cleared successfully'
        ]);
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults(): JsonResponse
    {
        $defaultSettings = [
            // General settings
            [
                'key' => 'site_name',
                'value' => 'My E-commerce Store',
                'type' => 'string',
                'group' => 'general',
                'description' => 'The name of the website'
            ],
            [
                'key' => 'site_description',
                'value' => 'Your one-stop shop for everything',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Website meta description'
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@example.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Primary contact email'
            ],

            // Store settings
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Store currency'
            ],
            [
                'key' => 'tax_rate',
                'value' => 10,
                'type' => 'float',
                'group' => 'store',
                'description' => 'Default tax rate percentage'
            ],
            [
                'key' => 'shipping_methods',
                'value' => [
                    'standard' => ['name' => 'Standard Shipping', 'price' => 5.00],
                    'express' => ['name' => 'Express Shipping', 'price' => 15.00]
                ],
                'type' => 'array',
                'group' => 'store',
                'description' => 'Available shipping methods'
            ],

            // Email settings
            [
                'key' => 'smtp_host',
                'value' => 'smtp.example.com',
                'type' => 'string',
                'group' => 'email',
                'description' => 'SMTP server host'
            ],
            [
                'key' => 'smtp_port',
                'value' => 587,
                'type' => 'integer',
                'group' => 'email',
                'description' => 'SMTP server port'
            ],

            // Social media
            [
                'key' => 'social_links',
                'value' => [
                    'facebook' => 'https://facebook.com/mystore',
                    'twitter' => 'https://twitter.com/mystore',
                    'instagram' => 'https://instagram.com/mystore'
                ],
                'type' => 'array',
                'group' => 'social',
                'description' => 'Social media links'
            ],

            // Analytics
            [
                'key' => 'google_analytics_id',
                'value' => '',
                'type' => 'string',
                'group' => 'analytics',
                'description' => 'Google Analytics tracking ID'
            ],

            // Features
            [
                'key' => 'enable_reviews',
                'value' => true,
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable product reviews'
            ],
            [
                'key' => 'enable_wishlist',
                'value' => true,
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable wishlist feature'
            ],
            [
                'key' => 'enable_guest_checkout',
                'value' => true,
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Allow guest checkout'
            ]
        ];

        foreach ($defaultSettings as $setting) {
            Setting::set(
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['group'],
                $setting['description']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Default settings initialized successfully'
        ]);
    }
} 