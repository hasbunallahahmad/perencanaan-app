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
        // Step 1: Drop existing foreign key constraints safely
        Schema::table('kegiatan', function (Blueprint $table) {
            // Get existing foreign keys
            $foreignKeys = $this->getExistingForeignKeys('kegiatan');

            // Drop foreign key for indikator_id_2 if exists
            if (in_array('kegiatan_indikator_id_2_foreign', $foreignKeys)) {
                $table->dropForeign('kegiatan_indikator_id_2_foreign');
            }

            // Drop foreign key for indikator_id if exists
            if (in_array('kegiatan_indikator_id_foreign', $foreignKeys)) {
                $table->dropForeign('kegiatan_indikator_id_foreign');
            }
        });

        // Step 2: Remove unwanted columns
        Schema::table('kegiatan', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatan', 'indikator_id_2')) {
                $table->dropColumn('indikator_id_2');
            }
        });

        // Step 3: Add/modify indikator_id column
        Schema::table('kegiatan', function (Blueprint $table) {
            // Check if column exists, if not add it
            if (!Schema::hasColumn('kegiatan', 'indikator_id')) {
                $table->unsignedBigInteger('indikator_id')->nullable()->after('id_program');
            } else {
                // Modify existing column to ensure it's nullable and unsigned
                $table->unsignedBigInteger('indikator_id')->nullable()->change();
            }
        });

        // Step 4: Add foreign key constraint safely
        Schema::table('kegiatan', function (Blueprint $table) {
            // Check if master_indikator table exists before adding foreign key
            if (Schema::hasTable('master_indikator')) {
                $table->foreign('indikator_id', 'fk_kegiatan_indikator')
                    ->references('id')
                    ->on('master_indikator')
                    ->onDelete('set null');
            }
        });

        // Step 5: Drop pivot table if exists
        Schema::dropIfExists('kegiatan_indikator');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop foreign key constraint
        Schema::table('kegiatan', function (Blueprint $table) {
            $foreignKeys = $this->getExistingForeignKeys('kegiatan');

            if (in_array('fk_kegiatan_indikator', $foreignKeys)) {
                $table->dropForeign('fk_kegiatan_indikator');
            }
        });

        // Step 2: Add back indikator_id_2 column
        Schema::table('kegiatan', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatan', 'indikator_id_2')) {
                $table->unsignedBigInteger('indikator_id_2')->nullable()->after('indikator_id');
            }
        });

        // Step 3: Add foreign key for indikator_id_2
        Schema::table('kegiatan', function (Blueprint $table) {
            if (Schema::hasTable('master_indikator')) {
                $table->foreign('indikator_id_2', 'fk_kegiatan_indikator_id_2')
                    ->references('id')
                    ->on('master_indikator')
                    ->onDelete('set null');
            }
        });

        // Step 4: Recreate pivot table
        if (!Schema::hasTable('kegiatan_indikator')) {
            Schema::create('kegiatan_indikator', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kegiatan_id');
                $table->unsignedBigInteger('indikator_id');
                $table->timestamps();

                // Check if kegiatan table uses id_kegiatan or id as primary key
                $kegiatanPrimaryKey = $this->getKegiatanPrimaryKey();

                $table->foreign('kegiatan_id', 'fk_pivot_kegiatan')
                    ->references($kegiatanPrimaryKey)
                    ->on('kegiatan')
                    ->onDelete('cascade');

                $table->foreign('indikator_id', 'fk_pivot_indikator')
                    ->references('id')
                    ->on('master_indikator')
                    ->onDelete('cascade');

                $table->unique(['kegiatan_id', 'indikator_id'], 'unique_kegiatan_indikator');
            });
        }
    }

    /**
     * Get existing foreign keys for a table
     */
    private function getExistingForeignKeys(string $tableName): array
    {
        $databaseName = DB::connection()->getDatabaseName();

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$databaseName, $tableName]);

        return array_column($foreignKeys, 'CONSTRAINT_NAME');
    }

    /**
     * Get primary key column name for kegiatan table
     */
    private function getKegiatanPrimaryKey(): string
    {
        if (Schema::hasColumn('kegiatan', 'id_kegiatan')) {
            return 'id_kegiatan';
        }
        return 'id';
    }
};
