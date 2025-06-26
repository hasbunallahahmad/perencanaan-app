<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class SanitizeGlobalInput
{
    /**
     * Dangerous patterns to detect
     */
    private array $dangerousPatterns = [
        // SQL Injection patterns
        '/(\s|^)(or|and)\s+(\'|\"|`)*\s*\d+\s*=\s*\d+/i',
        '/(\s|^)(or|and)\s+(\'|\"|`)*\s*(\'|\"|\`)\s*(\'|\"|\`)\s*=\s*(\'|\"|\`)/i',
        '/union\s+select/i',
        '/drop\s+(table|database)/i',
        '/delete\s+from/i',
        '/insert\s+into/i',
        '/update\s+.+set/i',
        '/alter\s+table/i',
        '/create\s+(table|database)/i',
        '/truncate\s+table/i',
        '/--\s*$|\/\*|\*\//i',

        // XSS patterns
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript\s*:/i',
        '/on\w+\s*=/i', // onclick, onload, etc
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/<object[^>]*>.*?<\/object>/is',
        '/<embed[^>]*>/i',
        '/<form[^>]*>.*?<\/form>/is',

        // Path traversal
        '/(\.\.[\/\\\\])+/i',
        '/\/etc\/passwd/i',
        '/\/proc\/self\/environ/i',
        '/\/var\/log/i',
        '/windows\/system32/i',

        // Command injection
        '/;\s*(rm|cat|ls|pwd|whoami|uname|id|ps|kill|chmod|chown)/i',
        '/\|\s*(rm|cat|ls|pwd|whoami|uname|id|ps|kill|chmod|chown)/i',
        '/`[^`]*`/i', // backticks
        '/\$\([^)]*\)/i', // command substitution

        // File inclusion
        '/include\s*\(/i',
        '/require\s*\(/i',
        '/file_get_contents\s*\(/i',
        '/readfile\s*\(/i',
        '/fopen\s*\(/i',

        // PHP code injection
        '/<\?php/i',
        '/<\?=/i',
        '/eval\s*\(/i',
        '/exec\s*\(/i',
        '/system\s*\(/i',
        '/shell_exec\s*\(/i',
        '/passthru\s*\(/i',
        '/base64_decode\s*\(/i',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip sanitization for auth routes to prevent CSRF issues
        if ($this->shouldSkipSanitization($request)) {
            return $next($request);
        }

        // Only sanitize user input, not Laravel system data
        $userInput = $request->except($this->getSystemFields());

        if (!empty($userInput)) {
            $this->sanitizeData($userInput);
        }

        return $next($request);
    }

    /**
     * Determine if we should skip sanitization for this request
     */
    private function shouldSkipSanitization(Request $request): bool
    {
        // Skip for login/auth routes to avoid CSRF token issues
        $skipPatterns = [
            'login*',
            'register*',
            'password/*',
            'email/verify*',
            'logout*',
            'auth/*',
            'sanctum/*',
            'api/*', // Skip API routes
        ];

        foreach ($skipPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        // Skip for AJAX requests with valid CSRF token
        if ($request->ajax() && $request->hasHeader('X-CSRF-TOKEN')) {
            return true;
        }

        return false;
    }

    /**
     * Get Laravel system fields that should never be sanitized
     */
    private function getSystemFields(): array
    {
        return [
            '_token',
            '_method',
            'csrf_token',
            'laravel_session',
            'XSRF-TOKEN',
            'remember_token',
            '_previous',
            '_flash',
            'intended_url',
        ];
    }

    private function sanitizeData(array $data): void
    {
        foreach ($data as $key => $value) {
            // Skip fields that start with underscore (Laravel convention)
            if (str_starts_with($key, '_')) {
                continue;
            }

            if (is_array($value)) {
                $this->sanitizeData($value);
            } elseif (is_string($value)) {
                $this->checkDangerousInput($key, $value);
            }
        }
    }

    private function checkDangerousInput(string $key, string $value): void
    {
        // Skip empty values
        if (empty(trim($value))) {
            return;
        }

        // URL decode to catch encoded malicious input
        $decodedValue = urldecode($value);

        // Check both original and decoded values
        $valuesToCheck = array_unique([$value, $decodedValue]);

        foreach ($valuesToCheck as $checkValue) {
            foreach ($this->dangerousPatterns as $pattern) {
                if (preg_match($pattern, $checkValue)) {
                    $this->logThreat('dangerous_pattern', $key, $checkValue, $pattern);
                    abort(403, 'Input contains dangerous characters that are not allowed.');
                }
            }

            // Additional checks for suspicious character combinations
            if ($this->containsSuspiciousCharacters($checkValue)) {
                $this->logThreat('suspicious_chars', $key, $checkValue);
                abort(403, 'Input contains suspicious character combinations.');
            }
        }
    }

    private function containsSuspiciousCharacters(string $value): bool
    {
        // Check for multiple suspicious characters in combination
        $suspiciousChars = ["'", '"', '`', '<', '>', '(', ')', ';', '|', '&', '$'];
        $foundChars = [];

        foreach ($suspiciousChars as $char) {
            if (str_contains($value, $char)) {
                $foundChars[] = $char;
            }
        }

        // If more than 3 suspicious characters found, it might be malicious
        return count($foundChars) > 3;
    }

    /**
     * Log security threats with proper sanitization
     */
    private function logThreat(string $type, string $field, string $value, ?string $pattern = null): void
    {
        $logData = [
            'type' => $type,
            'field' => $field,
            'value' => $this->sanitizeLogValue($value),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'timestamp' => now()->toISOString(),
        ];

        if ($pattern) {
            $logData['pattern'] = $pattern;
        }

        Log::warning('Security threat detected', $logData);
    }

    /**
     * Sanitize value for logging to prevent log injection
     */
    private function sanitizeLogValue(string $value): string
    {
        return substr(
            str_replace(["\n", "\r", "\t", "\0"], ['\\n', '\\r', '\\t', '\\0'], $value),
            0,
            200
        );
    }
}
