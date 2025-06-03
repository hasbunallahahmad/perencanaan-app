<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Filament\Resources\SubKegiatanResource;
use App\Http\Resources\SubKegiatanCollection;
use App\Models\SubKegiatan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubKegiatanController extends Controller
{
    // MENDAPATKAN DATA DENGAN PERSENTASE SERAPAN
    public function index()
    {
        $subKegiatans = SubKegiatan::with(['kegiatan.program.organisasi'])
            ->selectRaw('
                *,
                CASE 
                    WHEN anggaran = 0 OR anggaran IS NULL THEN 0
                    ELSE ROUND((realisasi / anggaran * 100), 2) 
                END as serapan_persen
            ')
            ->get();

        return response()->json($subKegiatans);
    }

    // FILTER BERDASARKAN SERAPAN
    public function bySerapan(Request $request)
    {
        $query = SubKegiatan::with(['kegiatan.program.organisasi']);

        // Filter serapan rendah (< 60%)
        if ($request->has('serapan_rendah')) {
            $query->serapanRendah(60);
        }

        // Filter serapan tinggi (>= 80%)
        if ($request->has('serapan_tinggi')) {
            $query->serapanTinggi(80);
        }

        // Filter range serapan
        if ($request->has('min_serapan') && $request->has('max_serapan')) {
            $query->bySerapanRange($request->min_serapan, $request->max_serapan);
        }

        $result = $query->selectRaw('
                *,
                CASE 
                    WHEN anggaran = 0 OR anggaran IS NULL THEN 0
                    ELSE ROUND((realisasi / anggaran * 100), 2) 
                END as serapan_persen
            ')
            ->orderBy('serapan_persen', 'desc')
            ->get();

        return response()->json($result);
    }

    // STATISTIK SERAPAN
    public function statistikSerapan()
    {
        $stats = SubKegiatan::selectRaw('
                COUNT(*) as total_sub_kegiatan,
                SUM(anggaran) as total_anggaran,
                SUM(realisasi) as total_realisasi,
                CASE 
                    WHEN SUM(anggaran) = 0 THEN 0
                    ELSE ROUND((SUM(realisasi) / SUM(anggaran) * 100), 2) 
                END as rata_rata_serapan,
                SUM(CASE WHEN (realisasi / NULLIF(anggaran, 0) * 100) >= 80 THEN 1 ELSE 0 END) as serapan_tinggi,
                SUM(CASE WHEN (realisasi / NULLIF(anggaran, 0) * 100) BETWEEN 60 AND 79.99 THEN 1 ELSE 0 END) as serapan_sedang,
                SUM(CASE WHEN (realisasi / NULLIF(anggaran, 0) * 100) < 60 THEN 1 ELSE 0 END) as serapan_rendah
            ')
            ->first();

        return response()->json($stats);
    }
}
