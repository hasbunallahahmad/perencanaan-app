<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RencanaAksiResource\Pages;
use App\Models\RencanaAksi;
use App\Models\Bidang;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Services\YearContext;
use App\Traits\HasYearFilter;
use Faker\Core\Color;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RencanaAksiResource extends Resource
{
    use HasYearFilter;
    // use HasShieldPermissions;

    protected static ?string $model = RencanaAksi::class;

    protected static ?string $navigationGroup = 'Perencanaan';
    protected static ?string $navigationLabel = 'Rencana Aksi';
    protected static ?string $modelLabel = 'Rencana Aksi';
    protected static ?string $pluralModelLabel = 'Rencana Aksi';
    protected static ?int $navigationSort = 5;

    // Override query untuk filter berdasarkan tahun aktif
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tahun', YearContext::getActiveYear());
    }

    // Override table query untuk filter berdasarkan tahun aktif
    protected static function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('tahun', YearContext::getActiveYear());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step 1: Pemilihan Bidang dan Program
                    Wizard\Step::make('Pilih Bidang & Program')
                        ->description('Pilih bidang dan program yang akan digunakan')
                        ->icon('heroicon-o-folder')
                        ->schema([
                            Section::make('Informasi Dasar')
                                ->description('Pilih bidang dan program kerja')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('bidang_id')
                                                ->label('Bidang')
                                                ->options(
                                                    Bidang::where('aktif', true)
                                                        ->distinct()
                                                        ->orderBy('nama')
                                                        ->pluck('nama', 'id')
                                                )
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set) {
                                                    // $set('id_program', null);
                                                    $set('id_kegiatan', null);
                                                    $set('id_sub_kegiatan', null);
                                                })
                                                ->columnSpan(1),

                                            Select::make('id_program')
                                                ->label('Program')
                                                ->options(function () {
                                                    // Ambil seluruh program tanpa filter bidang
                                                    return Program::where('tahun', YearContext::getActiveYear())
                                                        ->distinct()
                                                        ->orderBy('nama_program')
                                                        ->pluck('nama_program', 'id_program');
                                                })
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set) {
                                                    // Reset kegiatan dan sub kegiatan ketika program berubah
                                                    $set('id_kegiatan', null);
                                                    $set('id_sub_kegiatan', null);
                                                })
                                                ->columnSpan(1),
                                        ]),
                                ])
                        ]),

                    // Step 2: Pemilihan Kegiatan dan Sub Kegiatan
                    Wizard\Step::make('Pilih Kegiatan')
                        ->description('Pilih kegiatan dan sub kegiatan')
                        ->icon('heroicon-o-clipboard-document')
                        ->schema([
                            Section::make('Kegiatan & Sub Kegiatan')
                                ->description('Pilih kegiatan dan sub kegiatan yang akan dilaksanakan')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('id_kegiatan')
                                                ->label('Kegiatan')
                                                ->getSearchResultsUsing(function (string $search) {
                                                    return Kegiatan::where('tahun', YearContext::getActiveYear())
                                                        ->where('nama_kegiatan', 'like', "%{$search}%")
                                                        ->distinct()
                                                        ->orderBy('nama_kegiatan')
                                                        ->limit(50) // Batasi hasil untuk performa
                                                        ->pluck('nama_kegiatan', 'id_kegiatan');
                                                })
                                                ->getOptionLabelUsing(function ($value) {
                                                    return Kegiatan::where('id_kegiatan', $value)->value('nama_kegiatan');
                                                })
                                                ->required()
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set) {
                                                    // Hanya reset sub kegiatan
                                                    $set('id_sub_kegiatan', null);
                                                })
                                                ->columnSpan(1),

                                            Select::make('id_sub_kegiatan')
                                                ->label('Sub Kegiatan')
                                                ->getSearchResultsUsing(function (string $search) {
                                                    return SubKegiatan::where('tahun', YearContext::getActiveYear())
                                                        ->where('nama_sub_kegiatan', 'like', "%{$search}%")
                                                        ->distinct()
                                                        ->orderBy('nama_sub_kegiatan')
                                                        ->limit(50) // Batasi hasil untuk performa
                                                        ->pluck('nama_sub_kegiatan', 'id_sub_kegiatan');
                                                })
                                                ->getOptionLabelUsing(function ($value) {
                                                    return SubKegiatan::where('id_sub_kegiatan', $value)->value('nama_sub_kegiatan');
                                                })
                                                ->required()
                                                ->searchable()
                                                ->columnSpan(1),
                                        ]),
                                ])
                        ]),

                    // Step 3: Rencana Aksi (tetap sama seperti sebelumnya)
                    Wizard\Step::make('Rencana Aksi')
                        ->description('Tambahkan rencana aksi yang akan dilakukan')
                        ->icon('heroicon-o-list-bullet')
                        ->schema([
                            Section::make('Daftar Rencana Aksi')
                                ->description('Tambahkan rencana aksi yang akan dilaksanakan beserta jadwal dan anggaran')
                                ->schema([
                                    Repeater::make('rencana_aksi_list')
                                        ->label('Rencana Aksi')
                                        ->schema([
                                            // Informasi Aksi - Full Width
                                            Section::make('Detail Rencana Aksi')
                                                ->schema([
                                                    TextInput::make('aksi')
                                                        ->label('Rencana Aksi')
                                                        ->required()
                                                        ->maxLength(500)
                                                        ->placeholder('Masukkan detail rencana aksi yang akan dilakukan...')
                                                        ->columnSpanFull(),
                                                ])
                                                ->columns(1),

                                            // Jadwal dan Waktu
                                            Section::make('Jadwal Pelaksanaan')
                                                ->schema([
                                                    Select::make('bulan')
                                                        ->label('Bulan Pelaksanaan')
                                                        ->options([
                                                            '01' => 'Januari',
                                                            '02' => 'Februari',
                                                            '03' => 'Maret',
                                                            '04' => 'April',
                                                            '05' => 'Mei',
                                                            '06' => 'Juni',
                                                            '07' => 'Juli',
                                                            '08' => 'Agustus',
                                                            '09' => 'September',
                                                            '10' => 'Oktober',
                                                            '11' => 'November',
                                                            '12' => 'Desember',
                                                        ])
                                                        ->required()
                                                        ->searchable()
                                                        ->multiple()
                                                        ->placeholder('Pilih bulan pelaksanaan...')
                                                        ->columnSpanFull(),
                                                ])
                                                ->columns(1),

                                            // Anggaran dan Narasumber dalam Grid
                                            Section::make('Anggaran & Narasumber')
                                                ->schema([
                                                    Grid::make(2)
                                                        ->schema([
                                                            CheckboxList::make('jenis_anggaran')
                                                                ->label('Jenis Anggaran')
                                                                ->options(RencanaAksi::JENIS_ANGGARAN_OPTIONS)
                                                                ->required()
                                                                ->columns(1)
                                                                ->gridDirection('row')
                                                                ->columnSpan(1),

                                                            CheckboxList::make('narasumber')
                                                                ->label('Narasumber')
                                                                ->options(RencanaAksi::NARASUMBER_OPTIONS)
                                                                ->required()
                                                                ->columns(1)
                                                                ->gridDirection('row')
                                                                ->columnSpan(1),
                                                        ]),
                                                ])
                                                ->columns(2),
                                        ])
                                        ->columns(1)
                                        ->defaultItems(1)
                                        ->addActionLabel('+ Tambah Rencana Aksi Baru')
                                        ->reorderable()
                                        ->collapsible()
                                        ->cloneable()
                                        ->itemLabel(
                                            fn(array $state): ?string =>
                                            !empty($state['aksi'])
                                                ? (strlen($state['aksi']) > 50
                                                    ? substr($state['aksi'], 0, 50) . '...'
                                                    : $state['aksi'])
                                                : 'Rencana Aksi Baru'
                                        )
                                        ->required()
                                        ->minItems(1)
                                        ->deleteAction(
                                            fn($action) => $action
                                                ->requiresConfirmation()
                                                ->modalHeading('Hapus Rencana Aksi')
                                                ->modalDescription('Apakah Anda yakin ingin menghapus rencana aksi ini?')
                                                ->modalSubmitActionLabel('Ya, Hapus')
                                        )
                                        ->extraAttributes([
                                            'class' => 'space-y-4'
                                        ]),
                                ]),
                            Actions::make([
                                Action::make('simpan')
                                    ->label('Simpan Data')
                                    ->submit('submit')
                                    ->icon('heroicon-o-check-circle')
                                    ->color('success')
                                    ->size('md')
                                    ->extraAttributes(['class' => 'w-md']),
                            ])
                                ->alignRight()
                                ->extraAttributes(['class' => 'mt-2']),
                        ]),
                ])
                    ->columnSpanFull()
                    ->nextAction(
                        fn($action) => $action
                            ->label('Selanjutnya')
                            ->icon('heroicon-o-arrow-right')
                    )
                    ->previousAction(
                        fn($action) => $action
                            ->label('Sebelumnya')
                            ->icon('heroicon-o-arrow-left')
                    )
                    ->skippable(true)
                    ->persistStepInQueryString()
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rencana_aksi_names')
                    ->label('Nama Rencana Aksi')
                    ->getStateUsing(function ($record) {
                        if (empty($record->rencana_aksi_list)) {
                            return 'Tidak ada data';
                        }

                        $aksiList = collect($record->rencana_aksi_list)
                            ->pluck('aksi')
                            ->filter()
                            ->map(function ($aksi) {
                                return strlen($aksi) > 100 ? substr($aksi, 0, 100) . '...' : $aksi;
                            })
                            ->toArray();

                        return implode(' | ', $aksiList);
                    })
                    ->html()
                    ->searchable()
                    ->wrap()
                    ->limit(200)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $record = $column->getRecord();
                        if (empty($record->rencana_aksi_list)) {
                            return null;
                        }

                        $aksiList = collect($record->rencana_aksi_list)
                            ->pluck('aksi')
                            ->filter()
                            ->toArray();

                        return implode("\n• ", array_merge([''], $aksiList));
                    }),

                // Sub Kegiatan
                Tables\Columns\TextColumn::make('subKegiatan.nama_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                // Jenis Anggaran
                Tables\Columns\TextColumn::make('jenis_anggaran_string')
                    ->label('Jenis Anggaran')
                    ->badge()
                    ->separator(',')
                    ->wrap()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!is_string($state) || strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('rencana_pelaksanaan_formatted')
                    ->label('Jadwal Pelaksanaan')
                    ->badge()
                    ->color('success'),

                // Narasumber
                Tables\Columns\TextColumn::make('narasumber_string')
                    ->label('Narasumber')
                    ->badge()
                    ->separator(',')
                    ->wrap()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!is_string($state) || strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                // Kolom tambahan yang bisa disembunyikan
                Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('program.nama_program')
                    ->label('Program')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Kegiatan')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rencana_aksi_count')
                    ->label('Jumlah Aksi')
                    ->getStateUsing(fn($record) => count($record->rencana_aksi_list ?? []))
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bidang_id')
                    ->label('Bidang')
                    ->options(function () {
                        // Perbaikan: Filter bidang berdasarkan tahun aktif juga
                        return Bidang::where('aktif', true)
                            ->distinct()
                            ->orderBy('nama')
                            ->pluck('nama', 'id');
                    })
                    ->searchable()
                    ->preload(),

                // Update filter untuk jenis_anggaran dengan query yang lebih spesifik
                Tables\Filters\SelectFilter::make('jenis_anggaran')
                    ->label('Jenis Anggaran')
                    ->options(RencanaAksi::JENIS_ANGGARAN_OPTIONS)
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['values'])) {
                            return $query->where(function (Builder $query) use ($data) {
                                foreach ($data['values'] as $value) {
                                    $query->orWhereJsonContains('jenis_anggaran', $value)
                                        ->orWhereRaw("JSON_SEARCH(JSON_EXTRACT(rencana_aksi_list, '$[*].jenis_anggaran'), 'one', ?) IS NOT NULL", [$value]);
                                }
                            });
                        }
                        return $query;
                    }),

                // Update filter untuk narasumber dengan query yang lebih spesifik
                Tables\Filters\SelectFilter::make('narasumber')
                    ->label('Narasumber')
                    ->options(RencanaAksi::NARASUMBER_OPTIONS)
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['values'])) {
                            return $query->where(function (Builder $query) use ($data) {
                                foreach ($data['values'] as $value) {
                                    $query->orWhereJsonContains('narasumber', $value)
                                        ->orWhereRaw("JSON_SEARCH(JSON_EXTRACT(rencana_aksi_list, '$[*].narasumber'), 'one', ?) IS NOT NULL", [$value]);
                                }
                            });
                        }
                        return $query;
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRencanaAksis::route('/'),
            'create' => Pages\CreateRencanaAksi::route('/create'),
            'view' => Pages\ViewRencanaAksi::route('/{record}'),
            'edit' => Pages\EditRencanaAksi::route('/{record}/edit'),
        ];
    }

    public static function formatBulanToName($bulan, $short = false): string
    {
        $longMonths = [
            '01' => 'Januari',
            '1' => 'Januari',
            '02' => 'Februari',
            '2' => 'Februari',
            '03' => 'Maret',
            '3' => 'Maret',
            '04' => 'April',
            '4' => 'April',
            '05' => 'Mei',
            '5' => 'Mei',
            '06' => 'Juni',
            '6' => 'Juni',
            '07' => 'Juli',
            '7' => 'Juli',
            '08' => 'Agustus',
            '8' => 'Agustus',
            '09' => 'September',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $shortMonths = [
            '01' => 'Jan',
            '1' => 'Jan',
            '02' => 'Feb',
            '2' => 'Feb',
            '03' => 'Mar',
            '3' => 'Mar',
            '04' => 'Apr',
            '4' => 'Apr',
            '05' => 'Mei',
            '5' => 'Mei',
            '06' => 'Jun',
            '6' => 'Jun',
            '07' => 'Jul',
            '7' => 'Jul',
            '08' => 'Ags',
            '8' => 'Ags',
            '09' => 'Sep',
            '9' => 'Sep',
            '10' => 'Okt',
            '11' => 'Nov',
            '12' => 'Des'
        ];

        $months = $short ? $shortMonths : $longMonths;
        return $months[trim($bulan)] ?? $bulan;
    }

    protected static function getYearColumn(): string
    {
        return 'tahun';
    }
}
