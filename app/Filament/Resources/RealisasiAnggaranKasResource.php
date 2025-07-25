<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RealisasiAnggaranKasResource\Pages;
use App\Models\RealisasiAnggaranKas;
use App\Models\RencanaAnggaranKas;
use App\Services\YearContext;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;

class RealisasiAnggaranKasResource extends Resource
{
    protected static ?string $model = RealisasiAnggaranKas::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Realisasi Anggaran Kas';

    protected static ?string $modelLabel = 'Realisasi Anggaran Kas';

    protected static ?string $pluralModelLabel = 'Realisasi Anggaran Kas';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Manajemen Anggaran';

    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tahun')
                                    ->label('Tahun')
                                    ->required()
                                    ->numeric()
                                    ->default(YearContext::getActiveYear())
                                    ->minValue(2020)
                                    ->maxValue(2030),

                                Select::make('rencana_anggaran_kas_id')
                                    ->label('Pilih Rencana Anggaran')
                                    ->options(function () {
                                        $activeYear = YearContext::getActiveYear();
                                        return RencanaAnggaranKas::where('status', 'approved')
                                            ->where('tahun', $activeYear)
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => "{$item->tahun} - {$item->jenis_anggaran_text} (Rp " . number_format($item->jumlah_rencana, 0, ',', '.') . ")"
                                                ];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        if ($state) {
                                            $rencana = RencanaAnggaranKas::find($state);
                                            if ($rencana) {
                                                $set('jenis_anggaran', $rencana->jenis_anggaran_text);
                                                $set('pagu', $rencana->jumlah_rencana);
                                            }
                                        }
                                    }),

                                TextInput::make('jenis_anggaran')
                                    ->label('Jenis Anggaran')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('pagu')
                                    ->label('Pagu (Total)')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefix('Rp')
                                    ->live()
                                    ->formatStateUsing(function ($state, $get, $record) {
                                        // PERBAIKAN: Gunakan data dari record saat edit
                                        if ($record && $record->rencanaAnggaranKas) {
                                            return number_format($record->rencanaAnggaranKas->jumlah_rencana, 0, ',', '.');
                                        }

                                        $rencanaId = $get('rencana_anggaran_kas_id');
                                        if ($rencanaId) {
                                            $rencana = RencanaAnggaranKas::find($rencanaId);
                                            if ($rencana && $rencana->jumlah_rencana) {
                                                return number_format($rencana->jumlah_rencana, 0, ',', '.');
                                            }
                                        }
                                        return $state ? number_format((float)$state, 0, ',', '.') : '0';
                                    }),
                            ]),
                    ]),

                Section::make('Rencana dan Realisasi Per Triwulan')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                // PERBAIKAN: Pastikan nilai dari database dimuat dengan benar
                                Section::make('Triwulan 1')
                                    ->schema([
                                        TextInput::make('rencana_tw_1')
                                            ->label('Rencana TW 1')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->required()
                                            ->reactive()
                                            // PERBAIKAN: Format nilai saat dimuat
                                            ->formatStateUsing(function ($state) {
                                                return $state ? (float)$state : 0;
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                static::calculatePercentage($get, $set);
                                            }),

                                        TextInput::make('realisasi_tw_1')
                                            ->label('Realisasi TW 1')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->reactive()
                                            // PERBAIKAN: Format nilai saat dimuat
                                            ->formatStateUsing(function ($state) {
                                                return $state ? (float)$state : 0;
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                static::calculatePercentage($get, $set);
                                            }),

                                        DatePicker::make('tanggal_realisasi_tw_1')
                                            ->label('Tanggal Realisasi')
                                            ->default(now()),
                                    ]),

                                // Ulangi untuk TW 2, 3, 4 dengan pola yang sama
                                Section::make('Triwulan 2')
                                    ->schema([
                                        TextInput::make('rencana_tw_2')
                                            ->label('Rencana TW 2')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->required()
                                            ->reactive()
                                            ->formatStateUsing(function ($state) {
                                                return $state ? (float)$state : 0;
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                static::calculatePercentage($get, $set);
                                            }),

                                        TextInput::make('realisasi_tw_2')
                                            ->label('Realisasi TW 2')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->reactive()
                                            ->formatStateUsing(function ($state) {
                                                return $state ? (float)$state : 0;
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                static::calculatePercentage($get, $set);
                                            }),

                                        DatePicker::make('tanggal_realisasi_tw_2')
                                            ->label('Tanggal Realisasi')
                                            ->default(now()),
                                    ]),

                                Section::make('Triwulan 3')
                                    ->schema([
                                        TextInput::make('rencana_tw_3')
                                            ->label('Rencana TW 3')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->required()
                                            ->reactive()
                                            ->formatStateUsing(function ($state) {
                                                return $state ? (float)$state : 0;
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                static::calculatePercentage($get, $set);
                                            }),

                                        TextInput::make('realisasi_tw_3')
                                            ->label('Realisasi TW 3')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->reactive()
                                            ->formatStateUsing(function ($state) {
                                                return $state ? (float)$state : 0;
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                static::calculatePercentage($get, $set);
                                            }),

                                        DatePicker::make('tanggal_realisasi_tw_3')
                                            ->label('Tanggal Realisasi')
                                            ->default(now()),
                                    ]),

                                Section::make('Triwulan 4')
                                    ->schema([
                                        TextInput::make('rencana_tw_4')
                                            ->label('Rencana TW 4')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->required()
                                            ->reactive()
                                            ->formatStateUsing(function ($state) {
                                                return $state ? (float)$state : 0;
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                static::calculatePercentage($get, $set);
                                            }),

                                        TextInput::make('realisasi_tw_4')
                                            ->label('Realisasi TW 4')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->reactive()
                                            ->formatStateUsing(function ($state) {
                                                return $state ? (float)$state : 0;
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                static::calculatePercentage($get, $set);
                                            }),

                                        DatePicker::make('tanggal_realisasi_tw_4')
                                            ->label('Tanggal Realisasi')
                                            ->default(now()),
                                    ]),
                            ]),
                    ]),

                Section::make('Realisasi Sampai Dengan (S/D)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('realisasi_sd_tw')
                                    ->label('Realisasi S/D TW')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->formatStateUsing(function ($state, $get) {
                                        $rencanaId = $get('rencana_anggaran_kas_id');
                                        if ($rencanaId) {
                                            $rencana = RencanaAnggaranKas::find($rencanaId);
                                            if ($rencana && $rencana->jumlah_rencana) {
                                                return number_format($rencana->jumlah_rencana, 0, ',', '.');
                                            }
                                        }
                                        return $state ? number_format((float)$state, 0, ',', '.') : '0';
                                    }),

                                TextInput::make('persentase_total')
                                    ->label('Persentase Total')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix('%'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                    ])
                                    ->default('completed')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Catatan')
                    ->schema([
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('catatan_realisasi')
                            ->label('Catatan Realisasi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function calculatePercentage(callable $get, callable $set): void
    {
        // Konversi semua nilai ke float dengan penanganan null dan string
        $totalRealisasi = collect([
            static::convertToFloat($get('realisasi_tw_1')),
            static::convertToFloat($get('realisasi_tw_2')),
            static::convertToFloat($get('realisasi_tw_3')),
            static::convertToFloat($get('realisasi_tw_4')),
        ])->sum();

        $totalRencana = collect([
            static::convertToFloat($get('rencana_tw_1')),
            static::convertToFloat($get('rencana_tw_2')),
            static::convertToFloat($get('rencana_tw_3')),
            static::convertToFloat($get('rencana_tw_4')),
        ])->sum();

        $set('realisasi_sd_tw', $totalRealisasi);

        // Hitung persentase berdasarkan total rencana
        if ($totalRencana > 0) {
            $persentase = round(($totalRealisasi / $totalRencana) * 100, 2);
            $set('persentase_total', $persentase);
        } else {
            $set('persentase_total', 0);
        }

        // Konversi pagu juga ke float
        $pagu = static::convertToFloat($get('pagu'));

        if ($totalRencana > $pagu && $pagu > 0) {
            \Filament\Notifications\Notification::make()
                ->title('Peringatan!')
                ->body('Total rencana (Rp ' . number_format($totalRencana, 0, ',', '.') . ') melebihi pagu (Rp ' . number_format($pagu, 0, ',', '.') . ')')
                ->warning()
                ->send();
        }
    }

    /**
     * Helper method untuk konversi nilai ke float
     */
    protected static function convertToFloat($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        if (is_string($value)) {
            // Hapus formatting seperti koma dan titik untuk currency
            $cleaned = str_replace([',', '.'], ['', '.'], $value);
            // Jika masih ada karakter non-numeric, ambil hanya angka
            $cleaned = preg_replace('/[^0-9.]/', '', $cleaned);
            return (float)$cleaned;
        }

        return (float)$value;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rencanaAnggaranKas.jenis_anggaran_text')
                    ->label('Jenis Anggaran')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rencanaAnggaranKas.jumlah_rencana')
                    ->label('Pagu')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                // Tampilkan kolom rencana per triwulan
                Tables\Columns\TextColumn::make('rencana_tw_1')
                    ->label('TW 1')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rencana_tw_2')
                    ->label('TW 2')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rencana_tw_3')
                    ->label('TW 3')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rencana_tw_4')
                    ->label(' TW 4')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_rencana')
                    ->label('Rencana')
                    ->money('IDR')
                    ->getStateUsing(function ($record) {
                        return static::convertToFloat($record->rencana_tw_1) +
                            static::convertToFloat($record->rencana_tw_2) +
                            static::convertToFloat($record->rencana_tw_3) +
                            static::convertToFloat($record->rencana_tw_4);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('realisasi_tw_1')
                    ->label(' TW 1')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('realisasi_tw_2')
                    ->label(' TW 2')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('realisasi_tw_3')
                    ->label(' TW 3')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('realisasi_tw_4')
                    ->label(' TW 4')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_realisasi')
                    ->label('Realisasi')
                    ->money('IDR')
                    ->getStateUsing(function ($record) {
                        return static::convertToFloat($record->realisasi_tw_1) +
                            static::convertToFloat($record->realisasi_tw_2) +
                            static::convertToFloat($record->realisasi_tw_3) +
                            static::convertToFloat($record->realisasi_tw_4);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('persentase_total')
                    ->label('Persentase')
                    ->formatStateUsing(function ($record) {
                        $totalRealisasi = static::convertToFloat($record->realisasi_tw_1) +
                            static::convertToFloat($record->realisasi_tw_2) +
                            static::convertToFloat($record->realisasi_tw_3) +
                            static::convertToFloat($record->realisasi_tw_4);

                        $totalRencana = static::convertToFloat($record->rencana_tw_1) +
                            static::convertToFloat($record->rencana_tw_2) +
                            static::convertToFloat($record->rencana_tw_3) +
                            static::convertToFloat($record->rencana_tw_4);

                        if ($totalRencana > 0) {
                            $persentase = round(($totalRealisasi / $totalRencana) * 100, 2);
                            return $persentase . '%';
                        }
                        return '0%';
                    })
                    ->badge()
                    ->color(function ($record) {
                        $totalRealisasi = static::convertToFloat($record->realisasi_tw_1) +
                            static::convertToFloat($record->realisasi_tw_2) +
                            static::convertToFloat($record->realisasi_tw_3) +
                            static::convertToFloat($record->realisasi_tw_4);

                        $totalRencana = static::convertToFloat($record->rencana_tw_1) +
                            static::convertToFloat($record->rencana_tw_2) +
                            static::convertToFloat($record->rencana_tw_3) +
                            static::convertToFloat($record->rencana_tw_4);

                        if ($totalRencana > 0) {
                            $persentase = round(($totalRealisasi / $totalRencana) * 100, 2);
                            return match (true) {
                                $persentase >= 100 => 'success',
                                $persentase >= 75 => 'warning',
                                default => 'danger',
                            };
                        }
                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'secondary' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = date('Y');
                        $years = [];
                        for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    })
                    ->default(YearContext::getActiveYear()),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Action::make('updateRealisasi')
                    ->label('Update Realisasi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('realisasi_tw_1')
                                    ->label('Realisasi TW 1')
                                    ->numeric()
                                    ->prefix('Rp'),

                                DatePicker::make('tanggal_realisasi_tw_1')
                                    ->label('Tanggal Realisasi TW 1'),

                                TextInput::make('realisasi_tw_2')
                                    ->label('Realisasi TW 2')
                                    ->numeric()
                                    ->prefix('Rp'),

                                DatePicker::make('tanggal_realisasi_tw_2')
                                    ->label('Tanggal Realisasi TW 2'),

                                TextInput::make('realisasi_tw_3')
                                    ->label('Realisasi TW 3')
                                    ->numeric()
                                    ->prefix('Rp'),

                                DatePicker::make('tanggal_realisasi_tw_3')
                                    ->label('Tanggal Realisasi TW 3'),

                                TextInput::make('realisasi_tw_4')
                                    ->label('Realisasi TW 4')
                                    ->numeric()
                                    ->prefix('Rp'),

                                DatePicker::make('tanggal_realisasi_tw_4')
                                    ->label('Tanggal Realisasi TW 4'),

                                Textarea::make('catatan_realisasi')
                                    ->label('Catatan Realisasi')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->fillForm(fn($record) => $record->toArray())
                    ->action(function ($record, array $data) {
                        $record->update($data);

                        $totalRealisasi = collect([
                            static::convertToFloat($data['realisasi_tw_1'] ?? 0),
                            static::convertToFloat($data['realisasi_tw_2'] ?? 0),
                            static::convertToFloat($data['realisasi_tw_3'] ?? 0),
                            static::convertToFloat($data['realisasi_tw_4'] ?? 0),
                        ])->sum();

                        $record->update(['realisasi_sd_tw' => $totalRealisasi]);

                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil!')
                            ->body('Realisasi berhasil diupdate')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Update Realisasi')
                    ->modalWidth('4xl'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada realisasi anggaran kas')
            ->emptyStateDescription('Mulai dengan membuat realisasi anggaran kas pertama Anda.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->modifyQueryUsing(function (Builder $query) {
                return $query->byYear();
            });
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
            'index' => Pages\ListRealisasiAnggaranKas::route('/'),
            'create' => Pages\CreateRealisasiAnggaranKas::route('/create'),
            'view' => Pages\ViewRealisasiAnggaranKas::route('/{record}'),
            'edit' => Pages\EditRealisasiAnggaranKas::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['rencanaAnggaranKas']);
    }
}
