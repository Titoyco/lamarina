<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRoutePermission;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar middlewares específicos de rutas
        $middleware->alias([
            'check.route.permission' => CheckRoutePermission::class, // 👈 Registrás el alias
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
