    <?php

    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    // Home route with proper authentication check
    Route::get('/', function () {
        if (Auth::check()) {
            return redirect('/admin');
        }
        return redirect('/admin/login');
    })->name('home');

    // Secure logout with CSRF protection - NO SANITIZATION
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    })->name('logout')->middleware(['auth', 'throttle:5,1']);

    // API logout for Sanctum - NO SANITIZATION
    if (class_exists(\Laravel\Sanctum\Sanctum::class)) {
        Route::post('/api/logout', function (Request $request) {
            $user = $request->user();
            if ($user && method_exists($user, 'currentAccessToken')) {
                $token = $user->currentAccessToken();
                if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
                    $token->delete();
                }
            }
            return response()->json(['message' => 'Logged out']);
        })->middleware(['auth:sanctum', 'throttle:5,1']);
    }

    // Minimal health check (no sensitive info)
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    })->name('health')->middleware('throttle:10,1');

    // Detailed health check for authenticated users only
    Route::get('/health/detailed', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'app' => config('app.name'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => [
                'status' => DB::connection()->getPdo() ? 'connected' : 'disconnected'
            ],
            'cache' => [
                'status' => Cache::store()->getStore() ? 'connected' : 'disconnected'
            ]
        ]);
    })->name('health.detailed')->middleware(['auth', 'can:view-system-info', 'throttle:5,1']);

    // Secure sitemap
    Route::get('/sitemap.xml', function () {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $sitemap .= '<url><loc>' . url('/') . '</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>';
        $sitemap .= '</urlset>';

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600'
        ]);
    })->name('sitemap')->middleware('throttle:20,1');

    // Secure robots.txt
    Route::get('/robots.txt', function () {
        $robots = "User-agent: *\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /filament/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "Disallow: /storage/\n";
        $robots .= "Disallow: /vendor/\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($robots, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=86400'
        ]);
    })->name('robots')->middleware('throttle:20,1');

    // Security headers route (for testing)
    Route::get('/security-test', function () {
        return response()->json([
            'message' => 'Security headers applied',
            'headers' => [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block',
                'Referrer-Policy' => 'strict-origin-when-cross-origin'
            ]
        ]);
    })->middleware(['auth', 'throttle:10,1']);

    // ===========================================
    // ROUTES THAT NEED SANITIZATION
    // ===========================================
    // Group routes that handle user input and need sanitization
    Route::middleware(['sanitize'])->group(function () {

        // Example: Contact form or public form submissions
        Route::post('/contact', function (Request $request) {
            // This route will have input sanitized
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'message' => 'required|string|max:5000',
            ]);

            // Process contact form...
            return response()->json(['message' => 'Contact form submitted successfully']);
        })->name('contact.submit')->middleware('throttle:5,10'); // 5 attempts per 10 minutes

        // Example: Newsletter subscription
        Route::post('/newsletter/subscribe', function (Request $request) {
            $validated = $request->validate([
                'email' => 'required|email|unique:newsletter_subscribers',
            ]);

            // Process newsletter subscription...
            return response()->json(['message' => 'Subscribed successfully']);
        })->name('newsletter.subscribe')->middleware('throttle:3,10');

        // Example: Public search functionality
        Route::get('/search', function (Request $request) {
            $query = $request->get('q', '');

            // Perform search...
            return response()->json([
                'query' => $query,
                'results' => []
            ]);
        })->name('search')->middleware('throttle:30,1');

        // Add other public-facing routes that need input sanitization here
        // But keep ALL authentication and admin routes OUT of this group
    });

    // Enhanced fallback with proper security - NO SANITIZATION
    Route::fallback(function (Request $request) {
        // Log suspicious requests
        if ($request->getPathInfo() !== '/' && !str_starts_with($request->getPathInfo(), '/admin')) {
            Log::warning('404 attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->getPathInfo(),
                'method' => $request->method()
            ]);
        }

        if (Auth::check()) {
            return redirect('/admin');
        }

        return redirect('/admin/login');
    })->name('fallback');
