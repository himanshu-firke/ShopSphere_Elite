<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Get all users (admin only).
     */
    public function getUsers(Request $request): JsonResponse
    {
        $users = User::with(['profile', 'addresses'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }

    /**
     * Get user statistics (admin only).
     */
    public function getUserStats(Request $request): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'customers' => User::where('role', 'customer')->count(),
            'vendors' => User::where('role', 'vendor')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Toggle user active status (admin only).
     */
    public function toggleUserStatus(Request $request, User $user): JsonResponse
    {
        $user->update([
            'is_active' => !$user->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => [
                'user' => $user->fresh()
            ]
        ]);
    }

    /**
     * Update user role (admin only).
     */
    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:customer,vendor,admin'
        ]);

        $user->update([
            'role' => $request->role
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User role updated successfully',
            'data' => [
                'user' => $user->fresh()
            ]
        ]);
    }
}
