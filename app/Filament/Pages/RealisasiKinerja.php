<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\CapaianKinerja as CapaianKinerjaModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;

class RealisasiKinerja extends Page implements HasForms, HasTable
{
  use InteractsWithForms;
  use InteractsWithTable;

  protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
  protected static ?string $navigationGroup = 'Capaian Kinerja';
  protected static string $view = 'filament.pages.realisasi-kinerja';
  protected static ?string $title = 'Realisasi Kinerja';
  protected static ?string $navigationLabel = 'Realisasi Sub Kegiatan';
  protected static ?string $pluralLabel = 'Realisasi Sub Kegiatan';
  protected static ?string $pluralModelLabel = 'Realisasi Sub Kegiatan';
  protected static ?int $navigationSort = 2;

  public function table(Table $table): Table
  {
    return $table
      ->query(
        CapaianKinerjaModel::query()
          ->with(['program', 'kegiatan', 'subKegiatan'])
          ->whereNotNull('target_nilai')
      )
      ->columns([
        // TextColumn::make('program.kode_program')
        //   ->label('Kode Program')
        //   ->sortable()
        //   ->searchable(),

        // TextColumn::make('program.nama_program')
        //   ->label('Nama Program')
        //   ->wrap()
        //   ->sortable()
        //   ->searchable(),

        // TextColumn::make('kegiatan.kode_kegiatan')
        //   ->label('Kode Kegiatan')
        //   ->sortable()
        //   ->searchable(),

        // TextColumn::make('kegiatan.nama_kegiatan')
        //   ->label('Nama Kegiatan')
        //   ->wrap()
        //   ->sortable()
        //   ->searchable(),

        TextColumn::make('subKegiatan.kode_sub_kegiatan')
          ->label('Kode Sub Kegiatan')
          ->sortable()
          ->searchable(),

        TextColumn::make('subKegiatan.nama_sub_kegiatan')
          ->label('Nama Sub Kegiatan')
          ->wrap()
          ->sortable()
          ->searchable(),

        TextColumn::make('target_dokumen')
          ->label('Satuan')
          ->alignCenter(),

        TextColumn::make('target_nilai')
          ->label('Target')
          ->alignCenter()
          ->weight('bold')
          ->color('primary'),

        TextColumn::make('tw1')
          ->label('TW 1')
          ->alignCenter()
          ->color(fn($state) => $state > 0 ? 'success' : 'gray'),

        TextColumn::make('tw2')
          ->label('TW 2')
          ->alignCenter()
          ->color(fn($state) => $state > 0 ? 'success' : 'gray'),

        TextColumn::make('tw3')
          ->label('TW 3')
          ->alignCenter()
          ->color(fn($state) => $state > 0 ? 'success' : 'gray'),

        TextColumn::make('tw4')
          ->label('TW 4')
          ->alignCenter()
          ->color(fn($state) => $state > 0 ? 'success' : 'gray'),

        TextColumn::make('total')
          ->label('Total')
          ->alignCenter()
          ->weight('bold')
          ->color('info'),

        TextColumn::make('persentase')
          ->label('Persentase (%)')
          ->alignCenter()
          ->formatStateUsing(fn($state) => number_format($state, 2) . '%')
          ->weight('bold')
          ->color(fn($state) => match (true) {
            $state >= 100 => 'success',
            $state >= 75 => 'warning',
            $state >= 50 => 'info',
            default => 'danger'
          }),

        TextColumn::make('tahun')
          ->label('Tahun')
          ->alignCenter()
          ->sortable(),

        TextColumn::make('status_realisasi')
          ->label('Status Realisasi')
          ->alignCenter()
          ->badge()
          ->color(fn($state) => match ($state) {
            'completed' => 'success',
            'in_progress' => 'warning',
            'not_started' => 'gray',
            default => 'danger'
          })
          ->formatStateUsing(
            fn($state, $record) =>
            $record->total == 0 ? 'Belum Dimulai' : ($record->persentase >= 100 ? 'Selesai' : ($record->total > 0 ? 'Dalam Progress' : 'Belum Dimulai'))
          ),
      ])
      ->filters([
        SelectFilter::make('id_program')
          ->label('Program')
          ->options(Program::pluck('nama_program', 'id_program'))
          ->searchable(),

        SelectFilter::make('tahun')
          ->label('Tahun')
          ->options(CapaianKinerjaModel::distinct()->pluck('tahun', 'tahun')->sort()),

        SelectFilter::make('status')
          ->label('Status Pencapaian')
          ->options([
            'not_started' => 'Belum Dimulai',
            'low' => 'Rendah (< 50%)',
            'medium' => 'Sedang (50-74%)',
            'good' => 'Baik (75-99%)',
            'excellent' => 'Sangat Baik (≥ 100%)',
          ])
          ->query(function ($query, $data) {
            if (!$data['value']) return $query;

            return match ($data['value']) {
              'not_started' => $query->where('total', 0),
              'low' => $query->where('persentase', '<', 50)->where('total', '>', 0),
              'medium' => $query->whereBetween('persentase', [50, 74.99]),
              'good' => $query->whereBetween('persentase', [75, 99.99]),
              'excellent' => $query->where('persentase', '>=', 100),
              default => $query
            };
          }),
      ])
      ->actions([
        Action::make('input_realisasi')
          ->label('Input Realisasi')
          ->icon('heroicon-o-pencil-square')
          ->color('primary')
          ->form([
            Hidden::make('id'),

            Grid::make(2)
              ->schema([
                TextInput::make('program_info')
                  ->label('Program')
                  ->disabled()
                  ->formatStateUsing(fn($record) => $record->program->nama_program ?? '-'),

                TextInput::make('kegiatan_info')
                  ->label('Kegiatan')
                  ->disabled()
                  ->formatStateUsing(fn($record) => $record->kegiatan->nama_kegiatan ?? '-'),
              ]),

            Grid::make(2)
              ->schema([
                TextInput::make('sub_kegiatan_info')
                  ->label('Sub Kegiatan')
                  ->disabled()
                  ->formatStateUsing(fn($record) => $record->subKegiatan->nama_sub_kegiatan ?? '-'),

                TextInput::make('target_info')
                  ->label('Target')
                  ->disabled()
                  ->formatStateUsing(fn($record) => $record->target_nilai . ' ' . $record->target_dokumen),
              ]),

            Grid::make(4)
              ->schema([
                TextInput::make('tw1')
                  ->label('TW 1')
                  ->numeric()
                  ->reactive()
                  ->helperText(function ($record) {
                    if ($record->tw1 > 0) {
                      return 'TW 1 sudah terkunci karena sudah diinput';
                    }
                    return 'Realisasi Triwulan 1';
                  })
                  ->disabled(function ($record) {
                    // Disable jika sudah ada nilai dan persentase belum 100%
                    return $record->tw1 > 0;
                  })
                  ->afterStateUpdated(function (callable $set, callable $get) {
                    $this->calculateTotalInModal($set, $get);
                  }),

                TextInput::make('tw2')
                  ->label('TW 2')
                  ->numeric()
                  ->reactive()
                  ->helperText(function ($record) {
                    if ($record->tw2 > 0) {
                      return 'TW 2 sudah terkunci karena sudah diinput';
                    }
                    return 'Realisasi Triwulan 2';
                  })
                  ->disabled(function ($record) {
                    return $record->tw2 > 0;
                  })
                  ->afterStateUpdated(function (callable $set, callable $get) {
                    $this->calculateTotalInModal($set, $get);
                  }),

                TextInput::make('tw3')
                  ->label('TW 3')
                  ->numeric()
                  ->reactive()
                  ->helperText(function ($record) {
                    if ($record->tw3 > 0) {
                      return 'TW 3 sudah terkunci karena sudah diinput';
                    }
                    return 'Realisasi Triwulan 3';
                  })
                  ->disabled(function ($record) {
                    return $record->tw3 > 0;
                  })
                  ->afterStateUpdated(function (callable $set, callable $get) {
                    $this->calculateTotalInModal($set, $get);
                  }),

                TextInput::make('tw4')
                  ->label('TW 4')
                  ->numeric()
                  ->reactive()
                  ->helperText(function ($record) {
                    if ($record->tw4 > 0) {
                      return 'TW 4 sudah terkunci karena sudah diinput';
                    }
                    return 'Realisasi Triwulan 4';
                  })
                  ->disabled(function ($record) {
                    return $record->tw4 > 0;
                  })
                  ->afterStateUpdated(function (callable $set, callable $get) {
                    $this->calculateTotalInModal($set, $get);
                  }),
              ]),

            Grid::make(2)
              ->schema([
                TextInput::make('total')
                  ->label('Total Realisasi')
                  ->disabled()
                  ->dehydrated()
                  ->helperText('Total = TW 1 + TW 2 + TW 3 + TW 4'),

                TextInput::make('persentase')
                  ->label('Persentase Pencapaian (%)')
                  ->disabled()
                  ->dehydrated()
                  ->helperText('Persentase = (Total / Target) × 100%')
                  ->suffix('%'),
              ]),
          ])
          ->fillForm(function ($record) {
            $data = $record->toArray();
            $data['program_info'] = $record->program->nama_program ?? '';
            $data['kegiatan_info'] = $record->kegiatan->nama_kegiatan ?? '';
            $data['sub_kegiatan_info'] = $record->subKegiatan->nama_sub_kegiatan ?? '';
            $data['target_info'] = $record->target_nilai . ' ' . $record->target_dokumen;
            return $data;
          })
          ->action(function (array $data, $record) {
            unset($data['program_info'], $data['kegiatan_info'], $data['sub_kegiatan_info'], $data['target_info']);

            $originalData = $record->toArray();

            if ($originalData['tw1'] > 0) {
              unset($data['tw1']);
            }

            if ($originalData['tw2'] > 0) {
              unset($data['tw2']);
            }

            if ($originalData['tw3'] > 0) {
              unset($data['tw3']);
            }

            if ($originalData['tw4'] > 0) {
              unset($data['tw4']);
            }

            $record->update($data);

            Notification::make()
              ->title('Realisasi kinerja berhasil diperbarui')
              ->success()
              ->send();
          }),

        // Action::make('view_detail')
        //   ->label('Lihat Detail')
        //   ->icon('heroicon-o-eye')
        //   ->color('info')
        //   ->infolist([])
        //   ->modalWidth('5xl'),
      ])
      ->headerActions([
        Action::make('export')
          ->label('Export Data')
          ->icon('heroicon-o-arrow-down-tray')
          ->color('success'),
      ])
      ->defaultSort('created_at', 'desc');
  }

  private function calculateTotalInModal(callable $set, callable $get): void
  {
    $tw1 = (float) ($get('tw1') ?? 0);
    $tw2 = (float) ($get('tw2') ?? 0);
    $tw3 = (float) ($get('tw3') ?? 0);
    $tw4 = (float) ($get('tw4') ?? 0);

    $total = $tw1 + $tw2 + $tw3 + $tw4;
    $set('total', $total);

    $target = (float) ($get('target_nilai') ?? 0);
    if ($target > 0) {
      $persentase = round(($total / $target) * 100, 2);
      $set('persentase', $persentase);
    } else {
      $set('persentase', 0);
    }
  }
}
