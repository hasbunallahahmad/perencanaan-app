<?php

namespace App\Http\Middleware;

use App\Services\YearContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class YearContextMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set default year jika belum ada di session
        if (!session()->has('app.active_tahun')) {
            $defaultYear = YearContext::getFirstYearWithData() ?? date('Y');
            YearContext::setActiveYear($defaultYear);
        }

        // Validasi tahun aktif
        $activeYear = YearContext::getActiveYear();
        if (!YearContext::isValidYear($activeYear)) {
            $fallbackYear = YearContext::setYearWithFallback($activeYear);

            // Log perubahan tahun
            Log::info('Year context auto-adjusted', [
                'invalid_year' => $activeYear,
                'fallback_year' => $fallbackYear,
                'user_id' => Auth::id(),
                'request_url' => $request->url(),
            ]);
        }

        return $next($request);
    }
}
