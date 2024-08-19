<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class GuestJwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // Cek apakah token valid dan user sudah terautentikasi
            if ($user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah login'
                ], 403);
            }
        } catch (\Exception $e) {
            // Jika token tidak valid atau tidak ada, lanjutkan proses
        }

        return $next($request);
    }
}
