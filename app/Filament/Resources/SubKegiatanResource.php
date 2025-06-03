<?php

namespace App\Http\Resources;

use App\Http\Resources\SerapanAnggaranResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubKegiatanResource extends JsonResource
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
            'kode_sub_kegiatan' => $this->kode_sub_kegiatan,
            'nama_sub_kegiatan' => $this->nama_sub_kegiatan,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relasi dengan kegiatan
            'kegiatan' => $this->whenLoaded('kegiatan', function () {
                return [
                    'id' => $this->kegiatan->id,
                    'kode_kegiatan' => $this->kegiatan->kode_kegiatan,
                    'nama_kegiatan' => $this->kegiatan->nama_kegiatan,

                    // Nested relasi dengan program
                    'program' => $this->whenLoaded('kegiatan.program', function () {
                        return [
                            'id' => $this->kegiatan->program->id,
                            'kode_program' => $this->kegiatan->program->kode_program,
                            'nama_program' => $this->kegiatan->program->nama_program,

                            // Nested relasi dengan organisasi
                            'organisasi' => $this->whenLoaded('kegiatan.program.organisasi', function () {
                                return [
                                    'id' => $this->kegiatan->program->organisasi->id,
                                    'nama' => $this->kegiatan->program->organisasi->nama,
                                ];
                            }),
                        ];
                    }),
                ];
            }),

            // Relasi dengan serapan anggaran
            'serapan_anggaran' => $this->whenLoaded('serapanAnggaran', function () {
                return SerapanAnggaranResource::collection($this->serapanAnggaran);
            }),

            // Summary serapan anggaran jika ada
            'total_anggaran' => $this->when(
                $this->relationLoaded('serapanAnggaran'),
                function () {
                    return $this->serapanAnggaran->sum('anggaran');
                }
            ),

            'total_realisasi' => $this->when(
                $this->relationLoaded('serapanAnggaran'),
                function () {
                    return $this->serapanAnggaran->sum('realisasi');
                }
            ),

            'persentase_realisasi' => $this->when(
                $this->relationLoaded('serapanAnggaran'),
                function () {
                    $totalAnggaran = $this->serapanAnggaran->sum('anggaran');
                    $totalRealisasi = $this->serapanAnggaran->sum('realisasi');

                    if ($totalAnggaran > 0) {
                        return round(($totalRealisasi / $totalAnggaran) * 100, 2);
                    }

                    return 0;
                }
            ),
        ];
    }

    /**
     * Resource untuk tampilan ringkas (list)
     */
    public static function collection($resource)
    {
        return parent::collection($resource)->additional([
            'meta' => [
                'total_records' => $resource->count()
            ]
        ]);
    }

    /**
     * Method untuk custom response dengan hanya field tertentu
     */
    public function minimal(): array
    {
        return [
            'id' => $this->id,
            'kode_sub_kegiatan' => $this->kode_sub_kegiatan,
            'nama_sub_kegiatan' => $this->nama_sub_kegiatan,
            'kode_kegiatan' => $this->kegiatan?->kode_kegiatan,
            'nama_kegiatan' => $this->kegiatan?->nama_kegiatan,
        ];
    }
}
