<?php

namespace App\Observers;

use App\Models\Seksi;
use App\Services\CacheService;

class SeksiCacheObserver
{
    /**
     * Handle the Seksi "created" event.
     */
    public function created(Seksi $seksi): void
    {
        $this->clearRelatedCaches($seksi);
    }

    /**
     * Handle the Seksi "updated" event.
     */
    public function updated(Seksi $seksi): void
    {
        if ($seksi->isDirty('bidang_id')) {
            $originalBidangId = $seksi->getOriginal('bidang_id');
            if ($originalBidangId) {
                CacheService::clearSeksiCaches($originalBidangId);
            }
        }
        $this->clearRelatedCaches($seksi);
    }

    /**
     * Handle the Seksi "deleted" event.
     */
    public function deleted(Seksi $seksi): void
    {
        $this->clearRelatedCaches($seksi);
    }

    /**
     * Handle the Seksi "restored" event.
     */
    public function restored(Seksi $seksi): void
    {
        $this->clearRelatedCaches($seksi);
    }

    /**
     * Clear all related caches for the seksi
     */
    private function clearRelatedCaches(Seksi $seksi): void
    {
        $bidangId = $seksi->bidang_id;
        $organisasiId = null;

        if ($seksi->relationLoaded('bidang') && $seksi->bidang) {
            $organisasiId = $seksi->bidang->organisasi_id;
        } elseif ($bidangId) {
            // Load relation efficiently - only if not already loaded
            if (!$seksi->relationLoaded('bidang')) {
                $seksi->load('bidang:id,organisasi_id');
            }

            if ($seksi->bidang) {
                $organisasiId = $seksi->bidang->organisasi_id;
            }
        }

        // Single cache clear operation
        CacheService::clearSeksiCaches($bidangId, $organisasiId);
    }
}
