<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShouldBePrimary
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! isset($request->user()->is_primary) || ! $request->user()->is_primary)
        {
            return response([
                'code' => 8,
                'message' => 'only primary accounts can access this part of api'
            ], 403);
        }
        return $next($request);
    }
}
