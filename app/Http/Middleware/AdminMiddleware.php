<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Roles that are allowed to access admin routes
     */
    private const ALLOWED_ROLES = [
        'admin',
        'super-admin'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with([
                'verify' => 'error',
                'status' => 'danger',
                'message' => 'Please login to access this page.'
            ]);
        }

        // Check if user has admin or super-admin role
        if (!$this->hasAdminAccess($user->role)) {
            return redirect()->route('dashboard')->with([
                'verify' => 'error',
                'status' => 'danger',
                'message' => 'You do not have permission to access this page.'
            ]);
        }

        // Add role and admin status to request for use in views/controllers
        $request->merge([
            'user_role' => $user->role,
            'is_super_admin' => $user->role === 'super-admin',
            'is_admin' => in_array($user->role, self::ALLOWED_ROLES, true)
        ]);

        return $next($request);
    }

    /**
     * Check if the given role has admin access
     */
    private function hasAdminAccess(string $role): bool
    {
        return in_array($role, self::ALLOWED_ROLES, true);
    }
}
