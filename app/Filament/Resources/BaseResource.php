<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use App\Services\YearContext;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseResource extends Resource
{
  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->where('tahun', YearContext::getActiveYear());
  }

  public static function getNavigationBadge(): ?string
  {
    return 'Tahun ' . YearContext::getActiveYear();
  }

  public static function shouldRegisterNavigation(): bool
  {
    return true;
  }
}
