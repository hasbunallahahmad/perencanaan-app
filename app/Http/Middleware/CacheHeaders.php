<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we can serve from cache for GET requests
        if ($request->isMethod('GET') && $this->shouldCache($request)) {
            $cacheKey = $this->generateCacheKey($request);

            // Try to get cached response
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                if ($cachedData && isset($cachedData['content'], $cachedData['headers'])) {
                    $response = response($cachedData['content'], 200);

                    // Restore headers
                    foreach ($cachedData['headers'] as $key => $value) {
                        $response->headers->set($key, $value);
                    }

                    $response->headers->set('X-Cache', 'HIT');
                    return $response;
                }
            }
        }

        $response = $next($request);

        // Handle caching for different types of requests
        if ($request->is('admin/*')) {
            if ($request->isMethod('GET') && $this->shouldCache($request)) {
                $this->cacheResponse($request, $response);
                $response->headers->set('X-Cache', 'MISS');
            } else {
                // For non-cacheable admin requests
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
        } else {
            // For non-admin requests, set appropriate cache headers
            $this->setPublicCacheHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Determine if the request should be cached
     */
    private function shouldCache(Request $request): bool
    {
        // Don't cache if user is authenticated and request contains dynamic content
        if (Auth::check()) {
            // Cache only specific admin routes that are relatively static
            $cacheableRoutes = [
                'admin/seksis',
                'admin/bidangs',
                'admin/organisasis',
            ];

            foreach ($cacheableRoutes as $route) {
                if ($request->is($route) && !$request->hasAny(['search', 'filter', 'sort'])) {
                    return true;
                }
            }

            return false;
        }

        // Cache public pages
        return $request->is('api/*') || $request->is('public/*');
    }

    /**
     * Generate cache key for request
     */
    private function generateCacheKey(Request $request): string
    {
        $key = 'http_cache:' . md5($request->fullUrl());

        // Include user ID for authenticated requests
        if (Auth::check()) {
            $key .= ':user_' . Auth::id();
        }

        // Include relevant headers that might affect response
        $relevantHeaders = ['Accept', 'Accept-Language'];
        foreach ($relevantHeaders as $header) {
            if ($request->hasHeader($header)) {
                $key .= ':' . md5($request->header($header));
            }
        }

        return $key;
    }

    /**
     * Cache the response
     */
    private function cacheResponse(Request $request, Response $response): void
    {
        try {
            // Only cache successful responses
            if ($response->getStatusCode() === 200) {
                $cacheKey = $this->generateCacheKey($request);

                $cacheData = [
                    'content' => $response->getContent(),
                    'headers' => [
                        'Content-Type' => $response->headers->get('Content-Type'),
                        'Content-Length' => $response->headers->get('Content-Length'),
                    ],
                    'cached_at' => now()->timestamp,
                ];

                // Cache for 5 minutes for admin pages, 30 minutes for others
                $ttl = $request->is('admin/*') ? 300 : 1800;
                Cache::put($cacheKey, $cacheData, $ttl);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cache response: ' . $e->getMessage());
        }
    }

    /**
     * Set public cache headers
     */
    private function setPublicCacheHeaders(Response $response, Request $request): void
    {
        if ($request->is('api/*')) {
            // API responses - cache for 10 minutes
            $response->headers->set('Cache-Control', 'public, max-age=600');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 600));
        } elseif ($request->is('assets/*') || $request->is('images/*')) {
            // Static assets - cache for 1 day
            $response->headers->set('Cache-Control', 'public, max-age=86400');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
        }
    }

    /**
     * Clear cache for specific patterns
     */
    public static function clearCache(string $pattern = null): void
    {
        if ($pattern) {
            // Clear specific pattern
            $keys = Cache::getRedis()->keys(config('cache.prefix') . ':http_cache:' . $pattern . '*');
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } else {
            // Clear all HTTP cache
            $keys = Cache::getRedis()->keys(config('cache.prefix') . ':http_cache:*');
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        }
    }
}
