<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class QueryOptimizationMiddleware
{
    private int $queryCount = 0;
    private float $startTime;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start monitoring in development
        if (app()->environment('local')) {
            $this->startTime = microtime(true);
            $this->queryCount = 0;

            DB::listen(function ($query) {
                $this->queryCount++;

                // Log slow queries
                if ($query->time > 100) { // queries longer than 100ms
                    Log::warning('Slow Query Detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms',
                        'url' => request()->url(),
                    ]);
                }
            });
        }

        $response = $next($request);

        // Log query statistics in development
        if (app()->environment('local')) {
            $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);

            // Warn if too many queries
            if ($this->queryCount > 20) {
                Log::warning('High Query Count', [
                    'count' => $this->queryCount,
                    'time' => $executionTime . 'ms',
                    'url' => $request->url(),
                    'method' => $request->method(),
                ]);
            }

            // Add debug headers
            $response->headers->set('X-Query-Count', $this->queryCount);
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
        }

        return $response;
    }
}
