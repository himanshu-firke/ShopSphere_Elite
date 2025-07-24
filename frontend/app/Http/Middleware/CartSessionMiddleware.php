<?php

namespace App\Http\Middleware;

use App\Services\SessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartSessionMiddleware
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $sessionId = $request->cookie('cart_session');

        // Generate new session ID for guests if none exists
        if (!$user && !$sessionId) {
            $sessionId = $this->sessionService->generateSessionId();
            $response = $next($request);
            return $response->cookie('cart_session', $sessionId, 60 * 24); // 24 hours
        }

        // Merge guest cart into user cart after login
        if ($user && $sessionId) {
            $this->sessionService->mergeGuestCart($user, $sessionId);
            $response = $next($request);
            return $response->cookie('cart_session', null, -1); // Remove guest session cookie
        }

        // Extend session expiration
        $this->sessionService->extendSession($user, $sessionId);

        return $next($request);
    }
} 