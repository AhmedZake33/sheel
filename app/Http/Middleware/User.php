<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\System\System;
use App\Models\User as UserModel;

class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $allowedRoutes = ['register','verifyCode','login','download'];
        if(in_array($request->route()->getActionMethod() , $allowedRoutes)){
            return $next($request);
        }
        $user = Auth::user();
        if($user->type != UserModel::TYPE_USER){
            return error([],System::HTTP_UNAUTHORIZED , "Not authentication");
        }
        return $next($request);
        
    }
}
