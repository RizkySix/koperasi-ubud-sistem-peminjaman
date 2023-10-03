<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnverifiedPhone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user()->phone_number_verified !== null){
            return response()->json([
                'status' => false,
                'data' => 'Akun sudah terverifikasi'
            ], 403);
        }
        return $next($request);
    }
}
