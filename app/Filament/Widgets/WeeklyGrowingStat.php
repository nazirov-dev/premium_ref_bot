<?php

namespace App\Filament\Widgets;

use App\Models\BotUser;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class WeeklyGrowingStat extends ChartWidget
{
    protected static ?string $heading = 'Xaftalik statistika';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = $this->getUsersForLastSevenDays();

        return [
            'datasets' => [
                [
                    'label' => "Oxirgi 7 kunlik statistika",
                    'data' => $data['usersPerDay'],
                ]
            ],
            'labels' => $data['days']
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getUsersForLastSevenDays(): array
    {
        $today = Carbon::today();
        $usersPerDay = [];
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $count = BotUser::whereDate('created_at', '=', $date)->count();
            $usersPerDay[] = $count;
            $days[] = $date->format('M d');
        }

        return [
            'usersPerDay' => $usersPerDay,
            'days' => $days
        ];
    }
}
