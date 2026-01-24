<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Unauthorized Access');
        }

        $userRole = auth()->user()->role;

        foreach ($roles as $role) {
            if ($userRole == $role) {
                return $next($request);
            }
        }

        return redirect('/login')->with('error', 'Unauthorized Access');
    }
}
