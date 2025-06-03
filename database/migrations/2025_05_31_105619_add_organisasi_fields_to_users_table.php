<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organisasi_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bidang_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('seksi_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organisasi_id']);
            $table->dropForeign(['bidang_id']);
            $table->dropForeign(['seksi_id']);
            $table->dropColumn(['organisasi_id', 'bidang_id', 'seksi_id']);
        });
    }
};
