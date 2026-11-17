<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetSucursalGlobal;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetSucursalGlobal::class,
        ]);
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            $is419 = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 419)
                || $e instanceof \Illuminate\Session\TokenMismatchException;

            if ($is419) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'La sesión o el formulario ha expirado por inactividad. Por favor recargue la página e intente nuevamente.',
                    ], 419);
                }

                return redirect()->route('login')
                    ->withInput($request->only('username', 'email'))
                    ->with('warning', 'La sesión o el formulario expiró por inactividad. Por favor ingrese sus credenciales nuevamente.');
            }
        });
    })->create();
