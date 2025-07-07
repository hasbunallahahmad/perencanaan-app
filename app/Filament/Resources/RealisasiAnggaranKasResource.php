<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RealisasiAnggaranKasResource\Pages;
use App\Models\RealisasiAnggaranKas;
use App\Models\RencanaAnggaranKas;
use App\Services\YearContext;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

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
                Forms\Components\Section::make('Pilih Rencana Anggaran')
                    ->schema([
                        Forms\Components\Select::make('rencana_anggaran_kas_id')
                            ->label('Rencana Anggaran Kas')
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
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $rencana = RencanaAnggaranKas::find($state);
                                    if ($rencana) {
                                        $set('tahun', $rencana->tahun);
                                        $set('deskripsi', $rencana->deskripsi);
                                    }
                                } else {
                                    $set('tahun', null);
                                    $set('deskripsi', null);
                                }
                            }),
                    ]),

                Forms\Components\Section::make('Detail Realisasi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Hidden::make('tahun'),
                                Forms\Components\Hidden::make('kategori'),

                                Forms\Components\Select::make('triwulan')
                                    ->label('Triwulan')
                                    ->options([
                                        '1' => 'Triwulan I',
                                        '2' => 'Triwulan II',
                                        '3' => 'Triwulan III',
                                        '4' => 'Triwulan IV',
                                    ])
                                    ->required()
                                    // ->default(function () {
                                    //     // Set default triwulan berdasarkan bulan saat ini
                                    //     $month = date('n');

                                    //     if ($month >= 1 && $month <= 3) {
                                    //         return 1;
                                    //     } elseif ($month >= 4 && $month <= 6) {
                                    //         return 2;
                                    //     } elseif ($month >= 7 && $month <= 9) {
                                    //         return 3;
                                    //     } else {
                                    //         return 4;
                                    //     }
                                    // })
                                    ->native(false),

                                Forms\Components\TextInput::make('jumlah_realisasi')
                                    ->label('Jumlah Realisasi (Rp)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->reactive()
                                    ->debounce(1000)
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $rencanaId = $get('rencana_anggaran_kas_id');
                                        if ($rencanaId && $state) {
                                            $rencana = RencanaAnggaranKas::find($rencanaId);
                                            if ($rencana && $rencana->jumlah_rencana > 0) {
                                                $percentage = round(($state / $rencana->jumlah_rencana) * 100, 2);
                                                $set('persentase_display', $percentage . '%');
                                            }
                                        }
                                    }),

                                Forms\Components\DatePicker::make('tanggal_realisasi')
                                    ->label('Tanggal Realisasi')
                                    ->required()
                                    ->default(now()),
                            ]),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Realisasi')
                            ->rows(3)
                            ->placeholder('Deskripsi detail mengenai realisasi anggaran'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('completed')
                            ->required(),

                        // Forms\Components\FileUpload::make('bukti_dokumen')
                        //     ->label('Bukti Dokumen Maksimal *5MB*')
                        //     ->directory('bukti-realisasi')
                        //     ->acceptedFileTypes(['application/pdf', 'image/*'])
                        //     ->maxSize(5120), // 5MB

                        Forms\Components\Textarea::make('catatan_realisasi')
                            ->label('Catatan Realisasi')
                            ->rows(3)
                            ->placeholder('Catatan tambahan mengenai realisasi'),

                        // Display field for percentage (read-only)
                        Forms\Components\TextInput::make('persentase_display')
                            ->label('Persentase Realisasi')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('%')
                            ->placeholder('0%'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('triwulan')
                    ->label('Triwulan')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => "TW $state")
                    ->colors([
                        'primary' => '1',
                        'success' => '2',
                        'warning' => '3',
                        'danger' => '4',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('rencanaAnggaranKas.jenis_anggaran_text')
                    ->label('Rencana (Rp)')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rencanaAnggaranKas.jumlah_rencana')
                    ->label('Rencana (Rp)')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('jumlah_realisasi')
                    ->label('Realisasi (Rp)')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('persentase_realisasi')
                    ->label('Persentase')
                    ->formatStateUsing(fn($record): string => $record->persentase_realisasi . '%')
                    ->badge()
                    ->color(fn($record): string => match (true) {
                        $record->persentase_realisasi >= 100 => 'success',
                        $record->persentase_realisasi >= 75 => 'warning',
                        default => 'danger',
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

                Tables\Columns\TextColumn::make('tanggal_realisasi')
                    ->label('Tanggal Realisasi')
                    ->date()
                    ->sortable(),

                // Tables\Columns\IconColumn::make('bukti_dokumen')
                //     ->label('Bukti')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-document-check')
                //     ->falseIcon('heroicon-o-document-minus')
                //     ->toggleable(),

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

                SelectFilter::make('triwulan')
                    ->label('Triwulan')
                    ->options([
                        '1' => 'Triwulan I',
                        '2' => 'Triwulan II',
                        '3' => 'Triwulan III',
                        '4' => 'Triwulan IV',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Action::make('complete')
                        ->label('Selesaikan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(RealisasiAnggaranKas $record): bool => $record->status === 'pending')
                        ->action(fn(RealisasiAnggaranKas $record) => $record->update(['status' => 'completed']))
                        ->requiresConfirmation()
                        ->modalHeading('Selesaikan Realisasi?')
                        ->modalDescription('Apakah Anda yakin ingin menandai realisasi ini sebagai selesai?')
                        ->modalSubmitActionLabel('Ya, Selesaikan'),

                    Action::make('cancel')
                        ->label('Batalkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(RealisasiAnggaranKas $record): bool => $record->status === 'pending')
                        ->action(fn(RealisasiAnggaranKas $record) => $record->update(['status' => 'cancelled']))
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Realisasi?')
                        ->modalDescription('Apakah Anda yakin ingin membatalkan realisasi ini?')
                        ->modalSubmitActionLabel('Ya, Batalkan'),

                    Action::make('downloadBukti')
                        ->label('Unduh Bukti')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->visible(fn(RealisasiAnggaranKas $record): bool => !empty($record->bukti_dokumen))
                        ->url(fn(RealisasiAnggaranKas $record): string => asset('storage/' . $record->bukti_dokumen))
                        ->openUrlInNewTab(),

                    // Action::make('viewRencana')
                    //     ->label('Lihat Rencana')
                    //     ->icon('heroicon-o-eye')
                    //     ->color('info')
                    //     ->url(
                    //         fn(RealisasiAnggaranKas $record): string =>
                    //         route('filament.admin.resources.rencana-anggaran-kas.view', $record->rencana_anggaran_kas_id)
                    //     )
                    //     ->openUrlInNewTab(),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulkComplete')
                        ->label('Selesaikan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update(['status' => 'completed']);
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Selesaikan Realisasi Terpilih?')
                        ->modalDescription('Apakah Anda yakin ingin menandai semua realisasi terpilih sebagai selesai?'),

                    Tables\Actions\BulkAction::make('bulkCancel')
                        ->label('Batalkan Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update(['status' => 'cancelled']);
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Realisasi Terpilih?')
                        ->modalDescription('Apakah Anda yakin ingin membatalkan semua realisasi terpilih?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada realisasi anggaran kas')
            ->emptyStateDescription('Mulai dengan membuat realisasi anggaran kas pertama Anda.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->modifyQueryUsing(function (Builder $query) {
                // Default filter by active year
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

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::byYear()->where('status', 'pending')->count();
    // }

    // public static function getNavigationBadgeColor(): ?string
    // {
    //     $count = static::getModel()::byYear()->where('status', 'pending')->count();
    //     return $count > 0 ? 'warning' : 'primary';
    // }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['rencanaAnggaranKas']);
    }
}
