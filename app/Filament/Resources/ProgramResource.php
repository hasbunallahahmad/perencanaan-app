<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use App\Models\Organisasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\DB;
use Filament\Infolists\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Program';

    protected static ?string $modelLabel = 'Program';

    protected static ?string $pluralModelLabel = 'Program';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Program')
                    ->description('Masukkan detail program')
                    ->schema([
                        TextInput::make('kode_program')
                            ->label('Kode Program')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('Contoh: 2.08.01')
                            ->helperText('Format: X.XX.XX'),

                        TextInput::make('nama_program')
                            ->label('Nama Program')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Masukkan nama program'),

                        Select::make('organisasi_id')
                            ->label('Organisasi')
                            ->relationship('organisasi', 'nama')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('nama')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('kode')
                                    ->maxLength(50),
                                Textarea::make('alamat')
                                    ->maxLength(500),
                            ])
                            ->helperText('Pilih organisasi yang mengelola program ini'),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi Program')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->placeholder('Deskripsi singkat tentang program (opsional)')
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Anggaran & Realisasi')
                    ->schema([
                        Forms\Components\Placeholder::make('total_anggaran_display')
                            ->label('Total Anggaran')
                            ->content(function ($record) {
                                if (!$record) return 'Rp 0';
                                return $record->formatted_anggaran;
                            }),

                        Forms\Components\Placeholder::make('total_realisasi_display')
                            ->label('Total Realisasi')
                            ->content(function ($record) {
                                if (!$record) return 'Rp 0';
                                return $record->formatted_realisasi;
                            }),

                        Forms\Components\Placeholder::make('persentase_serapan_display')
                            ->label('Persentase Serapan Program')
                            ->content(function ($record) {
                                if (!$record) return '0%';
                                return number_format($record->persentase_serapan, 2) . '%';
                            }),

                        Forms\Components\Placeholder::make('total_kegiatan_display')
                            ->label('Total Kegiatan')
                            ->content(function ($record) {
                                if (!$record) return '0';
                                return $record->total_kegiatan;
                            }),

                        Forms\Components\Placeholder::make('total_sub_kegiatan_display')
                            ->label('Total Sub Kegiatan')
                            ->content(function ($record) {
                                if (!$record) return '0';
                                return $record->total_sub_kegiatan;
                            }),

                        Forms\Components\Placeholder::make('kategori_display')
                            ->label('Kategori Program')
                            ->content(function ($record) {
                                if (!$record) return '-';
                                return $record->kategori;
                            }),
                    ])
                    ->columns(3)
                    ->visible(fn($context) => $context === 'edit' || $context === 'view')
                    ->description('Anggaran dan realisasi dihitung otomatis dari total sub kegiatan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_program')
                    ->label('Kode Program')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode program berhasil disalin!')
                    ->weight('medium'),

                TextColumn::make('nama_program')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(80)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 80 ? $state : null;
                    }),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn($record) => $record->badge_color)
                    ->sortable(),

                TextColumn::make('total_anggaran')
                    ->label('Total Anggaran')
                    ->getStateUsing(function (Program $record): int {
                        return $record->total_anggaran;
                    })
                    ->money('IDR')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->leftJoin('kegiatan', 'program.id_program', '=', 'kegiatan.id_program')
                            ->leftJoin('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                            ->groupBy('program.id_program')
                            ->orderBy(DB::raw('SUM(COALESCE(sub_kegiatan.anggaran, 0))'), $direction)
                            ->select('program.*');
                    })
                    ->alignEnd(),

                TextColumn::make('total_realisasi')
                    ->label('Total Realisasi')
                    ->getStateUsing(function (Program $record): int {
                        return $record->total_realisasi;
                    })
                    ->money('IDR')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->leftJoin('kegiatan', 'program.id_program', '=', 'kegiatan.id_program')
                            ->leftJoin('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                            ->groupBy('program.id_program')
                            ->orderBy(DB::raw('SUM(COALESCE(sub_kegiatan.realisasi, 0))'), $direction)
                            ->select('program.*');
                    })
                    ->alignEnd(),

                TextColumn::make('persentase_serapan')
                    ->label('Serapan (%)')
                    ->getStateUsing(function (Program $record): string {
                        return number_format($record->persentase_serapan, 2);
                    })
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->leftJoin('kegiatan', 'program.id_program', '=', 'kegiatan.id_program')
                            ->leftJoin('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                            ->groupBy('program.id_program')
                            ->orderBy(DB::raw('
                                CASE 
                                    WHEN SUM(COALESCE(sub_kegiatan.anggaran, 0)) = 0 THEN 0
                                    ELSE (SUM(COALESCE(sub_kegiatan.realisasi, 0)) / SUM(COALESCE(sub_kegiatan.anggaran, 0)) * 100)
                                END
                            '), $direction)
                            ->select('program.*');
                    })
                    ->alignCenter()
                    ->badge()
                    ->color(function ($state) {
                        $value = (float) $state;
                        return match (true) {
                            $value >= 80 => 'success',
                            $value >= 60 => 'warning',
                            default => 'danger',
                        };
                    }),

                TextColumn::make('kegiatan_count')
                    ->label('Jumlah Kegiatan')
                    ->counts('kegiatan')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('sub_kegiatan_count')
                    ->label('Sub Kegiatan')
                    ->getStateUsing(function (Program $record): int {
                        return $record->total_sub_kegiatan;
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organisasi')
                    ->relationship('organisasi', 'nama')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('kategori')
                    ->options([
                        'Program Penunjang' => 'Program Penunjang',
                        'PUG & Pemberdayaan Perempuan' => 'PUG & Pemberdayaan Perempuan',
                        'Perlindungan Perempuan' => 'Perlindungan Perempuan',
                        'Peningkatan Kualitas Keluarga' => 'Peningkatan Kualitas Keluarga',
                        'Data Gender dan Anak' => 'Data Gender dan Anak',
                        'Pemenuhan Hak Anak' => 'Pemenuhan Hak Anak',
                        'Perlindungan Khusus Anak' => 'Perlindungan Khusus Anak',
                        'Administrasi Pemerintahan Desa' => 'Administrasi Pemerintahan Desa',
                        'Pemberdayaan Lembaga Kemasyarakatan' => 'Pemberdayaan Lembaga Kemasyarakatan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->where(function ($q) use ($data) {
                            foreach ($data['values'] as $kategori) {
                                $prefix = match ($kategori) {
                                    'Program Penunjang' => '2.08.01',
                                    'PUG & Pemberdayaan Perempuan' => '2.08.02',
                                    'Perlindungan Perempuan' => '2.08.03',
                                    'Peningkatan Kualitas Keluarga' => '2.08.04',
                                    'Data Gender dan Anak' => '2.08.05',
                                    'Pemenuhan Hak Anak' => '2.08.06',
                                    'Perlindungan Khusus Anak' => '2.08.07',
                                    'Administrasi Pemerintahan Desa' => '2.13.04',
                                    'Pemberdayaan Lembaga Kemasyarakatan' => '2.13.05',
                                    default => null,
                                };

                                if ($prefix) {
                                    $q->orWhere('kode_program', 'like', $prefix . '%');
                                }
                            }
                        });
                    })
                    ->multiple(),

                Tables\Filters\Filter::make('has_kegiatan')
                    ->label('Memiliki Kegiatan')
                    ->query(fn(Builder $query): Builder => $query->has('kegiatan'))
                    ->toggle(),

                Tables\Filters\Filter::make('serapan_rendah')
                    ->label('Serapan < 60%')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('subKegiatan')
                            ->havingRaw('
                                (SELECT SUM(realisasi) FROM sub_kegiatan 
                                 JOIN kegiatan ON sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan 
                                 WHERE kegiatan.id_program = program.id_program) / 
                                NULLIF((SELECT SUM(anggaran) FROM sub_kegiatan 
                                        JOIN kegiatan ON sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan 
                                        WHERE kegiatan.id_program = program.id_program), 0) * 100 < 60
                            ');
                    }),

                Tables\Filters\Filter::make('serapan_tinggi')
                    ->label('Serapan ≥ 80%')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('subKegiatan')
                            ->havingRaw('
                                (SELECT SUM(realisasi) FROM sub_kegiatan 
                                 JOIN kegiatan ON sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan 
                                 WHERE kegiatan.id_program = program.id_program) / 
                                NULLIF((SELECT SUM(anggaran) FROM sub_kegiatan 
                                        JOIN kegiatan ON sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan 
                                        WHERE kegiatan.id_program = program.id_program), 0) * 100 >= 80
                            ');
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat dari tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat sampai tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->label('')
                    ->tooltip('Detail'),
                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->label('')
                    ->tooltip('Ubah'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->label('')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Program')
                    ->modalDescription('Apakah Anda yakin ingin menghapus program ini? Semua data kegiatan dan sub kegiatan terkait juga akan ikut terhapus.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Program Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus program-program yang dipilih? Semua data kegiatan dan sub kegiatan terkait juga akan ikut terhapus.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->defaultSort('kode_program', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Program')
                    ->schema([
                        TextEntry::make('kode_program')
                            ->label('Kode Program')
                            ->copyable()
                            ->copyMessage('Kode program berhasil disalin!')
                            ->weight('bold'),

                        TextEntry::make('nama_program')
                            ->label('Nama Program')
                            ->columnSpanFull(),

                        TextEntry::make('organisasi.nama')
                            ->label('Organisasi')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('kategori')
                            ->label('Kategori Program')
                            ->badge()
                            ->color(fn($record) => $record->badge_color),

                        TextEntry::make('deskripsi')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada deskripsi'),
                    ])
                    ->columns(2),

                Section::make('Informasi Anggaran & Realisasi')
                    ->schema([
                        TextEntry::make('formatted_anggaran')
                            ->label('Total Anggaran')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('formatted_realisasi')
                            ->label('Total Realisasi')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('persentase_serapan')
                            ->label('Persentase Serapan')
                            ->formatStateUsing(fn($state) => number_format($state, 2) . '%')
                            ->badge()
                            ->color(function ($state) {
                                return match (true) {
                                    $state >= 80 => 'success',
                                    $state >= 60 => 'warning',
                                    default => 'danger',
                                };
                            }),
                    ])
                    ->columns(3),

                Section::make('Statistik Program')
                    ->schema([
                        TextEntry::make('total_kegiatan')
                            ->label('Total Kegiatan')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('total_sub_kegiatan')
                            ->label('Total Sub Kegiatan')
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Tambahkan relation manager jika diperlukan
            // KegiatanRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
            'view' => Pages\ViewProgram::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['organisasi']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_program', 'nama_program', 'organisasi.nama'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Organisasi' => $record->organisasi?->nama,
            'Kategori' => $record->kategori,
        ];
    }
}
