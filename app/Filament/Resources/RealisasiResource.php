<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RealisasiResource\Pages;
use App\Models\Realisasi;
use App\Models\RencanaAksi;
use App\Models\Bidang;
use App\Services\YearContext;
use App\Traits\HasYearFilter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;


class RealisasiResource extends Resource
{
    use HasYearFilter;

    protected static ?string $model = Realisasi::class;

    protected static ?string $navigationGroup = 'Capaian Kinerja';
    protected static ?string $navigationLabel = 'Realisasi Renaksi';
    protected static ?string $modelLabel = 'Realisasi Renaksi';
    protected static ?string $pluralModelLabel = 'Realisasi Renaksi';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pilih Bidang')
                    ->description('Pilih Bidang anda terlebih dahulu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('rencana_aksi_id')
                                    ->label('Pilih Bidang')
                                    ->options(function () {
                                        return RencanaAksi::with(['bidang', 'program', 'kegiatan', 'subKegiatan'])
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                $label = sprintf(
                                                    '%s - %s - %s',
                                                    $item->bidang?->nama ?? 'N/A',
                                                    $item->program?->nama_program ?? 'N/A',
                                                    $item->kegiatan?->nama_kegiatan ?? 'N/A'
                                                );
                                                return [$item->id => $label];
                                            })
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        // Reset nama_aksi when rencana_aksi changes
                                        $set('nama_aksi', null);
                                    })
                                    ->columnSpan(2),
                            ]),

                        // Display selected rencana aksi details
                        Forms\Components\Placeholder::make('rencana_aksi_details')
                            ->label('Detail Bidang')
                            ->content(function (Get $get) {
                                $rencanaAksiId = $get('rencana_aksi_id');
                                if (!$rencanaAksiId) {
                                    return new HtmlString('<em class="text-gray-500">Pilih rencana aksi untuk melihat detail</em>');
                                }

                                $rencanaAksi = RencanaAksi::with(['bidang', 'program', 'kegiatan', 'subKegiatan'])
                                    ->find($rencanaAksiId);

                                if (!$rencanaAksi) {
                                    return new HtmlString('<em class="text-red-500">Rencana aksi tidak ditemukan</em>');
                                }

                                $html = '
                                <div class="space-y-2 p-4 bg-gray-50 rounded-lg">
                                    <div><strong>Bidang:</strong> ' . ($rencanaAksi->bidang?->nama ?? 'N/A') . '</div>
                                    <div><strong>Program:</strong> ' . ($rencanaAksi->program?->nama_program ?? 'N/A') . '</div>
                                    <div><strong>Kegiatan:</strong> ' . ($rencanaAksi->kegiatan?->nama_kegiatan ?? 'N/A') . '</div>
                                    <div><strong>Sub Kegiatan:</strong> ' . ($rencanaAksi->subKegiatan?->nama_sub_kegiatan ?? 'N/A') . '</div>
                                </div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Detail Realisasi')
                    ->description('Isi detail pelaksanaan kegiatan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // Perbaikan: Menggunakan rencana_aksi_list_index untuk menyimpan index
                                Select::make('rencana_aksi_list_index')
                                    ->label('Nama Aksi')
                                    ->options(function (Get $get) {
                                        $rencanaAksiId = $get('rencana_aksi_id');
                                        if (!$rencanaAksiId) {
                                            return [];
                                        }

                                        $rencanaAksi = RencanaAksi::find($rencanaAksiId);
                                        if (!$rencanaAksi || !$rencanaAksi->rencana_aksi_list) {
                                            return [];
                                        }

                                        $options = [];
                                        foreach ($rencanaAksi->rencana_aksi_list as $index => $aksi) {
                                            if (isset($aksi['aksi'])) {
                                                $options[$index] = $aksi['aksi'];
                                            }
                                        }

                                        return $options;
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        // Auto-fill nama_aksi based on selected index
                                        if ($state !== null) {
                                            $rencanaAksiId = $get('rencana_aksi_id');
                                            if ($rencanaAksiId) {
                                                $rencanaAksi = RencanaAksi::find($rencanaAksiId);
                                                if ($rencanaAksi && isset($rencanaAksi->rencana_aksi_list[$state]['aksi'])) {
                                                    $set('nama_aksi', $rencanaAksi->rencana_aksi_list[$state]['aksi']);
                                                }
                                            }
                                        }
                                    })
                                    ->columnSpan(2),

                                // Hidden field untuk menyimpan nama aksi yang sebenarnya
                                Forms\Components\Hidden::make('nama_aksi'),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Pelaksanaan')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(1),

                                TextInput::make('tempat')
                                    ->label('Tempat')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('narasumber')
                                    ->label('Narasumber')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('Data Peserta')
                    ->description('Isi data jumlah peserta kegiatan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('laki_laki')
                                    ->label('Laki-laki')
                                    ->numeric()
                                    ->minValue(0)
                                    ->reactive()
                                    ->columnSpan(1),

                                TextInput::make('perempuan')
                                    ->label('Perempuan')
                                    ->numeric()
                                    ->minValue(0)
                                    ->reactive()
                                    ->columnSpan(1),

                                Placeholder::make('jumlah_peserta_display')
                                    ->label('Total Peserta')
                                    ->content(function (Get $get) {
                                        $lakiLaki = (int) $get('laki_laki') ?: 0;
                                        $perempuan = (int) $get('perempuan') ?: 0;
                                        $total = $lakiLaki + $perempuan;

                                        return new HtmlString('<div class="text-lg font-semibold text-gray-900 dark:text-white">' . $total . '</div>');
                                    })
                                    ->columnSpan(1),

                                Hidden::make('jumlah_peserta')
                                    ->default(function (Get $get) {
                                        $lakiLaki = (int) $get('laki_laki') ?: 0;
                                        $perempuan = (int) $get('perempuan') ?: 0;
                                        return $lakiLaki + $perempuan;
                                    })
                                    ->live()
                                    ->afterStateHydrated(function ($state, Get $get, Set $set) {
                                        $lakiLaki = (int) $get('laki_laki') ?: 0;
                                        $perempuan = (int) $get('perempuan') ?: 0;
                                        $set('jumlah_peserta', $lakiLaki + $perempuan);
                                    })
                                    ->columnSpan(1),
                            ]),

                        Grid::make(columns: 1)
                            ->schema([
                                Textarea::make('asal_peserta')
                                    ->label('Asal Peserta')
                                    ->rows(3)
                                    ->placeholder('Tuliskan asal peserta (misal: Dinas A, Dinas B, dll)')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Anggaran & Dokumentasi')
                    ->description('Isi realisasi anggaran dan link dokumentasi')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('realisasi_anggaran')
                                    ->label('Realisasi Anggaran (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->columnSpan(1),

                                TextInput::make('foto_link_gdrive')
                                    ->label('Link Google Drive Foto')
                                    ->url()
                                    ->placeholder('https://drive.google.com/...')
                                    ->columnSpan(1),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->rows(3)
                            ->placeholder('Keterangan atau catatan tambahan tentang pelaksanaan kegiatan')
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rencanaAksi.bidang.nama')
                    ->label('Bidang')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('rencanaAksi.program.nama_program')
                    ->label('Program')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('rencanaAksi.kegiatan.nama_kegiatan')
                    ->label('Kegiatan')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('nama_aksi')
                    ->label('Nama Aksi')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tempat')
                    ->label('Tempat')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('narasumber')
                    ->label('Narasumber')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('jumlah_peserta')
                    ->label('Peserta')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        sprintf(
                            '%d (L:%d, P:%d)',
                            $state,
                            $record->laki_laki,
                            $record->perempuan
                        )
                    ),

                Tables\Columns\TextColumn::make('realisasi_anggaran')
                    ->label('Anggaran')
                    ->money('IDR')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('foto_link_gdrive')
                    ->label('Foto')
                    ->boolean()
                    ->trueIcon('heroicon-o-photo')
                    ->falseIcon('heroicon-o-x-mark')
                    ->alignCenter()
                    ->getStateUsing(fn($record) => !empty($record->foto_link_gdrive)),

                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bidang')
                    ->label('Bidang')
                    ->options(Bidang::where('aktif', true)->pluck('nama', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['value'])) {
                            return $query->whereHas('rencanaAksi', function ($q) use ($data) {
                                $q->where('bidang_id', $data['value']);
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
                Tables\Actions\Action::make('lihat_foto')
                    ->label('Foto')
                    ->icon('heroicon-o-photo')
                    ->color('success')
                    ->url(fn($record) => $record->foto_link_gdrive)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => !empty($record->foto_link_gdrive)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('tanggal', 'desc')
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
            'index' => Pages\ListRealisasis::route('/'),
            'create' => Pages\CreateRealisasi::route('/create'),
            'edit' => Pages\EditRealisasi::route('/{record}/edit'),
        ];
    }

    protected static function getYearColumn(): string
    {
        return 'tahun';
    }
}
