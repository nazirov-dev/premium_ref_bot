<?php

namespace App\Filament\Widgets;

use App\Models\BotUser;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyStatChart extends ChartWidget
{
    protected static ?string $heading = 'Yillik o\'sish statistikasi';
    protected int | string | array $columnSpan  = 'full';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = $this->getUsersPerMonth();

        return [
            'datasets' => [
                [
                    'label' => "Foydalanuvchi qo'shilish diagrammasi",
                    'data' => $data['usersPerMonth'],
                ]
            ],
            'labels' => $data['months']
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    private function getUsersPerMonth(): array
    {
        $now = Carbon::now();
        $usersPermonth = [];
        $months = collect(range(1, 12))->map(function ($month) use ($now, &$usersPermonth) {
            $count = BotUser::whereMonth('created_at', Carbon::parse($now->month($month)->format('Y-m')))->count();
            $usersPermonth[] = $count;
            return $now->month($month)->format('M');
        })->toArray();
        return [
            'usersPerMonth' => $usersPermonth,
            'months' => $months
        ];
    }
}
