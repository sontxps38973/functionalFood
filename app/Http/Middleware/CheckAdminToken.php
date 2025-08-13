<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sử dụng guard 'admin' đã cấu hình trong config/auth.php
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'message' => 'Bạn không phải là quản trị viên hoặc token không hợp lệ.'
            ], 401);
        }

        // Gán user cho request để controller nhận đúng admin
        $request->setUserResolver(function () use ($admin) {
            return $admin;
        });

        return $next($request);
    }
}
