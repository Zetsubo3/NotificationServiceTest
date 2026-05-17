<?php

use App\Exceptions\ApiExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Not authenticated',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }
            return redirect()->guest(route('login'));
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            return (new ApiExceptionHandler())->render($e, $request);
        });
    })->create();
