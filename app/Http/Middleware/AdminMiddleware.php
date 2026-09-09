<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only allow users with the 'admin' role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated.']);
        }

        if (!auth()->user()->isAdmin()) {
            if (auth()->user()->isCashier()) {
                return redirect()->route('cashier.pos')->with('error', 'Access restricted. You do not have administrator permissions.');
            }
            abort(403, 'Access denied. Administrator privileges required.');
        }

        return $next($request);
    }
}
