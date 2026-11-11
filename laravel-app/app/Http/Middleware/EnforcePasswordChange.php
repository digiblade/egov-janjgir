<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user !== null
            && method_exists($user, 'mustChangePassword')
            && $user->mustChangePassword()
            && ! $request->routeIs('admin.password.change', 'admin.password.change.update', 'admin.logout')
        ) {
            return redirect()->route('admin.password.change');
        }

        return $next($request);
    }
}
