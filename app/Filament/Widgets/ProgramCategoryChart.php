<?php

namespace App\Filament\Widgets;

use App\Models\Program;
use Filament\Widgets\ChartWidget;

class ProgramCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Program Berdasarkan Kategori';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $categories = Program::all()->groupBy('kategori');

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Program',
                    'data' => $categories->map(function ($category) {
                        return $category->count();
                    })->values()->toArray(),
                    'backgroundColor' => [
                        '#6366f1', // indigo
                        '#3b82f6', // blue
                        '#ef4444', // red
                        '#10b981', // green
                        '#f59e0b', // yellow
                        '#8b5cf6', // purple
                        '#ec4899', // pink
                        '#6366f1', // indigo
                        '#14b8a6', // teal
                    ],
                ],
            ],
            'labels' => $categories->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
