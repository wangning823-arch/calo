<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserAgreement
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->agreed_at && ! $request->routeIs('agreement.*')) {
            return redirect()->route('agreement.show');
        }

        return $next($request);
    }
}
