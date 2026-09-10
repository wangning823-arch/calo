<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminToPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_admin && ! $request->is('admin*') && ! $request->is('logout')) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
