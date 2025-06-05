<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;

class MigrateExistingDataToYear extends Command
{
    protected $signature = 'migrate:existing-data-to-year {year=2025}';
    protected $description = 'Migrate existing data to specific year';

    public function handle()
    {
        $year = $this->argument('year');

        $this->info("Migrating existing data to year: {$year}");

        // Update Program
        $programCount = Program::whereNull('tahun')->orWhere('tahun', 0)->count();
        Program::whereNull('tahun')->orWhere('tahun', 0)->update(['tahun' => $year]);
        $this->info("Updated {$programCount} program records");

        // Update Kegiatan
        $kegiatanCount = Kegiatan::whereNull('tahun')->orWhere('tahun', 0)->count();
        Kegiatan::whereNull('tahun')->orWhere('tahun', 0)->update(['tahun' => $year]);
        $this->info("Updated {$kegiatanCount} kegiatan records");

        // Update SubKegiatan
        $subKegiatanCount = SubKegiatan::whereNull('tahun')->orWhere('tahun', 0)->count();
        SubKegiatan::whereNull('tahun')->orWhere('tahun', 0)->update(['tahun' => $year]);
        $this->info("Updated {$subKegiatanCount} sub_kegiatan records");

        $this->info('Migration completed successfully!');
    }
}
