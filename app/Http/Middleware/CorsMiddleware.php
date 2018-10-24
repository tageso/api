<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::debug("Cors Middelware");
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE, PATCH',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];

        if ($request->isMethod('OPTIONS')) {
            Log::debug("OPTIONS Request");
            $response = response()->json('{"method":"OPTIONS"}', 200, $headers);
            Log::debug("Add CORS Header");
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }
            Log::debug("Return CORS Page");
            return $response;
        }

        Log::debug("Call Next in Middelware");
        $response = $next($request);
        Log::debug("Add CORS Header");
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
