<?php

namespace App\Filament\Pages;

use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use App\Models\Program;
use App\Models\CapaianKinerjaKegiatan as CapaianKinerjaKegiatanModel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;

class RealisasiKinerjaKegiatan extends Page implements HasForms, HasTable
{
  use InteractsWithForms;
  use InteractsWithTable;

  protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
  protected static ?string $navigationGroup = 'Capaian Kinerja';
  protected static string $view = 'filament.pages.realisasi-kinerja-kegiatan';
  protected static ?string $title = 'Realisasi Kinerja Kegiatan';
  protected static ?string $navigationLabel = 'Realisasi Kegiatan';
  protected static ?string $pluralLabel = 'Realisasi Kegiatan';
  protected static ?string $pluralModelLabel = 'Realisasi Kegiatan';
  protected static ?int $navigationSort = 1;

  // Untuk User dan Super Admin
  // public static function canAccess(): bool
  // {
  //   return \Illuminate\Support\Facades\Auth::user()->hasAnyRole(['user', 'super_admin']);
  // }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        CapaianKinerjaKegiatanModel::query()
          ->with(['program', 'kegiatan'])
          ->whereNotNull('target_nilai')
      )
      ->columns([
        TextColumn::make('kegiatan.kode_kegiatan')
          ->label('Kode Kegiatan')
          ->sortable()
          ->searchable(),

        TextColumn::make('kegiatan.nama_kegiatan')
          ->label('Nama Kegiatan')
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
          ->options(CapaianKinerjaKegiatanModel::distinct()->pluck('tahun', 'tahun')->sort()),

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
                TextInput::make('target_info')
                  ->label('Target')
                  ->disabled()
                  ->formatStateUsing(fn($record) => $record->target_nilai . ' ' . $record->target_dokumen),

                TextInput::make('tahun')
                  ->label('Tahun')
                  ->disabled(),
              ]),

            Grid::make(4)
              ->schema([
                TextInput::make('tw1')
                  ->label('TW 1')
                  ->numeric()
                  ->reactive()
                  ->helperText('Realisasi Triwulan 1 (Jan-Mar)')
                  ->afterStateUpdated(function (callable $set, callable $get) {
                    $this->calculateTotalInModal($set, $get);
                  }),

                TextInput::make('tw2')
                  ->label('TW 2')
                  ->numeric()
                  ->reactive()
                  ->helperText('Realisasi Triwulan 2 (Apr-Jun)')
                  ->afterStateUpdated(function (callable $set, callable $get) {
                    $this->calculateTotalInModal($set, $get);
                  }),

                TextInput::make('tw3')
                  ->label('TW 3')
                  ->numeric()
                  ->reactive()
                  ->helperText('Realisasi Triwulan 3 (Jul-Sep)')
                  ->afterStateUpdated(function (callable $set, callable $get) {
                    $this->calculateTotalInModal($set, $get);
                  }),

                TextInput::make('tw4')
                  ->label('TW 4')
                  ->numeric()
                  ->reactive()
                  ->helperText('Realisasi Triwulan 4 (Okt-Des)')
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
            $data['target_info'] = $record->target_nilai . ' ' . $record->target_dokumen;
            return $data;
          })
          ->action(function (array $data, $record) {
            unset($data['program_info'], $data['kegiatan_info'], $data['target_info']);

            $record->update($data);

            Notification::make()
              ->title('Realisasi kinerja kegiatan berhasil diperbarui')
              ->success()
              ->send();
          }),
      ])
      ->defaultSort('persentase', 'asc') // Prioritas yang persentasenya rendah
      ->poll(); // Auto refresh setiap beberapa detik
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

  // Method untuk mendapatkan statistik dashboard
  public function getStats(): array
  {
    $query = CapaianKinerjaKegiatanModel::where('status_perencanaan', 'approved');

    return [
      'total' => $query->count(),
      'completed' => $query->where('persentase', '>=', 100)->count(),
      'in_progress' => $query->where('total', '>', 0)->where('persentase', '<', 100)->count(),
      'not_started' => $query->where('total', 0)->count(),
      'avg_percentage' => round($query->avg('persentase'), 2),
    ];
  }
}
