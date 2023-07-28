<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $iss)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $issued = JWTAuth::getPayload()->get('iss');
            if ($issued !== 'auth.' . $iss) {
                throw new TokenInvalidException();
            }

            if (!$user->is_active) {
                return response()->json(['result' => false, 'error' => 'User not found'], 401);
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['result' => false, 'error' => 'Unauthorized. Invalid Token'], 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['result' => false, 'error' =>  'Token is Expired'], 401);
            }else{
                return response()->json(['result' => false, 'error' => 'Authorization Token not found'], 401);
            }
        }
        return $next($request);
    }
}
