<?php

use App\Http\Middleware\CacheHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\QueryOptimizationMiddleware;
use App\Http\Middleware\SanitizeGlobalInput;
use App\Http\Middleware\YearContextMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware - runs on every request
        // $middleware->append(SanitizeGlobalInput::class); // Don't use this for now

        // Web middleware group - better approach
        $middleware->web(append: [
            // Put sanitization AFTER CSRF verification to avoid token issues
            SanitizeGlobalInput::class,
            YearContextMiddleware::class,

        ]);
        // if (app()->environment('local')) {
        //     $middleware->append(QueryOptimizationMiddleware::class);
        // }
        // Or use as route-specific middleware (recommended)
        $middleware->alias([
            'sanitize' => SanitizeGlobalInput::class,
            'cache.headers' => CacheHeaders::class,
        ]);
        $middleware->group('admin', [
            'cache.headers',
        ]);
    })
    // ->withCommands([
    //     \App\Console\Commands\OptimizeCacheCommand::class,
    // ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
