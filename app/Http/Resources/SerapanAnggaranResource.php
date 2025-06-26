<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SerapanAnggaranResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tahun' => $this->tahun,
            'bulan' => $this->bulan,
            'nama_bulan' => $this->getNamaBulan($this->bulan),
            'anggaran' => $this->anggaran,
            'anggaran_formatted' => 'Rp ' . number_format($this->anggaran, 0, ',', '.'),
            'realisasi' => $this->realisasi,
            'realisasi_formatted' => 'Rp ' . number_format($this->realisasi, 0, ',', '.'),
            'persentase_realisasi' => $this->anggaran > 0 ? round(($this->realisasi / $this->anggaran) * 100, 2) : 0,
            'sisa_anggaran' => $this->anggaran - $this->realisasi,
            'sisa_anggaran_formatted' => 'Rp ' . number_format($this->anggaran - $this->realisasi, 0, ',', '.'),
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relasi dengan sub kegiatan (opsional)
            'sub_kegiatan' => $this->whenLoaded('subKegiatan', function () {
                return [
                    'id' => $this->subKegiatan->id,
                    'kode_sub_kegiatan' => $this->subKegiatan->kode_sub_kegiatan,
                    'nama_sub_kegiatan' => $this->subKegiatan->nama_sub_kegiatan,
                ];
            }),
        ];
    }

    /**
     * Helper method untuk mengkonversi nomor bulan ke nama bulan
     */
    private function getNamaBulan(int $bulan): string
    {
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $namaBulan[$bulan] ?? 'Unknown';
    }
}
