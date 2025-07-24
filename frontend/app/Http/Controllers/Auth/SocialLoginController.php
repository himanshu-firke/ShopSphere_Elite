<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle(): JsonResponse
    {
        $url = Socialite::driver('google')->redirect()->getTargetUrl();
        
        return response()->json([
            'success' => true,
            'data' => [
                'redirect_url' => $url
            ]
        ]);
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->email)->first();
            
            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'customer',
                    'email_verified_at' => now(), // Google users are pre-verified
                ]);

                // Create user profile
                $user->profile()->create([
                    'user_id' => $user->id,
                    'profile_picture' => $googleUser->avatar,
                    'social_google' => $googleUser->id,
                ]);
            } else {
                // Update existing user's Google ID if not set
                if (!$user->profile || !$user->profile->social_google) {
                    if (!$user->profile) {
                        $user->profile()->create([
                            'user_id' => $user->id,
                            'social_google' => $googleUser->id,
                        ]);
                    } else {
                        $user->profile->update([
                            'social_google' => $googleUser->id,
                        ]);
                    }
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google login successful',
                'data' => [
                    'user' => $user->load('profile'),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google login failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Redirect to Facebook OAuth.
     */
    public function redirectToFacebook(): JsonResponse
    {
        $url = Socialite::driver('facebook')->redirect()->getTargetUrl();
        
        return response()->json([
            'success' => true,
            'data' => [
                'redirect_url' => $url
            ]
        ]);
    }

    /**
     * Handle Facebook OAuth callback.
     */
    public function handleFacebookCallback(Request $request): JsonResponse
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            
            $user = User::where('email', $facebookUser->email)->first();
            
            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $facebookUser->name,
                    'email' => $facebookUser->email,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'customer',
                    'email_verified_at' => now(), // Facebook users are pre-verified
                ]);

                // Create user profile
                $user->profile()->create([
                    'user_id' => $user->id,
                    'profile_picture' => $facebookUser->avatar,
                    'social_facebook' => $facebookUser->id,
                ]);
            } else {
                // Update existing user's Facebook ID if not set
                if (!$user->profile || !$user->profile->social_facebook) {
                    if (!$user->profile) {
                        $user->profile()->create([
                            'user_id' => $user->id,
                            'social_facebook' => $facebookUser->id,
                        ]);
                    } else {
                        $user->profile->update([
                            'social_facebook' => $facebookUser->id,
                        ]);
                    }
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Facebook login successful',
                'data' => [
                    'user' => $user->load('profile'),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook login failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Link social account to existing user.
     */
    public function linkSocialAccount(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:google,facebook',
            'social_id' => 'required|string',
        ]);

        $user = $request->user();
        
        if (!$user->profile) {
            $user->profile()->create([
                'user_id' => $user->id,
            ]);
        }

        $field = 'social_' . $request->provider;
        $user->profile->update([
            $field => $request->social_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->provider) . ' account linked successfully',
            'data' => [
                'user' => $user->load('profile')
            ]
        ]);
    }

    /**
     * Unlink social account.
     */
    public function unlinkSocialAccount(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:google,facebook',
        ]);

        $user = $request->user();
        
        if ($user->profile) {
            $field = 'social_' . $request->provider;
            $user->profile->update([
                $field => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->provider) . ' account unlinked successfully',
            'data' => [
                'user' => $user->load('profile')
            ]
        ]);
    }
}
