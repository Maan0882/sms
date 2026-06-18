<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check() && !\Illuminate\Support\Facades\Auth::user()->is_active) {
            \Illuminate\Support\Facades\Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->guest(\Filament\Facades\Filament::getLoginUrl());
        }

        return $next($request);
    }
}
