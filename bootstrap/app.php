<?php

use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Only trust forwarded HTTPS/host headers in production. Locally this
        // treats HTTP logins as secure and drops the session cookie (419).
        if (env('APP_ENV') === 'production') {
            $middleware->trustProxies(at: '*');
        }

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'cron/*',
        ]);

        // Keep admin + webhooks reachable while the storefront is in maintenance.
        $middleware->preventRequestsDuringMaintenance([
            'admin',
            'admin/*',
            'webhooks/*',
            'cron/*',
            'up',
            'ridhisidhi-admin-bypass',
        ]);

        $middleware->alias([
            'admin.auth' => EnsureUserIsAdmin::class,
            'admin.guest' => RedirectIfAdminAuthenticated::class,
            'permission' => EnsureUserHasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $redirectExpiredSession = function (Request $request) {
            $loginRoute = $request->is('admin', 'admin/*') ? 'admin.login' : 'login';

            return redirect()
                ->route($loginRoute)
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Your session expired. Please sign in again.');
        };

        $exceptions->render(function (TokenMismatchException $e, Request $request) use ($redirectExpiredSession) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }

            return $redirectExpiredSession($request);
        });

        $exceptions->respond(function (Response $response, Throwable $e, Request $request) use ($redirectExpiredSession) {
            if ($response->getStatusCode() === 419 && ! $request->expectsJson()) {
                return $redirectExpiredSession($request);
            }

            return $response;
        });
    })->create();
