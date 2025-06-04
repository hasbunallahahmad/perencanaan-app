<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jika ada data existing, kita perlu mengkonversi dari enum ke array
        // Sebelum menjalankan migration utama, backup data yang ada

        // 1. Backup data existing jika ada
        $existingData = DB::table('sub_kegiatan')
            ->whereNotNull('sumber_dana')
            ->select('id_sub_kegiatan', 'sumber_dana')
            ->get();

        // 2. Drop kolom enum yang lama
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->dropColumn('sumber_dana');
        });

        // 3. Tambah kolom JSON yang baru
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->json('sumber_dana')->nullable()->after('id_kegiatan');
        });

        // 4. Restore data dengan format array JSON
        foreach ($existingData as $data) {
            DB::table('sub_kegiatan')
                ->where('id_sub_kegiatan', $data->id_sub_kegiatan)
                ->update([
                    'sumber_dana' => json_encode([$data->sumber_dana])
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup data JSON
        $existingData = DB::table('sub_kegiatan')
            ->whereNotNull('sumber_dana')
            ->select('id_sub_kegiatan', 'sumber_dana')
            ->get();

        // Drop kolom JSON
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->dropColumn('sumber_dana');
        });

        // Kembalikan ke enum
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->enum('sumber_dana', ['APBD', 'BANKEU', 'APBN', 'DBHCHT', 'DAK'])->nullable()->after('id_kegiatan');
        });

        // Restore data dengan mengambil elemen pertama dari array
        foreach ($existingData as $data) {
            $jsonData = json_decode($data->sumber_dana, true);
            if (is_array($jsonData) && !empty($jsonData)) {
                DB::table('sub_kegiatan')
                    ->where('id_sub_kegiatan', $data->id_sub_kegiatan)
                    ->update([
                        'sumber_dana' => $jsonData[0]
                    ]);
            }
        }
    }
};
