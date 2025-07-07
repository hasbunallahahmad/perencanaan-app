<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GreetingWidget extends Widget
{
    protected static string $view = 'filament.widgets.greeting-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -20;
    public function getViewData(): array
    {
        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');
        $hour = $now->hour;

        $greeting = match (true) {
            $hour >= 5 && $hour < 12 => 'Selamat Pagi',
            $hour >= 12 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam'
        };
        return [
            'greeting' => $greeting,
            'user' => $user,
            'userName' => $user ? $user->name : 'Guest',
            'date' => $now->format('d F Y'),
            'time' => $now->format('H:i'),
        ];
    }
}
