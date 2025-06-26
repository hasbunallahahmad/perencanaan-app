<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SanitizeGlobalInput;

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
        ]);

        // Or use as route-specific middleware (recommended)
        $middleware->alias([
            'sanitize' => SanitizeGlobalInput::class,
        ]);

        // Priority order for web middleware (Laravel 11+ handles this automatically)
        // 1. EncryptCookies
        // 2. AddQueuedCookiesToResponse  
        // 3. StartSession
        // 4. ShareErrorsFromSession
        // 5. VerifyCsrfToken <- CSRF must come before sanitization
        // 6. SanitizeGlobalInput <- Our middleware comes after
        // 7. SubstituteBindings
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
