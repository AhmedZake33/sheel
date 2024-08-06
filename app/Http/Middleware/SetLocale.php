<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale($request->header('lang')?$request->header('lang') : 'en');
    
        return $next($request);
    }
}
