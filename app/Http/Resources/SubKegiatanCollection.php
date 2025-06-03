<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubKegiatanCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => SubKegiatanResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),
                'summary' => $this->getSummary(),
            ],
        ];
    }

    /**
     * Get summary information from the collection
     */
    private function getSummary(): array
    {
        $totalAnggaran = 0;
        $totalRealisasi = 0;
        $programCount = [];
        $kegiatanCount = [];

        foreach ($this->collection as $subKegiatan) {
            // Hitung total anggaran dan realisasi jika ada serapan anggaran
            if ($subKegiatan->relationLoaded('serapanAnggaran')) {
                $totalAnggaran += $subKegiatan->serapanAnggaran->sum('anggaran');
                $totalRealisasi += $subKegiatan->serapanAnggaran->sum('realisasi');
            }

            // Hitung jumlah program unik
            if ($subKegiatan->relationLoaded('kegiatan') && $subKegiatan->kegiatan->relationLoaded('program')) {
                $programId = $subKegiatan->kegiatan->program->id;
                $programCount[$programId] = $subKegiatan->kegiatan->program->nama_program;
            }

            // Hitung jumlah kegiatan unik
            if ($subKegiatan->relationLoaded('kegiatan')) {
                $kegiatanId = $subKegiatan->kegiatan->id;
                $kegiatanCount[$kegiatanId] = $subKegiatan->kegiatan->nama_kegiatan;
            }
        }

        return [
            'total_sub_kegiatan' => $this->collection->count(),
            'total_kegiatan' => count($kegiatanCount),
            'total_program' => count($programCount),
            'total_anggaran' => $totalAnggaran,
            'total_anggaran_formatted' => 'Rp ' . number_format($totalAnggaran, 0, ',', '.'),
            'total_realisasi' => $totalRealisasi,
            'total_realisasi_formatted' => 'Rp ' . number_format($totalRealisasi, 0, ',', '.'),
            'persentase_realisasi_keseluruhan' => $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0,
            'sisa_anggaran' => $totalAnggaran - $totalRealisasi,
            'sisa_anggaran_formatted' => 'Rp ' . number_format($totalAnggaran - $totalRealisasi, 0, ',', '.'),
        ];
    }

    /**
     * Add additional meta data
     */
    public function with(Request $request): array
    {
        return [
            'status' => 'success',
            'message' => 'Data sub kegiatan berhasil diambil',
            'timestamp' => now()->toISOString(),
        ];
    }
}
