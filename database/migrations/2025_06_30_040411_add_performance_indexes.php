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
        // Add indexes to bidangs table for better query performance
        Schema::table('bidangs', function (Blueprint $table) {
            if (!$this->hasIndex('bidangs', 'idx_bidangs_aktif')) {
                $table->index('aktif', 'idx_bidangs_aktif');
            }
            if (!$this->hasIndex('bidangs', 'idx_bidangs_org_aktif')) {
                $table->index(['organisasi_id', 'aktif'], 'idx_bidangs_org_aktif');
            }
            if (!$this->hasIndex('bidangs', 'idx_bidangs_sekretariat')) {
                $table->index('is_sekretariat', 'idx_bidangs_sekretariat');
            }
            if (!$this->hasIndex('bidangs', 'idx_bidangs_nama')) {
                $table->index('nama', 'idx_bidangs_nama');
            }
        });

        // Add indexes to organisasis table
        Schema::table('organisasis', function (Blueprint $table) {
            if (!$this->hasIndex('organisasis', 'idx_organisasis_aktif')) {
                $table->index('aktif', 'idx_organisasis_aktif');
            }
            if (!$this->hasIndex('organisasis', 'idx_organisasis_nama')) {
                $table->index('nama', 'idx_organisasis_nama');
            }
        });

        // Add indexes to permission tables if they exist
        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                if (!$this->hasIndex('model_has_permissions', 'idx_model_permissions')) {
                    $table->index(['model_id', 'model_type'], 'idx_model_permissions');
                }
            });
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                if (!$this->hasIndex('model_has_roles', 'idx_model_roles')) {
                    $table->index(['model_id', 'model_type'], 'idx_model_roles');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bidangs', function (Blueprint $table) {
            if ($this->hasIndex('bidangs', 'idx_bidangs_aktif')) {
                $table->dropIndex('idx_bidangs_aktif');
            }
            if ($this->hasIndex('bidangs', 'idx_bidangs_org_aktif')) {
                $table->dropIndex('idx_bidangs_org_aktif');
            }
            if ($this->hasIndex('bidangs', 'idx_bidangs_sekretariat')) {
                $table->dropIndex('idx_bidangs_sekretariat');
            }
            if ($this->hasIndex('bidangs', 'idx_bidangs_nama')) {
                $table->dropIndex('idx_bidangs_nama');
            }
        });

        Schema::table('organisasis', function (Blueprint $table) {
            if ($this->hasIndex('organisasis', 'idx_organisasis_aktif')) {
                $table->dropIndex('idx_organisasis_aktif');
            }
            if ($this->hasIndex('organisasis', 'idx_organisasis_nama')) {
                $table->dropIndex('idx_organisasis_nama');
            }
        });

        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                if ($this->hasIndex('model_has_permissions', 'idx_model_permissions')) {
                    $table->dropIndex('idx_model_permissions');
                }
            });
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                if ($this->hasIndex('model_has_roles', 'idx_model_roles')) {
                    $table->dropIndex('idx_model_roles');
                }
            });
        }
    }

    /**
     * Check if index exists on table using raw SQL query
     */
    private function hasIndex(string $table, string $index): bool
    {
        $database = DB::connection()->getDatabaseName();

        $result = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = ? 
            AND index_name = ?
        ", [$database, $table, $index]);

        return $result[0]->count > 0;
    }
};
