<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helper\Wrapper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BasicAuthMiddleware
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

        if(!('telkom' == $_SERVER['PHP_AUTH_USER'] && $_SERVER['PHP_AUTH_PW'] == 'da1c25d8-37c8-41b1-afe2-42dd4825bfea')) {
            return response()->json([
                "data" => null,
                "message" => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }else{
            return $next($request);
        }

    }
}
