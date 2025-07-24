<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RealisasiTujuanSasaranResource\Pages;
use App\Models\RealisasiTujuanSasaran;
use App\Models\MasterTujuanSasaran;
use App\Models\MasterSasaran;
use App\Services\YearContext;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Enums\MaxWidth;

class RealisasiTujuanSasaranResource extends Resource
{
    protected static ?string $model = RealisasiTujuanSasaran::class;
    protected static ?string $navigationGroup = 'Capaian Kinerja';
    protected static ?string $navigationLabel = 'Realisasi Tujuan & Sasaran';
    protected static ?string $modelLabel = 'Realisasi Tujuan & Sasaran';
    protected static ?string $pluralModelLabel = 'Realisasi Tujuan & Sasaran';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun')
                            ->options(collect(YearContext::getAvailableYears())->mapWithKeys(fn($year) => [$year => $year]))
                            ->default(YearContext::getActiveYear())
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->columnSpan(1),
                    ])
                    ->columns(1),

                Section::make('Pilih Jenis Data')
                    ->schema([
                        Forms\Components\Radio::make('jenis_data')
                            ->label('Pilih jenis data yang akan diisi')
                            ->options([
                                'tujuan' => 'Data Tujuan',
                                'sasaran' => 'Data Sasaran',
                            ])
                            ->required()
                            ->reactive()
                            ->default(function ($record) {
                                if ($record) {
                                    // Set default berdasarkan data yang ada
                                    return $record->master_tujuan_sasarans_id ? 'tujuan' : 'sasaran';
                                }
                                return null;
                            })
                            ->afterStateUpdated(function ($state, $set) {
                                // Reset semua field ketika jenis data berubah
                                $set('master_tujuan_sasarans_id', null);
                                $set('master_sasaran_id', null);
                                $set('target_tahun', null);
                            })
                            ->columns(2),
                    ]),

                // Section untuk Data Tujuan
                Section::make('Data Tujuan')
                    ->schema([
                        Forms\Components\Select::make('master_tujuan_sasarans_id')
                            ->label('Pilih Tujuan')
                            ->options(function () {
                                return MasterTujuanSasaran::where('is_active', 1)
                                    ->pluck('tujuan', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                if ($state) {
                                    $tujuan = MasterTujuanSasaran::find($state);
                                    if ($tujuan) {
                                        // Hanya set target jika sedang create (tidak ada record)
                                        if (!$livewire->record) {
                                            $set('target_tahun', $tujuan->target);
                                        }
                                    }
                                } else {
                                    $set('target_tahun', null);
                                }
                            }),

                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Placeholder::make('indikator_tujuan_display')
                                    ->label('Indikator Tujuan')
                                    ->content(function ($get) {
                                        $tujuanId = $get('master_tujuan_sasarans_id');
                                        if ($tujuanId) {
                                            $tujuan = MasterTujuanSasaran::find($tujuanId);
                                            return $tujuan?->indikator_tujuan ?? '-';
                                        }
                                        return '-';
                                    }),

                                Forms\Components\TextInput::make('target_tahun')
                                    ->label('Target Tahun')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->placeholder('0.00')
                                    ->required()
                                    ->reactive()
                                    ->formatStateUsing(function ($record, $get, $livewire) {
                                        // Prioritaskan nilai dari record yang ada (mode edit)
                                        if ($record && isset($record->target_tahun)) {
                                            return $record->target_tahun;
                                        }

                                        // Jika mode create atau tidak ada record, ambil dari master
                                        $tujuanId = $get('master_tujuan_sasarans_id');
                                        if ($tujuanId) {
                                            $tujuan = MasterTujuanSasaran::find($tujuanId);
                                            return $tujuan?->target ?? 0;
                                        }

                                        return 0;
                                    }),

                                Forms\Components\Placeholder::make('satuan_tujuan_display')
                                    ->label('Satuan')
                                    ->content(function ($get) {
                                        $tujuanId = $get('master_tujuan_sasarans_id');
                                        if ($tujuanId) {
                                            $tujuan = MasterTujuanSasaran::find($tujuanId);
                                            return $tujuan?->satuan ?? '-';
                                        }
                                        return '-';
                                    }),

                                Forms\Components\Hidden::make('master_sasaran_id')
                                    ->default(null),
                            ]),
                    ])
                    ->visible(fn($get) => $get('jenis_data') === 'tujuan')
                    ->collapsible()
                    ->collapsed(false),

                // Section untuk Data Sasaran
                Section::make('Data Sasaran')
                    ->schema([
                        Forms\Components\Select::make('master_sasaran_id')
                            ->label('Pilih Sasaran')
                            ->options(function () {
                                return MasterSasaran::where('is_active', 1)
                                    ->pluck('sasaran', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                if ($state) {
                                    $sasaran = MasterSasaran::find($state);
                                    if ($sasaran) {
                                        // Hanya set target jika sedang create (tidak ada record)
                                        if (!$livewire->record) {
                                            $set('target_tahun', $sasaran->target);
                                        }
                                    }
                                } else {
                                    $set('target_tahun', null);
                                }
                            }),

                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Placeholder::make('indikator_sasaran_display')
                                    ->label('Indikator Sasaran')
                                    ->content(function ($get) {
                                        $sasaranId = $get('master_sasaran_id');
                                        if ($sasaranId) {
                                            $sasaran = MasterSasaran::find($sasaranId);
                                            return $sasaran?->indikator_sasaran ?? '-';
                                        }
                                        return '-';
                                    }),

                                Forms\Components\TextInput::make('target_tahun')
                                    ->label('Target Tahun')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->placeholder('0.00')
                                    ->required()
                                    ->reactive()
                                    ->formatStateUsing(function ($record, $get, $livewire) {
                                        // Prioritaskan nilai dari record yang ada (mode edit)
                                        if ($record && isset($record->target_tahun)) {
                                            return $record->target_tahun;
                                        }

                                        // Jika mode create atau tidak ada record, ambil dari master
                                        $sasaranId = $get('master_sasaran_id');
                                        if ($sasaranId) {
                                            $sasaran = MasterSasaran::find($sasaranId);
                                            return $sasaran?->target ?? 0;
                                        }

                                        return 0;
                                    }),

                                Forms\Components\Placeholder::make('satuan_sasaran_display')
                                    ->label('Satuan')
                                    ->content(function ($get) {
                                        $sasaranId = $get('master_sasaran_id');
                                        if ($sasaranId) {
                                            $sasaran = MasterSasaran::find($sasaranId);
                                            return $sasaran?->satuan ?? '-';
                                        }
                                        return '-';
                                    }),

                                Forms\Components\Hidden::make('master_tujuan_sasarans_id')
                                    ->default(null),
                            ]),
                    ])
                    ->visible(fn($get) => $get('jenis_data') === 'sasaran')
                    ->collapsible()
                    ->collapsed(false),

                // Section untuk Realisasi Per Triwulan
                Section::make('Realisasi Per Triwulan')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('realisasi_tw1')
                                        ->label('Realisasi TW1')
                                        ->numeric()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->placeholder('0.00')
                                        ->suffix(function ($get) {
                                            $jenisData = $get('jenis_data');
                                            if ($jenisData === 'tujuan') {
                                                $tujuanId = $get('master_tujuan_sasarans_id');
                                                if ($tujuanId) {
                                                    $tujuan = MasterTujuanSasaran::find($tujuanId);
                                                    return $tujuan?->satuan ?? 'Persen';
                                                }
                                            } elseif ($jenisData === 'sasaran') {
                                                $sasaranId = $get('master_sasaran_id');
                                                if ($sasaranId) {
                                                    $sasaran = MasterSasaran::find($sasaranId);
                                                    return $sasaran?->satuan ?? 'Persen';
                                                }
                                            }
                                            return 'Persen';
                                        }),

                                    Forms\Components\Toggle::make('verifikasi_tw1')
                                        ->label('Verifikasi TW1')
                                        ->default(false),

                                    Forms\Components\Select::make('status_tw1')
                                        ->label('Status TW1')
                                        ->options([
                                            'pending' => 'Pending',
                                            'verified' => 'Verified',
                                            'rejected' => 'Rejected',
                                        ])
                                        ->default('pending'),

                                    Forms\Components\FileUpload::make('dokumen_tw1')
                                        ->label('Dokumen TW1')
                                        ->multiple()
                                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                        ->maxSize(5120),
                                ])->columnSpan(1),

                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('realisasi_tw2')
                                        ->label('Realisasi TW2')
                                        ->numeric()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->placeholder('0.00')
                                        ->suffix(function ($get) {
                                            $jenisData = $get('jenis_data');
                                            if ($jenisData === 'tujuan') {
                                                $tujuanId = $get('master_tujuan_sasarans_id');
                                                if ($tujuanId) {
                                                    $tujuan = MasterTujuanSasaran::find($tujuanId);
                                                    return $tujuan?->satuan ?? 'Persen';
                                                }
                                            } elseif ($jenisData === 'sasaran') {
                                                $sasaranId = $get('master_sasaran_id');
                                                if ($sasaranId) {
                                                    $sasaran = MasterSasaran::find($sasaranId);
                                                    return $sasaran?->satuan ?? 'Persen';
                                                }
                                            }
                                            return 'Persen';
                                        }),

                                    Forms\Components\Toggle::make('verifikasi_tw2')
                                        ->label('Verifikasi TW2')
                                        ->default(false),

                                    Forms\Components\Select::make('status_tw2')
                                        ->label('Status TW2')
                                        ->options([
                                            'pending' => 'Pending',
                                            'verified' => 'Verified',
                                            'rejected' => 'Rejected',
                                        ])
                                        ->default('pending'),

                                    Forms\Components\FileUpload::make('dokumen_tw2')
                                        ->label('Dokumen TW2')
                                        ->multiple()
                                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                        ->maxSize(5120),
                                ])->columnSpan(1),

                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('realisasi_tw3')
                                        ->label('Realisasi TW3')
                                        ->numeric()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->placeholder('0.00')
                                        ->suffix(function ($get) {
                                            $jenisData = $get('jenis_data');
                                            if ($jenisData === 'tujuan') {
                                                $tujuanId = $get('master_tujuan_sasarans_id');
                                                if ($tujuanId) {
                                                    $tujuan = MasterTujuanSasaran::find($tujuanId);
                                                    return $tujuan?->satuan ?? 'Persen';
                                                }
                                            } elseif ($jenisData === 'sasaran') {
                                                $sasaranId = $get('master_sasaran_id');
                                                if ($sasaranId) {
                                                    $sasaran = MasterSasaran::find($sasaranId);
                                                    return $sasaran?->satuan ?? 'Persen';
                                                }
                                            }
                                            return 'Persen';
                                        }),

                                    Forms\Components\Toggle::make('verifikasi_tw3')
                                        ->label('Verifikasi TW3')
                                        ->default(false),

                                    Forms\Components\Select::make('status_tw3')
                                        ->label('Status TW3')
                                        ->options([
                                            'pending' => 'Pending',
                                            'verified' => 'Verified',
                                            'rejected' => 'Rejected',
                                        ])
                                        ->default('pending'),

                                    Forms\Components\FileUpload::make('dokumen_tw3')
                                        ->label('Dokumen TW3')
                                        ->multiple()
                                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                        ->maxSize(5120),
                                ])->columnSpan(1),

                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('realisasi_tw4')
                                        ->label('Realisasi TW4')
                                        ->numeric()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->placeholder('0.00')
                                        ->suffix(function ($get) {
                                            $jenisData = $get('jenis_data');
                                            if ($jenisData === 'tujuan') {
                                                $tujuanId = $get('master_tujuan_sasarans_id');
                                                if ($tujuanId) {
                                                    $tujuan = MasterTujuanSasaran::find($tujuanId);
                                                    return $tujuan?->satuan ?? 'Persen';
                                                }
                                            } elseif ($jenisData === 'sasaran') {
                                                $sasaranId = $get('master_sasaran_id');
                                                if ($sasaranId) {
                                                    $sasaran = MasterSasaran::find($sasaranId);
                                                    return $sasaran?->satuan ?? 'Persen';
                                                }
                                            }
                                            return 'Persen';
                                        }),

                                    Forms\Components\Toggle::make('verifikasi_tw4')
                                        ->label('Verifikasi TW4')
                                        ->default(false),

                                    Forms\Components\Select::make('status_tw4')
                                        ->label('Status TW4')
                                        ->options([
                                            'pending' => 'Pending',
                                            'verified' => 'Verified',
                                            'rejected' => 'Rejected',
                                        ])
                                        ->default('pending'),

                                    Forms\Components\FileUpload::make('dokumen_tw4')
                                        ->label('Dokumen TW4')
                                        ->multiple()
                                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                        ->maxSize(5120),
                                ])->columnSpan(1),
                            ]),
                    ])
                    ->visible(fn($get) => $get('jenis_data') && ($get('master_tujuan_sasarans_id') || $get('master_sasaran_id')))
                    ->description('Input realisasi untuk setiap triwulan'),
            ]);
    }

    // ... rest of the table method remains the same
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tujuan' => 'success',
                        'sasaran' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Tujuan/Sasaran')
                    ->searchable()
                    ->wrap()
                    ->limit(50)
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        // Ambil nama dari relasi atau fallback ke field langsung
                        if ($record->masterTujuanSasaran) {
                            return $record->masterTujuanSasaran->tujuan ?? '-';
                        } elseif ($record->masterSasaran) {
                            return $record->masterSasaran->sasaran ?? '-';
                        }
                        return $record->nama ?? '-';
                    })
                    ->tooltip(function (RealisasiTujuanSasaran $record): string {
                        if ($record->masterTujuanSasaran) {
                            return $record->masterTujuanSasaran->tujuan ?? '-';
                        } elseif ($record->masterSasaran) {
                            return $record->masterSasaran->sasaran ?? '-';
                        }
                        return $record->nama ?? '-';
                    }),

                Tables\Columns\TextColumn::make('indikator')
                    ->label('Indikator')
                    ->limit(75)
                    ->wrap()
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        // Ambil indikator dari relasi atau fallback ke field langsung
                        if ($record->masterTujuanSasaran) {
                            return $record->masterTujuanSasaran->indikator_tujuan ?? '-';
                        } elseif ($record->masterSasaran) {
                            return $record->masterSasaran->indikator_sasaran ?? '-';
                        }
                        return $record->indikator ?? '-';
                    })
                    ->tooltip(function (RealisasiTujuanSasaran $record): string {
                        if ($record->masterTujuanSasaran) {
                            return $record->masterTujuanSasaran->indikator_tujuan ?? '-';
                        } elseif ($record->masterSasaran) {
                            return $record->masterSasaran->indikator_sasaran ?? '-';
                        }
                        return $record->indikator ?? '-';
                    }),

                Tables\Columns\TextColumn::make('target_tahun')
                    ->label('Target')
                    ->numeric(2)
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        $value = $record->target_tahun ?? 0;
                        $satuan = '';

                        // Ambil satuan dari relasi
                        if ($record->masterTujuanSasaran) {
                            $satuan = $record->masterTujuanSasaran->satuan ?? '%';
                        } elseif ($record->masterSasaran) {
                            $satuan = $record->masterSasaran->satuan ?? '%';
                        } else {
                            $satuan = '%';
                        }

                        return number_format($value, 2) . ' ' . $satuan;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('realisasi_tw1')
                    ->label('TW1')
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        $value = $record->realisasi_tw1 ?? 0;
                        $satuan = '';

                        if ($record->masterTujuanSasaran) {
                            $satuan = $record->masterTujuanSasaran->satuan ?? '%';
                        } elseif ($record->masterSasaran) {
                            $satuan = $record->masterSasaran->satuan ?? '%';
                        } else {
                            $satuan = '%';
                        }

                        return number_format($value, 2) . ' ' . $satuan;
                    })
                    ->color(fn($record) => ($record->realisasi_tw1 ?? 0) > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('realisasi_tw2')
                    ->label('TW2')
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        $value = $record->realisasi_tw2 ?? 0;
                        $satuan = '';

                        if ($record->masterTujuanSasaran) {
                            $satuan = $record->masterTujuanSasaran->satuan ?? '%';
                        } elseif ($record->masterSasaran) {
                            $satuan = $record->masterSasaran->satuan ?? '%';
                        } else {
                            $satuan = '%';
                        }

                        return number_format($value, 2) . ' ' . $satuan;
                    })
                    ->color(fn($record) => ($record->realisasi_tw2 ?? 0) > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('realisasi_tw3')
                    ->label('TW3')
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        $value = $record->realisasi_tw3 ?? 0;
                        $satuan = '';

                        if ($record->masterTujuanSasaran) {
                            $satuan = $record->masterTujuanSasaran->satuan ?? '%';
                        } elseif ($record->masterSasaran) {
                            $satuan = $record->masterSasaran->satuan ?? '%';
                        } else {
                            $satuan = '%';
                        }

                        return number_format($value, 2) . ' ' . $satuan;
                    })
                    ->color(fn($record) => ($record->realisasi_tw3 ?? 0) > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('realisasi_tw4')
                    ->label('TW4')
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        $value = $record->realisasi_tw4 ?? 0;
                        $satuan = '';

                        if ($record->masterTujuanSasaran) {
                            $satuan = $record->masterTujuanSasaran->satuan ?? '%';
                        } elseif ($record->masterSasaran) {
                            $satuan = $record->masterSasaran->satuan ?? '%';
                        } else {
                            $satuan = '%';
                        }

                        return number_format($value, 2) . ' ' . $satuan;
                    })
                    ->color(fn($record) => ($record->realisasi_tw4 ?? 0) > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('rata_rata_realisasi')
                    ->label('Rata-rata')
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        // Hitung rata-rata dari triwulan yang memiliki nilai > 0
                        $realisasi = [
                            $record->realisasi_tw1 ?? 0,
                            $record->realisasi_tw2 ?? 0,
                            $record->realisasi_tw3 ?? 0,
                            $record->realisasi_tw4 ?? 0,
                        ];

                        // Filter nilai yang > 0 untuk menghitung rata-rata
                        $validRealisasi = array_filter($realisasi, fn($val) => $val > 0);
                        $rataRata = count($validRealisasi) > 0 ? array_sum($validRealisasi) / count($validRealisasi) : 0;

                        $satuan = '';
                        if ($record->masterTujuanSasaran) {
                            $satuan = $record->masterTujuanSasaran->satuan ?? '%';
                        } elseif ($record->masterSasaran) {
                            $satuan = $record->masterSasaran->satuan ?? '%';
                        } else {
                            $satuan = '%';
                        }

                        return number_format($rataRata, 2) . ' ' . $satuan;
                    })
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('persentase_pencapaian')
                    ->label('Pencapaian')
                    ->getStateUsing(function (RealisasiTujuanSasaran $record): string {
                        $target = $record->target_tahun ?? 0;

                        if ($target <= 0) {
                            return '0.00%';
                        }

                        // Hitung rata-rata realisasi
                        $realisasi = [
                            $record->realisasi_tw1 ?? 0,
                            $record->realisasi_tw2 ?? 0,
                            $record->realisasi_tw3 ?? 0,
                            $record->realisasi_tw4 ?? 0,
                        ];

                        $totalRealisasi = array_sum($realisasi);
                        $rataRata = $totalRealisasi / 4;

                        // Hitung persentase pencapaian berdasarkan rata-rata
                        $persentase = ($rataRata / $target) * 100;

                        return number_format($persentase, 2) . '%';
                    })
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        $target = $record->target_tahun ?? 0;

                        if ($target <= 0) {
                            return 'gray';
                        }

                        $realisasi = [
                            $record->realisasi_tw1 ?? 0,
                            $record->realisasi_tw2 ?? 0,
                            $record->realisasi_tw3 ?? 0,
                            $record->realisasi_tw4 ?? 0,
                        ];

                        $validRealisasi = array_filter($realisasi, fn($val) => $val > 0);
                        $rataRata = count($validRealisasi) > 0 ? array_sum($validRealisasi) / count($validRealisasi) : 0;
                        $persentase = ($rataRata / $target) * 100;

                        if ($persentase >= 100) return 'success';
                        if ($persentase >= 75) return 'warning';
                        if ($persentase >= 50) return 'info';
                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(collect(YearContext::getAvailableYears())->mapWithKeys(fn($year) => [$year => $year]))
                    ->default(YearContext::getActiveYear())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $year): Builder => $query->byYear($year),
                        );
                    }),
                SelectFilter::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'tujuan' => 'Tujuan',
                        'sasaran' => 'Sasaran',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === 'tujuan',
                            fn(Builder $query): Builder => $query->tujuanOnly(),
                        )->when(
                            $data['value'] === 'sasaran',
                            fn(Builder $query): Builder => $query->sasaranOnly(),
                        );
                    }),


                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $status): Builder {
                                return $query->where(function ($q) use ($status) {
                                    $q->where('status_tw1', $status)
                                        ->orWhere('status_tw2', $status)
                                        ->orWhere('status_tw3', $status)
                                        ->orWhere('status_tw4', $status);
                                });
                            },
                        );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ], layout: FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->actions([
                // Action untuk Edit Realisasi dalam Modal
                Tables\Actions\Action::make('edit_realisasi')
                    ->label('Edit Realisasi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading(fn($record) => 'Edit Realisasi - ' . ($record->masterTujuanSasaran?->tujuan ?? $record->masterSasaran?->sasaran ?? 'Data'))
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->fillForm(function ($record) {
                        // Pre-fill form dengan data dari database
                        return [
                            'realisasi_tw1' => $record->realisasi_tw1,
                            'realisasi_tw2' => $record->realisasi_tw2,
                            'realisasi_tw3' => $record->realisasi_tw3,
                            'realisasi_tw4' => $record->realisasi_tw4,
                            'status_tw1' => $record->status_tw1 ?? 'pending',
                            'status_tw2' => $record->status_tw2 ?? 'pending',
                            'status_tw3' => $record->status_tw3 ?? 'pending',
                            'status_tw4' => $record->status_tw4 ?? 'pending',
                        ];
                    })
                    ->form(function ($record) {
                        $tipe = $record->master_tujuan_sasarans_id ? 'tujuan' : 'sasaran';
                        $satuan = '';

                        if ($tipe === 'tujuan' && $record->masterTujuanSasaran) {
                            $satuan = $record->masterTujuanSasaran->satuan ?? '%';
                        } elseif ($tipe === 'sasaran' && $record->masterSasaran) {
                            $satuan = $record->masterSasaran->satuan ?? '%';
                        } else {
                            $satuan = '%';
                        }

                        return [
                            Section::make('Informasi Data')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Placeholder::make('nama_display')
                                                ->label($tipe === 'tujuan' ? 'Tujuan' : 'Sasaran')
                                                ->content($record->masterTujuanSasaran?->tujuan ?? $record->masterSasaran?->sasaran ?? '-'),

                                            Forms\Components\Placeholder::make('target_display')
                                                ->label('Target Tahun')
                                                ->content(number_format($record->target_tahun ?? 0, 2) . ' ' . $satuan),

                                            Forms\Components\Placeholder::make('indikator_display')
                                                ->label('Indikator')
                                                ->content($record->masterTujuanSasaran?->indikator_tujuan ?? $record->masterSasaran?->indikator_sasaran ?? '-'),
                                        ]),
                                ])
                                ->collapsible()
                                ->collapsed(false),

                            Section::make('Realisasi Per Triwulan')
                                ->schema([
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            // TW1
                                            Forms\Components\Group::make([
                                                Forms\Components\TextInput::make('realisasi_tw1')
                                                    ->label('Realisasi TW1')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->minValue(0)
                                                    ->suffix($satuan)
                                                    ->placeholder('0.00')
                                                    ->default($record->realisasi_tw1), // Set default value

                                                Forms\Components\Select::make('status_tw1')
                                                    ->label('Status TW1')
                                                    ->options([
                                                        'pending' => 'Pending',
                                                        'verified' => 'Verified',
                                                        'rejected' => 'Rejected',
                                                    ])
                                                    ->default($record->status_tw1 ?? 'pending'), // Set default value
                                            ]),

                                            // TW2
                                            Forms\Components\Group::make([
                                                Forms\Components\TextInput::make('realisasi_tw2')
                                                    ->label('Realisasi TW2')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->minValue(0)
                                                    ->suffix($satuan)
                                                    ->placeholder('0.00')
                                                    ->default($record->realisasi_tw2), // Set default value

                                                Forms\Components\Select::make('status_tw2')
                                                    ->label('Status TW2')
                                                    ->options([
                                                        'pending' => 'Pending',
                                                        'verified' => 'Verified',
                                                        'rejected' => 'Rejected',
                                                    ])
                                                    ->default($record->status_tw2 ?? 'pending'), // Set default value
                                            ]),

                                            // TW3
                                            Forms\Components\Group::make([
                                                Forms\Components\TextInput::make('realisasi_tw3')
                                                    ->label('Realisasi TW3')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->minValue(0)
                                                    ->suffix($satuan)
                                                    ->placeholder('0.00')
                                                    ->default($record->realisasi_tw3), // Set default value

                                                Forms\Components\Select::make('status_tw3')
                                                    ->label('Status TW3')
                                                    ->options([
                                                        'pending' => 'Pending',
                                                        'verified' => 'Verified',
                                                        'rejected' => 'Rejected',
                                                    ])
                                                    ->default($record->status_tw3 ?? 'pending'), // Set default value
                                            ]),

                                            // TW4
                                            Forms\Components\Group::make([
                                                Forms\Components\TextInput::make('realisasi_tw4')
                                                    ->label('Realisasi TW4')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->minValue(0)
                                                    ->suffix($satuan)
                                                    ->placeholder('0.00')
                                                    ->default($record->realisasi_tw4), // Set default value

                                                Forms\Components\Select::make('status_tw4')
                                                    ->label('Status TW4')
                                                    ->options([
                                                        'pending' => 'Pending',
                                                        'verified' => 'Verified',
                                                        'rejected' => 'Rejected',
                                                    ])
                                                    ->default($record->status_tw4 ?? 'pending'), // Set default value
                                            ]),
                                        ]),
                                ])
                                ->description('Edit realisasi untuk setiap triwulan'),

                            Section::make('Ringkasan Perhitungan')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Placeholder::make('rata_rata_display')
                                                ->label('Rata-rata Realisasi')
                                                ->content(function ($get) use ($satuan, $record) {
                                                    $realisasi = [
                                                        $get('realisasi_tw1') ?? $record->realisasi_tw1 ?? 0,
                                                        $get('realisasi_tw2') ?? $record->realisasi_tw2 ?? 0,
                                                        $get('realisasi_tw3') ?? $record->realisasi_tw3 ?? 0,
                                                        $get('realisasi_tw4') ?? $record->realisasi_tw4 ?? 0,
                                                    ];
                                                    $totalRealisasi = array_sum($realisasi);
                                                    $rataRata = $totalRealisasi / 4;
                                                    return number_format($rataRata, 2) . ' ' . $satuan;
                                                })
                                                ->live(),

                                            Forms\Components\Placeholder::make('pencapaian_display')
                                                ->label('Persentase Pencapaian')
                                                ->content(function ($get) use ($record) {
                                                    $target = $record->target_tahun ?? 0;
                                                    if ($target <= 0) return '0.00%';

                                                    $realisasi = [
                                                        $get('realisasi_tw1') ?? $record->realisasi_tw1 ?? 0,
                                                        $get('realisasi_tw2') ?? $record->realisasi_tw2 ?? 0,
                                                        $get('realisasi_tw3') ?? $record->realisasi_tw3 ?? 0,
                                                        $get('realisasi_tw4') ?? $record->realisasi_tw4 ?? 0,
                                                    ];
                                                    $totalRealisasi = array_sum($realisasi);
                                                    $rataRata = $totalRealisasi / 4;
                                                    $persentase = ($rataRata / $target) * 100;
                                                    return number_format($persentase, 2) . '%';
                                                })
                                                ->live(),

                                            Forms\Components\Placeholder::make('status_display')
                                                ->label('Status Keseluruhan')
                                                ->content(function ($get) use ($record) {
                                                    $target = $record->target_tahun ?? 0;
                                                    if ($target <= 0) return 'Target tidak valid';

                                                    $realisasi = [
                                                        $get('realisasi_tw1') ?? $record->realisasi_tw1 ?? 0,
                                                        $get('realisasi_tw2') ?? $record->realisasi_tw2 ?? 0,
                                                        $get('realisasi_tw3') ?? $record->realisasi_tw3 ?? 0,
                                                        $get('realisasi_tw4') ?? $record->realisasi_tw4 ?? 0,
                                                    ];
                                                    $totalRealisasi = array_sum($realisasi);
                                                    $rataRata = $totalRealisasi / 4;
                                                    $persentase = ($rataRata / $target) * 100;

                                                    if ($persentase >= 100) return '✅ Tercapai';
                                                    if ($persentase >= 75) return '⚠️ Hampir Tercapai';
                                                    if ($persentase >= 50) return '📈 Dalam Proses';
                                                    return '❌ Perlu Peningkatan';
                                                })
                                                ->live(),
                                        ]),
                                ])
                                ->collapsible()
                                ->collapsed(false),
                        ];
                    })
                    ->action(function ($record, $data) {
                        $record->update([
                            'realisasi_tw1' => $data['realisasi_tw1'] ?? 0,
                            'realisasi_tw2' => $data['realisasi_tw2'] ?? 0,
                            'realisasi_tw3' => $data['realisasi_tw3'] ?? 0,
                            'realisasi_tw4' => $data['realisasi_tw4'] ?? 0,
                            'status_tw1' => $data['status_tw1'] ?? 'pending',
                            'status_tw2' => $data['status_tw2'] ?? 'pending',
                            'status_tw3' => $data['status_tw3'] ?? 'pending',
                            'status_tw4' => $data['status_tw4'] ?? 'pending',
                        ]);

                        Notification::make()
                            ->title('Realisasi berhasil diperbarui')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
            ->defaultGroup('tipe')
            ->groups([
                Tables\Grouping\Group::make('tipe')
                    ->label('Tipe')
                    ->collapsible(),
                Tables\Grouping\Group::make('tahun')
                    ->label('Tahun')
                    ->collapsible(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['masterTujuanSasaran', 'masterSasaran'])
            ->selectRaw('*, CASE
            WHEN master_tujuan_sasarans_id IS NOT NULL THEN "tujuan"
            WHEN master_sasaran_id IS NOT NULL THEN "sasaran"
            ELSE "unknown"
        END as tipe') // Tambahkan eager loading
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->byYear(YearContext::getActiveYear());
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
            'index' => Pages\ListRealisasiTujuanSasarans::route('/'),
            'create' => Pages\CreateRealisasiTujuanSasaran::route('/create'),
            'view' => Pages\ViewRealisasiTujuanSasaran::route('/{record}'),
            'edit' => Pages\EditRealisasiTujuanSasaran::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::byYear(YearContext::getActiveYear())->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
