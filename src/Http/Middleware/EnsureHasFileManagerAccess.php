<?php

namespace Iqonic\FileManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasFileManagerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized');
        }

        $permission = config('file-manager.dashboard_permission');

        if ($permission && method_exists($request->user(), 'can')) {
            if (! $request->user()->can($permission)) {
                abort(403, 'Forbidden');
            }
        }

        return $next($request);
    }
}
