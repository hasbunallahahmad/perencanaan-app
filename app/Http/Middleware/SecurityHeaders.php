<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
                "font-src 'self' https://fonts.gstatic.com; " .
                "img-src 'self' data: https:; " .
                "connect-src 'self'; " .
                "frame-ancestors 'none'; " .
                "form-action 'self'; " .
                "base-uri 'self';"
        );

        // Prevent XSS attacks
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Strict Transport Security (HTTPS only)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), ' .
                'microphone=(), ' .
                'camera=(), ' .
                'magnetometer=(), ' .
                'gyroscope=(), ' .
                'payment=(), ' .
                'usb=()'
        );

        // Remove server information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
