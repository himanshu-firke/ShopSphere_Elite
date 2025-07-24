<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Get user profile.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'addresses']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'date_of_birth' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'bio' => 'sometimes|nullable|string|max:1000',
            'website' => 'sometimes|nullable|url|max:255',
            'social_facebook' => 'sometimes|nullable|string|max:255',
            'social_twitter' => 'sometimes|nullable|string|max:255',
            'social_instagram' => 'sometimes|nullable|string|max:255',
            'social_linkedin' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user basic info
        $user->update($request->only(['name', 'phone', 'date_of_birth', 'gender']));

        // Update or create profile
        if (!$user->profile) {
            $user->profile()->create([
                'user_id' => $user->id,
            ]);
        }

        $user->profile->update($request->only([
            'bio', 'website', 'social_facebook', 'social_twitter', 
            'social_instagram', 'social_linkedin'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user->load(['profile', 'addresses'])
            ]
        ]);
    }

    /**
     * Upload profile picture.
     */
    public function uploadProfilePicture(Request $request): JsonResponse
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = $request->user();

        if (!$user->profile) {
            $user->profile()->create([
                'user_id' => $user->id,
            ]);
        }

        // Delete old profile picture if exists
        if ($user->profile->profile_picture) {
            Storage::disk('public')->delete($user->profile->profile_picture);
        }

        // Store new profile picture
        $path = $request->file('profile_picture')->store('profile-pictures', 'public');
        
        $user->profile->update([
            'profile_picture' => $path
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture uploaded successfully',
            'data' => [
                'profile_picture' => Storage::url($path)
            ]
        ]);
    }

    /**
     * Get user addresses.
     */
    public function getAddresses(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses;

        return response()->json([
            'success' => true,
            'data' => [
                'addresses' => $addresses
            ]
        ]);
    }

    /**
     * Add new address.
     */
    public function addAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'address_type' => 'required|in:shipping,billing',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // If this is set as default, unset other defaults of the same type
        if ($request->is_default) {
            $user->addresses()
                ->where('address_type', $request->address_type)
                ->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'data' => [
                'address' => $address
            ]
        ], 201);
    }

    /**
     * Update address.
     */
    public function updateAddress(Request $request, UserAddress $address): JsonResponse
    {
        // Ensure user owns this address
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'address_type' => 'sometimes|in:shipping,billing',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'sometimes|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is set as default, unset other defaults of the same type
        if ($request->is_default) {
            $request->user()->addresses()
                ->where('address_type', $address->address_type)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => [
                'address' => $address
            ]
        ]);
    }

    /**
     * Delete address.
     */
    public function deleteAddress(Request $request, UserAddress $address): JsonResponse
    {
        // Ensure user owns this address
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    /**
     * Set address as default.
     */
    public function setDefaultAddress(Request $request, UserAddress $address): JsonResponse
    {
        // Ensure user owns this address
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Unset other defaults of the same type
        $request->user()->addresses()
            ->where('address_type', $address->address_type)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => [
                'address' => $address
            ]
        ]);
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'preferences' => 'required|array',
            'preferences.notifications' => 'sometimes|array',
            'preferences.privacy' => 'sometimes|array',
            'preferences.theme' => 'sometimes|string|in:light,dark,auto',
            'preferences.language' => 'sometimes|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user->profile) {
            $user->profile()->create([
                'user_id' => $user->id,
            ]);
        }

        $user->profile->update([
            'preferences' => $request->preferences
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'data' => [
                'preferences' => $user->profile->preferences
            ]
        ]);
    }
}
