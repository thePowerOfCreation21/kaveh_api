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
    public function handle(Request $request, Closure $next, string $privileges_string)
    {
        $user = $request->user();

        if (! $user->is_primary)
        {
            $or_privileges = explode('|', $privileges_string);
            $last_index_of_or_privileges = count($or_privileges) - 1;

            foreach ($or_privileges AS $or_privilege_key => $or_privilege)
            {
                $privileges = explode('&', $or_privileges);
                foreach ($privileges AS $privilege_key => $privilege)
                {
                    if (! in_array($privilege, $user->privileges))
                    {
                        if ($or_privilege_key == $last_index_of_or_privileges)
                        {
                            return response([
                                'code' => 4,
                                'message' => 'you do not have permission to access this part of api',
                                'required_privilege' => $privilege
                            ], 403);
                        }
                        break;
                    }
                }
            }
        }

        return $next($request);
    }
}
