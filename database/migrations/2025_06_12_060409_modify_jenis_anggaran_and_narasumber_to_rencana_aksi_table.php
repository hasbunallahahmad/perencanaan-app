<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Drop constraint check dengan pendekatan yang lebih kompatibel
        try {
            // Coba drop constraint dengan nama yang umum digunakan Laravel/MySQL
            DB::statement("ALTER TABLE rencana_aksi DROP CHECK rencana_aksi_jenis_anggaran_check");
        } catch (Exception $e) {
            // Ignore jika constraint tidak ada
        }

        try {
            DB::statement("ALTER TABLE rencana_aksi DROP CHECK `rencana_aksi.jenis_anggaran`");
        } catch (Exception $e) {
            // Ignore jika constraint tidak ada
        }

        try {
            DB::statement("ALTER TABLE rencana_aksi DROP CHECK rencana_aksi_narasumber_check");
        } catch (Exception $e) {
            // Ignore jika constraint tidak ada
        }

        // Step 2: Backup data existing jika ada
        $existingData = DB::table('rencana_aksi')
            ->whereNotNull('jenis_anggaran')
            ->orWhereNotNull('narasumber')
            ->get();

        // Step 3: Drop kolom dan buat ulang dengan tipe JSON
        try {
            Schema::table('rencana_aksi', function (Blueprint $table) {
                $table->dropColumn(['jenis_anggaran', 'narasumber']);
            });
        } catch (Exception $e) {
            // Jika gagal dengan schema builder, gunakan raw SQL
            DB::statement("ALTER TABLE rencana_aksi DROP COLUMN jenis_anggaran");
            DB::statement("ALTER TABLE rencana_aksi DROP COLUMN narasumber");
        }

        // Step 4: Tambah kolom baru dengan tipe JSON
        Schema::table('rencana_aksi', function (Blueprint $table) {
            $table->json('jenis_anggaran')->nullable()->after('id_kegiatan');
            $table->json('narasumber')->nullable()->after('jenis_anggaran');
        });

        // Step 5: Restore data jika ada (convert string ke JSON array)
        foreach ($existingData as $data) {
            $updateData = [];

            if ($data->jenis_anggaran) {
                $updateData['jenis_anggaran'] = json_encode([$data->jenis_anggaran]);
            }

            if ($data->narasumber) {
                $updateData['narasumber'] = json_encode([$data->narasumber]);
            }

            if (!empty($updateData)) {
                DB::table('rencana_aksi')
                    ->where('id', $data->id)
                    ->update($updateData);
            }
        }
    }

    public function down()
    {
        // Rollback: kembalikan ke tipe data semula
        DB::statement("ALTER TABLE rencana_aksi MODIFY COLUMN jenis_anggaran ENUM('APBD', 'APBN', 'DAK', 'DBHCHT', 'BANPEM') NULL");
        DB::statement("ALTER TABLE rencana_aksi MODIFY COLUMN narasumber TEXT NULL");
    }
};
