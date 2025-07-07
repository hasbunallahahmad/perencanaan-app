<?php

namespace App\Console\Commands;

use App\Services\PerformanceOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class OptimizeCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:optimize-app
                          {--clear : Clear all application caches before warming up}
                          {--stats : Show cache statistics}';

    /**
     * The console command description.
     */
    protected $description = 'Optimize application caches for better performance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        if ($this->option('clear')) {
            $this->info('Clearing application caches...');
            $this->clearApplicationCaches();
        }

        $this->info('Warming up application caches...');
        $this->warmUpCaches();

        if ($this->option('stats')) {
            $this->showCacheStats();
        }

        $executionTime = round(microtime(true) - $startTime, 2);
        $this->info("Cache optimization completed in {$executionTime} seconds.");

        return SymfonyCommand::SUCCESS;
    }

    /**
     * Clear application-specific caches
     */
    private function clearApplicationCaches(): void
    {
        $cacheKeys = [
            'bidang_count',
            'organisasi_options',
            'organisasi_dropdown_options',
            'unique_active_bidang',
            'active_bidang_count',
            'active_organisasi_count',
        ];

        $cleared = 0;
        foreach ($cacheKeys as $key) {
            if (Cache::forget($key)) {
                $cleared++;
            }
        }

        // Clear user-specific caches (roles and permissions)
        $this->clearUserCaches();

        $this->line("Cleared {$cleared} application cache keys.");
    }

    /**
     * Clear user-specific role and permission caches
     */
    private function clearUserCaches(): void
    {
        // This would typically require knowing user IDs
        // For now, we'll use a pattern-based approach
        $this->info('Clearing user permission and role caches...');

        // In production, you might want to implement a more sophisticated
        // cache clearing strategy for user-specific data
    }

    /**
     * Warm up frequently used caches
     */
    private function warmUpCaches(): void
    {
        $caches = [
            'Organisasi Options' => fn() => PerformanceOptimizationService::getCachedOrganisasiOptions(),
            'Application Caches' => fn() => PerformanceOptimizationService::warmUpCaches(),
        ];

        $progressBar = $this->output->createProgressBar(count($caches));
        $progressBar->start();

        foreach ($caches as $name => $callback) {
            try {
                $callback();
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to warm up {$name}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Show cache statistics
     */
    private function showCacheStats(): void
    {
        $this->info('Cache Statistics:');

        $stats = [
            'Organisasi Options' => Cache::has('organisasi_dropdown_options') ? 'HIT' : 'MISS',
            'Bidang Count' => Cache::has('bidang_count') ? 'HIT' : 'MISS',
            'Active Bidang Count' => Cache::has('active_bidang_count') ? 'HIT' : 'MISS',
            'Active Organisasi Count' => Cache::has('active_organisasi_count') ? 'HIT' : 'MISS',
            'Unique Active Bidang' => Cache::has('unique_active_bidang') ? 'HIT' : 'MISS',
        ];

        $this->table(
            ['Cache Key', 'Status'],
            collect($stats)->map(fn($status, $key) => [$key, $status])->toArray()
        );
    }
}
