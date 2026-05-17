<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectTo(
            guests: '/login', //  ĐÚNG: Phải dùng chuỗi ký tự '/login' chứ KHÔNG ĐƯỢC dùng hàm route('login')
            users: '/'
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tự động trả về JSON 401 khi Frontend gọi API mà chưa đăng nhập hợp lệ
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn chưa đăng nhập hoặc phiên làm việc đã hết hạn!'
                ], 401);
            }
        });
    })->create();
