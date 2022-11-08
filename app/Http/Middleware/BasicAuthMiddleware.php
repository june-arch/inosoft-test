<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helper\Wrapper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;

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
        if(!(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))){
            return response()->json([
                "data" => null,
                "message" => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if(!($_ENV['BASIC_AUTH_USERNAME'] == $_SERVER['PHP_AUTH_USER'] &&
            $_SERVER['PHP_AUTH_PW'] == $_ENV['BASIC_AUTH_PASSWORD'])) {
            return response()->json([
                "data" => null,
                "message" => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
