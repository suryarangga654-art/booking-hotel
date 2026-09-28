<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Daftar role yang diizinkan (misal: 'admin', 'resepsionis')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah terautentikasi (login)
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.'
            ], 401);
        }

        // 2. Cek apakah 'peran' user ada di dalam daftar role yang diizinkan
        if (!in_array($request->user()->peran, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Anda tidak memiliki hak akses untuk fitur ini.'
            ], 403);
        }

        return $next($request);
    }
}