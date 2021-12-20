<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class AllowedUserClass
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $allowed_class = "")
    {
        $allowed_classes = explode('|', $allowed_class);
        $user = $request->user();
        foreach ($allowed_classes as $allowed_class)
        {
            if (is_a($user, $allowed_class))
            {
                return $next($request);
            }
        }
        return response([
            'code' => 2,
            'message' => 'user type not allowed'
        ], 401);
    }
}
