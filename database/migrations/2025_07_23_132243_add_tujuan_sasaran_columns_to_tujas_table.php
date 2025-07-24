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
        Schema::table('tujas', function (Blueprint $table) {
            // Add new columns with proper names
            $table->decimal('target_tujuan', 15, 3)->nullable()->after('indikator');
            $table->string('satuan_tujuan', 50)->nullable()->after('target_tujuan');
            $table->text('indikator_tujuan_text')->nullable()->after('satuan_tujuan');
            $table->decimal('realisasi_tujuan_tw_1', 15, 3)->default(0)->after('indikator_tujuan_text');
            $table->decimal('realisasi_tujuan_tw_2', 15, 3)->default(0)->after('realisasi_tujuan_tw_1');
            $table->decimal('realisasi_tujuan_tw_3', 15, 3)->default(0)->after('realisasi_tujuan_tw_2');
            $table->decimal('realisasi_tujuan_tw_4', 15, 3)->default(0)->after('realisasi_tujuan_tw_3');

            // Add sasaran columns
            $table->decimal('target_sasaran', 15, 3)->nullable()->after('realisasi_tujuan_tw_4');
            $table->string('satuan_sasaran', 50)->nullable()->after('target_sasaran');
            $table->text('indikator_sasaran_text')->nullable()->after('satuan_sasaran');
            $table->decimal('realisasi_sasaran_tw_1', 15, 3)->default(0)->after('indikator_sasaran_text');
            $table->decimal('realisasi_sasaran_tw_2', 15, 3)->default(0)->after('realisasi_sasaran_tw_1');
            $table->decimal('realisasi_sasaran_tw_3', 15, 3)->default(0)->after('realisasi_sasaran_tw_2');
            $table->decimal('realisasi_sasaran_tw_4', 15, 3)->default(0)->after('realisasi_sasaran_tw_3');
        });

        // Copy data from old columns to new columns, handle NULL values
        DB::statement('UPDATE tujas SET
            target_tujuan = target,
            satuan_tujuan = satuan,
            indikator_tujuan_text = indikator,
            realisasi_tujuan_tw_1 = COALESCE(realisasi_tw_1, 0),
            realisasi_tujuan_tw_2 = COALESCE(realisasi_tw_2, 0),
            realisasi_tujuan_tw_3 = COALESCE(realisasi_tw_3, 0),
            realisasi_tujuan_tw_4 = COALESCE(realisasi_tw_4, 0)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tujas', function (Blueprint $table) {
            $table->dropColumn([
                'target_tujuan',
                'satuan_tujuan',
                'indikator_tujuan_text',
                'realisasi_tujuan_tw_1',
                'realisasi_tujuan_tw_2',
                'realisasi_tujuan_tw_3',
                'realisasi_tujuan_tw_4',
                'target_sasaran',
                'satuan_sasaran',
                'indikator_sasaran_text',
                'realisasi_sasaran_tw_1',
                'realisasi_sasaran_tw_2',
                'realisasi_sasaran_tw_3',
                'realisasi_sasaran_tw_4'
            ]);
        });
    }
};
