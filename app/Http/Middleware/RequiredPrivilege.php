<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiredPrivilege
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $needed_privilege)
    {
        $needed_privileges = explode('&', $needed_privilege);
        $user = $request->user();
        if ($user->is_primary)
        {
            return $next($request);
        }
        foreach ($needed_privileges as $needed_privilege)
        {
            if (! in_array($needed_privilege, $user->privileges))
            {
                return response([
                    'code' => 4,
                    'message' => 'you do not have permission to access this part of api',
                    'needed_privileges' => $needed_privileges,
                    'your_privileges' => $user->privileges
                ], 403);
            }
        }
        return $next($request);
    }
}
