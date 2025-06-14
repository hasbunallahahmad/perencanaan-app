<?php

namespace App\Filament\Resources\RealisasiResource\Pages;

use App\Filament\Resources\RealisasiResource;
use App\Models\Realisasi;
use App\Models\RencanaAksi;
use App\Models\Bidang;
use App\Services\YearContext;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RekapRealisasi extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string $resource = RealisasiResource::class;
    protected static string $view = 'filament.resources.realisasi-resource.pages.rekap-realisasi';
    protected static ?string $title = 'Rekap Kegiatan';
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    public function getTableQuery(): Builder
    {
        // Get all RencanaAksi with their realisasi data
        return RencanaAksi::query()
            ->with([
                'bidang',
                'program',
                'kegiatan',
                'subKegiatan'
            ])
            ->withCount('realisasi')
            ->orderBy('created_at', 'desc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('program.nama_program')
                    ->label('Program')
                    ->sortable()
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Kegiatan')
                    ->sortable()
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('rencana_aksi_summary')
                    ->label('Rencana Aksi')
                    ->getStateUsing(function ($record) {
                        if (empty($record->rencana_aksi_list)) {
                            return 'Tidak ada data';
                        }

                        $count = count($record->rencana_aksi_list);
                        $firstAksi = $record->rencana_aksi_list[0]['aksi'] ?? 'N/A';

                        if ($count > 1) {
                            return sprintf(
                                '%s (+%d lainnya)',
                                strlen($firstAksi) > 30 ? substr($firstAksi, 0, 30) . '...' : $firstAksi,
                                $count - 1
                            );
                        }

                        return strlen($firstAksi) > 50 ? substr($firstAksi, 0, 50) . '...' : $firstAksi;
                    })
                    ->wrap()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $record = $column->getRecord();
                        if (empty($record->rencana_aksi_list)) {
                            return null;
                        }

                        $aksiList = collect($record->rencana_aksi_list)
                            ->pluck('aksi')
                            ->filter()
                            ->toArray();

                        return implode("\n• ", array_merge(['Daftar Rencana Aksi:'], $aksiList));
                    }),

                Tables\Columns\TextColumn::make('realisasi_count')
                    ->label('Jumlah Realisasi')
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state == 0 => 'danger',
                        $state < 3 => 'warning',
                        default => 'success'
                    }),

                Tables\Columns\TextColumn::make('total_peserta')
                    ->label('Total Peserta')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        return Realisasi::where('rencana_aksi_id', $record->id)
                            ->sum('jumlah_peserta');
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_anggaran')
                    ->label('Total Anggaran')
                    ->alignRight()
                    ->getStateUsing(function ($record) {
                        return Realisasi::where('rencana_aksi_id', $record->id)
                            ->sum('realisasi_anggaran');
                    })
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status_pelaksanaan')
                    ->label('Status')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $totalRencana = count($record->rencana_aksi_list ?? []);
                        $totalRealisasi = $record->realisasi_count;

                        if ($totalRealisasi == 0) {
                            return 'Belum Terlaksana';
                        } elseif ($totalRealisasi >= $totalRencana) {
                            return 'Selesai';
                        } else {
                            return 'Sebagian';
                        }
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Belum Terlaksana' => 'danger',
                        'Sebagian' => 'warning',
                        'Selesai' => 'success',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress (%)')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $totalRencana = count($record->rencana_aksi_list ?? []);
                        $totalRealisasi = $record->realisasi_count;

                        if ($totalRencana == 0) return '0%';

                        $percentage = round(($totalRealisasi / $totalRencana) * 100, 1);
                        return min($percentage, 100) . '%';
                    })
                    ->badge()
                    ->color(fn($state) => match (true) {
                        (float) str_replace('%', '', $state) == 0 => 'danger',
                        (float) str_replace('%', '', $state) < 50 => 'warning',
                        (float) str_replace('%', '', $state) < 100 => 'info',
                        default => 'success'
                    }),

                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bidang')
                    ->label('Bidang')
                    ->options(Bidang::where('aktif', true)->pluck('nama', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['value'])) {
                            return $query->where('bidang_id', $data['value']);
                        }
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pelaksanaan')
                    ->options([
                        'belum' => 'Belum Terlaksana',
                        'sebagian' => 'Sebagian',
                        'selesai' => 'Selesai'
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('realisasi', function ($q) use ($data) {
                            // This is a complex filter, we'll handle it in a different way
                        }, $data['value'] === 'belum' ? '=' : '>', $data['value'] === 'belum' ? 0 : 0);
                    }),

                Tables\Filters\Filter::make('progress')
                    ->form([
                        Forms\Components\TextInput::make('min_progress')
                            ->label('Progress Minimal (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('max_progress')
                            ->label('Progress Maksimal (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // This would need custom implementation based on progress calculation
                        return $query;
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('lihat_detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn($record) => 'Detail Rencana Aksi: ' . $record->kegiatan?->nama_kegiatan)
                    ->modalContent(function ($record) {
                        $realisasiList = Realisasi::where('rencana_aksi_id', $record->id)
                            ->orderBy('tanggal', 'desc')
                            ->get();

                        return view('filament.modals.detail-rencana-aksi', [
                            'rencanaAksi' => $record,
                            'realisasiList' => $realisasiList
                        ]);
                    })
                    ->modalWidth('7xl'),

                Tables\Actions\Action::make('tambah_realisasi')
                    ->label('Tambah Realisasi')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->url(fn($record) => route('filament.admin.resources.realisasis.create', [
                        'rencana_aksi_id' => $record->id
                    ])),

                Tables\Actions\Action::make('export_detail')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function ($record) {
                        // Implement export functionality
                        $this->exportDetailRealisasi($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            $this->exportMultipleRealisasi($records);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_all')
                ->label('Export Semua Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $this->exportAllRealisasi();
                }),

            Actions\Action::make('statistik')
                ->label('Lihat Statistik')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('Statistik Realisasi Kegiatan')
                ->modalContent(function () {
                    return $this->getStatistikContent();
                })
                ->modalWidth('6xl'),

            Actions\Action::make('filter_tahun')
                ->label('Tahun: ' . YearContext::getActiveYear())
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->form([
                    Forms\Components\Select::make('tahun')
                        ->label('Pilih Tahun')
                        ->options(function () {
                            $years = [];
                            $currentYear = (int) date('Y');
                            for ($i = $currentYear - 5; $i <= $currentYear + 2; $i++) {
                                $years[$i] = $i;
                            }
                            return $years;
                        })
                        ->default(YearContext::getActiveYear())
                        ->required(),
                ])
                ->action(function (array $data) {
                    YearContext::setActiveYear($data['tahun']);
                    $this->redirect(request()->url());
                }),
        ];
    }

    protected function exportDetailRealisasi($record)
    {
        // Implement export logic for single record
        // This could generate PDF, Excel, etc.
    }

    protected function exportMultipleRealisasi(Collection $records)
    {
        // Implement bulk export logic
    }

    protected function exportAllRealisasi()
    {
        // Implement export all logic
    }

    protected function getStatistikContent()
    {
        $stats = [
            'total_rencana' => RencanaAksi::count(),
            'total_realisasi' => Realisasi::count(),
            'total_peserta' => Realisasi::sum('jumlah_peserta'),
            'total_anggaran' => Realisasi::sum('realisasi_anggaran'),
            'realisasi_per_bidang' => Realisasi::join('rencana_aksi', 'realisasi.rencana_aksi_id', '=', 'rencana_aksi.id')
                ->join('bidang', 'rencana_aksi.bidang_id', '=', 'bidang.id')
                ->selectRaw('bidang.nama, COUNT(*) as total, SUM(jumlah_peserta) as peserta, SUM(realisasi_anggaran) as anggaran')
                ->groupBy('bidang.id', 'bidang.nama')
                ->get(),
        ];

        return view('filament.components.statistik-realisasi', compact('stats'));
    }

    public function getTitle(): string
    {
        return 'Rekap Realisasi Kegiatan - Tahun ' . YearContext::getActiveYear();
    }
}
