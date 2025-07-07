<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use App\Models\Organisasi;
use App\Models\MasterIndikator;
use App\Services\YearContext;
use App\Traits\HasYearFilter;
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
use Illuminate\Support\Facades\Auth;

class ProgramResource extends BaseResource
{
    protected static ?string $model = Program::class;

    // protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Program';
    protected static ?string $modelLabel = 'Program';
    protected static ?string $pluralModelLabel = 'Program';
    protected static ?int $navigationSort = 1;

    use HasYearFilter;
    protected static function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('tahun', YearContext::getActiveYear());
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tahun', YearContext::getActiveYear());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('tahun')
                    ->default(YearContext::getActiveYear()),
                Forms\Components\Section::make('Informasi Program')
                    ->description('Masukkan detail program')
                    ->schema([
                        TextInput::make('kode_program')
                            ->label('Kode Program')
                            ->required()
                            // ->unique(ignoreRecord: true)
                            ->rules([
                                function () {
                                    return function ($attribute, $value, $fail) {
                                        $tahun = request()->input('tahun');
                                        $recordId = request()->route('record'); // untuk edit

                                        $query = DB::table('program')
                                            ->where('kode_program', $value)
                                            ->where('tahun', $tahun);

                                        if ($recordId) {
                                            $query->where('id', '!=', $recordId);
                                        }

                                        if ($query->exists()) {
                                            $fail('Kode program sudah digunakan untuk tahun ' . $tahun);
                                        }
                                    };
                                }
                            ])
                            ->maxLength(20)
                            ->placeholder('Contoh: 2.08.01')
                            ->helperText('Format: X.XX.XX'),

                        TextInput::make('nama_program')
                            ->label('Nama Program')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Masukkan nama program'),

                        Select::make('indikator_id')
                            ->label('Indikator Utama')
                            ->relationship('indikator', 'nama_indikator')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Pilih indikator utama')
                            ->helperText('Indikator utama untuk mengukur kinerja program')
                            ->createOptionForm([
                                TextInput::make('nama_indikator')
                                    ->label('Nama Indikator')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama indikator baru'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $indikator = MasterIndikator::create($data);
                                return $indikator->id;
                            })
                            ->editOptionForm([
                                TextInput::make('nama_indikator')
                                    ->label('Nama Indikator')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columnSpan(1),

                        // INDIKATOR KEDUA
                        Select::make('indikator_id_2')
                            ->label('Indikator Kedua')
                            ->relationship('indikator2', 'nama_indikator')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Pilih indikator kedua (opsional)')
                            ->helperText('Indikator tambahan untuk mengukur kinerja program')
                            ->createOptionForm([
                                TextInput::make('nama_indikator')
                                    ->label('Nama Indikator')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama indikator baru'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $indikator = MasterIndikator::create($data);
                                return $indikator->id;
                            })
                            ->editOptionForm([
                                TextInput::make('nama_indikator')
                                    ->label('Nama Indikator')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->different('indikator_id')
                            ->columnSpan(1),

                        Select::make('organisasi_id')
                            ->label('Organisasi')
                            ->relationship('organisasi', 'nama')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih organisasi yang mengelola program ini')
                            ->columnSpan(1),
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

                // TextColumn::make('indikator_list')
                //     ->label('Indikator')
                //     ->searchable(['indikator.nama_indikator', 'indikator2.nama_indikator'])
                //     ->sortable(false)
                //     ->wrap()
                //     ->limit(60)
                //     ->placeholder('Tidak ada indikator')
                //     ->getStateUsing(function (Program $record): string {
                //         $indikators = [];

                //         if ($record->indikator) {
                //             $indikators[] = $record->indikator->nama_indikator;
                //         }

                //         if ($record->indikator2) {
                //             $indikators[] = $record->indikator2->nama_indikator;
                //         }

                //         return implode(', ', $indikators);
                //     })
                //     ->tooltip(function (TextColumn $column): ?string {
                //         $state = $column->getState();
                //         // Ensure $state is a string before using strlen()
                //         $stateString = is_string($state) ? $state : '';
                //         return strlen($stateString) > 60 ? $stateString : null;
                //     })
                //     ->badge()
                //     ->color('info')
                //     ->separator(', '),

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
                SelectFilter::make('indikator')
                    ->label('Indikator')
                    ->options(function () {
                        return MasterIndikator::pluck('nama_indikator', 'id')->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            return $query->where(function ($q) use ($data) {
                                $q->where('indikator_id', $data['value'])
                                    ->orWhere('indikator_id_2', $data['value']);
                            });
                        }
                        return $query;
                    })
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
                    ->hidden(fn() => Auth::user()->hasRole('panel_user'))
                    ->tooltip('Ubah'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->label('')
                    ->hidden(fn() => Auth::user()->hasRole('panel_user'))
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

                        TextEntry::make('indikator.nama_indikator')
                            ->label('Indikator Utama')
                            ->badge()
                            ->color('primary')
                            ->placeholder('Tidak ada indikator utama'),

                        TextEntry::make('indikator2.nama_indikator')
                            ->label('Indikator Kedua')
                            ->badge()
                            ->color('secondary')
                            ->placeholder('Tidak ada indikator kedua'),

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
        return [];
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

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['organisasi', 'indikator', 'indikator2']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'kode_program',
            'nama_program',
            'organisasi.nama',
            'indikator.nama_indikator',
            'indikator2.nama_indikator'
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [
            'Organisasi' => $record->organisasi?->nama,
            'Kategori' => $record->kategori,
        ];

        if ($record->indikator) {
            $details['Indikator Utama'] = $record->indikator->nama_indikator;
        }

        if ($record->indikator2) {
            $details['Indikator Kedua'] = $record->indikator2->nama_indikator;
        }

        return $details;
    }
}
