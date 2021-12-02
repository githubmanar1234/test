<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\JsonResponse;

class CheckSalon
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('client')->user()->role == 'salon') {

            return $next($request);
        }
        return JsonResponse::respondError("You are not a salon");
        //return $next($request);
    }
}
