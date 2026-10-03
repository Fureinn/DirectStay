<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsHost
{
    /**
     * Handle an incoming request.
     *
     * Ensure only hosts and administrators can access host portal features.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('host.login');
        }

        if (! $user->isHost()) {
            abort(403, 'Unauthorized. This area is reserved for property hosts and administrators.');
        }

        return $next($request);
    }
}
