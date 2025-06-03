<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubKegiatanResource;
use App\Http\Resources\SubKegiatanCollection;
use App\Models\SubKegiatan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubKegiatanController extends Controller
{
    /**
     * Display a listing of sub kegiatan
     */
    public function index(Request $request): JsonResponse
    {
        $query = SubKegiatan::with([
            'kegiatan.program.organisasi',
            'serapanAnggaran'
        ]);

        // Filter berdasarkan program jika ada
        if ($request->has('program_id')) {
            $query->whereHas('kegiatan.program', function ($q) use ($request) {
                $q->where('id', $request->program_id);
            });
        }

        // Filter berdasarkan kegiatan jika ada
        if ($request->has('kegiatan_id')) {
            $query->where('id_kegiatan', $request->kegiatan_id);
        }

        // Filter berdasarkan tahun anggaran jika ada
        if ($request->has('tahun')) {
            $query->whereHas('serapanAnggaran', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }

        // Search berdasarkan nama sub kegiatan
        if ($request->has('search')) {
            $query->where('nama_sub_kegiatan', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'kode_sub_kegiatan');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        if ($request->has('per_page')) {
            $subKegiatans = $query->paginate($request->per_page);
            return response()->json(new SubKegiatanCollection($subKegiatans));
        }

        $subKegiatans = $query->get();
        return response()->json(new SubKegiatanCollection($subKegiatans));
    }

    /**
     * Display the specified sub kegiatan
     */
    public function show(int $id): JsonResponse
    {
        $subKegiatan = SubKegiatan::with([
            'kegiatan.program.organisasi',
            'serapanAnggaran'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => new SubKegiatanResource($subKegiatan)
        ]);
    }

    /**
     * Get sub kegiatan minimal data (untuk dropdown/select)
     */
    public function minimal(Request $request): JsonResponse
    {
        $query = SubKegiatan::with('kegiatan');

        // Filter berdasarkan kegiatan jika ada
        if ($request->has('kegiatan_id')) {
            $query->where('id_kegiatan', $request->kegiatan_id);
        }

        $subKegiatans = $query->get();

        $data = $subKegiatans->map(function ($subKegiatan) {
            return (new SubKegiatanResource($subKegiatan))->minimal();
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Get sub kegiatan grouped by kegiatan
     */
    public function groupedByKegiatan(Request $request): JsonResponse
    {
        $query = SubKegiatan::with([
            'kegiatan.program.organisasi',
            'serapanAnggaran'
        ]);

        // Filter berdasarkan program jika ada
        if ($request->has('program_id')) {
            $query->whereHas('kegiatan.program', function ($q) use ($request) {
                $q->where('id', $request->program_id);
            });
        }

        $subKegiatans = $query->get();

        // Group by kegiatan
        $grouped = $subKegiatans->groupBy('kegiatan.id')->map(function ($items, $kegiatanId) {
            $firstItem = $items->first();
            return [
                'kegiatan' => [
                    'id' => $firstItem->kegiatan->id,
                    'kode_kegiatan' => $firstItem->kegiatan->kode_kegiatan,
                    'nama_kegiatan' => $firstItem->kegiatan->nama_kegiatan,
                    'program' => [
                        'id' => $firstItem->kegiatan->program->id,
                        'kode_program' => $firstItem->kegiatan->program->kode_program,
                        'nama_program' => $firstItem->kegiatan->program->nama_program,
                    ]
                ],
                'sub_kegiatans' => SubKegiatanResource::collection($items),
                'summary' => [
                    'total_sub_kegiatan' => $items->count(),
                    'total_anggaran' => $items->sum(function ($item) {
                        return $item->serapanAnggaran->sum('anggaran');
                    }),
                    'total_realisasi' => $items->sum(function ($item) {
                        return $item->serapanAnggaran->sum('realisasi');
                    }),
                ]
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $grouped
        ]);
    }

    /**
     * Get statistics/summary of sub kegiatan
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = SubKegiatan::with(['serapanAnggaran']);

        // Filter berdasarkan tahun jika ada
        if ($request->has('tahun')) {
            $query->whereHas('serapanAnggaran', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }

        $subKegiatans = $query->get();

        $totalAnggaran = $subKegiatans->sum(function ($item) {
            return $item->serapanAnggaran->sum('anggaran');
        });

        $totalRealisasi = $subKegiatans->sum(function ($item) {
            return $item->serapanAnggaran->sum('realisasi');
        });

        $statistics = [
            'total_sub_kegiatan' => $subKegiatans->count(),
            'sub_kegiatan_dengan_anggaran' => $subKegiatans->filter(function ($item) {
                return $item->serapanAnggaran->count() > 0;
            })->count(),
            'total_anggaran' => $totalAnggaran,
            'total_anggaran_formatted' => 'Rp ' . number_format($totalAnggaran, 0, ',', '.'),
            'total_realisasi' => $totalRealisasi,
            'total_realisasi_formatted' => 'Rp ' . number_format($totalRealisasi, 0, ',', '.'),
            'persentase_realisasi' => $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0,
            'sisa_anggaran' => $totalAnggaran - $totalRealisasi,
            'sisa_anggaran_formatted' => 'Rp ' . number_format($totalAnggaran - $totalRealisasi, 0, ',', '.'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $statistics
        ]);
    }
}
