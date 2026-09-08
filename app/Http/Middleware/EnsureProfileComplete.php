<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->gender && ! $request->routeIs('profile.*')) {
            return redirect()->route('profile.edit')
                ->with('warning', '请先完善个人信息（性别、出生日期、身高），才能使用完整功能。');
        }

        return $next($request);
    }
}
