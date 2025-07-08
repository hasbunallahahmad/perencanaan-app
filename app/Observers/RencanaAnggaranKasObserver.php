<?php
// app/Observers/RencanaAnggaranKasObserver.php

namespace App\Observers;

use App\Models\RencanaAnggaranKas;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RencanaAnggaranKasObserver
{
    /**
     * Handle the RencanaAnggaranKas "creating" event.
     */
    public function creating(RencanaAnggaranKas $rencanaAnggaranKas): void
    {
        // Validasi urutan jenis anggaran
        $this->validateJenisAnggaranOrder($rencanaAnggaranKas);

        // Set tanggal rencana jika belum ada
        if (!$rencanaAnggaranKas->tanggal_rencana) {
            $rencanaAnggaranKas->setAttribute('tanggal_rencana', Carbon::now()->format('Y-m-d'));
        }

        Log::info('Membuat rencana anggaran kas baru', [
            'tahun' => $rencanaAnggaranKas->tahun,
            'jenis_anggaran' => $rencanaAnggaranKas->jenis_anggaran,
            'kategori' => $rencanaAnggaranKas->kategori,
            'jumlah_rencana' => $rencanaAnggaranKas->jumlah_rencana,
        ]);
    }

    /**
     * Handle the RencanaAnggaranKas "created" event.
     */
    public function created(RencanaAnggaranKas $rencanaAnggaranKas): void
    {
        Log::info('Rencana anggaran kas berhasil dibuat', [
            'id' => $rencanaAnggaranKas->id,
            'tahun' => $rencanaAnggaranKas->tahun,
            'jenis_anggaran' => $rencanaAnggaranKas->jenis_anggaran,
        ]);
    }

    /**
     * Handle the RencanaAnggaranKas "updating" event.
     */
    public function updating(RencanaAnggaranKas $rencanaAnggaranKas): void
    {
        if ($rencanaAnggaranKas->isDirty('status') && $rencanaAnggaranKas->status === 'approved') {
            Log::info('Rencana anggaran kas disetujui', [
                'id' => $rencanaAnggaranKas->id,
                'tahun' => $rencanaAnggaranKas->tahun,
                'jenis_anggaran' => $rencanaAnggaranKas->jenis_anggaran,
                'jumlah_rencana' => $rencanaAnggaranKas->jumlah_rencana,
            ]);
        }
    }

    /**
     * Handle the RencanaAnggaranKas "updated" event.
     */
    public function updated(RencanaAnggaranKas $rencanaAnggaranKas): void
    {
        // Catat perubahan penting
        if ($rencanaAnggaranKas->wasChanged('jumlah_rencana')) {
            Log::info('Jumlah rencana anggaran kas diubah', [
                'id' => $rencanaAnggaranKas->id,
                'jumlah_lama' => $rencanaAnggaranKas->getOriginal('jumlah_rencana'),
                'jumlah_baru' => $rencanaAnggaranKas->jumlah_rencana,
            ]);
        }
    }

    /**
     * Handle the RencanaAnggaranKas "deleted" event.
     */
    public function deleted(RencanaAnggaranKas $rencanaAnggaranKas): void
    {
        Log::warning('Rencana anggaran kas dihapus', [
            'id' => $rencanaAnggaranKas->id,
            'tahun' => $rencanaAnggaranKas->tahun,
            'jenis_anggaran' => $rencanaAnggaranKas->jenis_anggaran,
            'jumlah_rencana' => $rencanaAnggaranKas->jumlah_rencana,
        ]);
    }

    /**
     * Validasi urutan jenis anggaran
     */
    private function validateJenisAnggaranOrder(RencanaAnggaranKas $rencanaAnggaranKas): void
    {
        $tahun = $rencanaAnggaranKas->tahun;
        $jenisAnggaran = $rencanaAnggaranKas->jenis_anggaran;

        // Cek apakah sudah ada anggaran murni jika yang dibuat bukan anggaran murni
        if ($jenisAnggaran !== 'anggaran_murni') {
            $hasAnggaranMurni = RencanaAnggaranKas::byTahun($tahun)
                ->byJenisAnggaran('anggaran_murni')
                ->approved()
                ->exists();

            if (!$hasAnggaranMurni) {
                throw new \Exception("Anggaran Murni harus dibuat terlebih dahulu sebelum membuat {$jenisAnggaran}");
            }
        }

        // Cek apakah sudah ada pergeseran jika yang dibuat adalah perubahan
        if ($jenisAnggaran === 'perubahan') {
            $hasPergeseran = RencanaAnggaranKas::byTahun($tahun)
                ->byJenisAnggaran('pergeseran')
                ->approved()
                ->exists();

            if (!$hasPergeseran) {
                Log::warning('Membuat perubahan anggaran tanpa pergeseran sebelumnya', [
                    'tahun' => $tahun,
                ]);
            }
        }
    }
}
