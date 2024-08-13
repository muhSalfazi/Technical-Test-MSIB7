<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log; // Tambahkan baris ini
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // Cek jika token diberikan dan autentikasi
            $user = JWTAuth::parseToken()->authenticate();

        
        } catch (TokenExpiredException $e) {
            Log::error('Token expired: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token telah kedaluwarsa'
            ], 401);
        } catch (TokenInvalidException $e) {
            Log::error('Token invalid: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid'
            ], 401);
        } catch (JWTException $e) {
            Log::error('JWT exception: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak ada'
            ], 401);
        } catch (Exception $e) {
            Log::error('General exception: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token otorisasi tidak ditemukan'
            ], 401);
        }

        return $next($request);
    }
}
