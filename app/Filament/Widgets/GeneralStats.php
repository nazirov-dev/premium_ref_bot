<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use App\Models\BotUser;
use App\Models\Channel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class GeneralStats extends BaseWidget
{
    protected static bool $isLazy = true;
    protected static ?string $pollingInterval = '30s';
    protected function getStats(): array
    {
        $stats = [
            Stat::make("Jami foydalanuvchilar soni", BotUser::count() . ' ta')
                ->color("success")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->description("Foydanaluvchilar haftalik o'sish darajasi")
                ->chart($this->getUsersPerDay()['usersPerDay']),
            Stat::make("Jami faol foydalanuvchilar soni", BotUser::where('status', true)->count() . ' ta'),
            Stat::make("Jami kanallar soni", Channel::count() . ' ta'),
        ];
        return $stats;
    }
    private function getUsersPerDay(): array
    {
        $now = Carbon::now();
        $usersPerDay = [];

        $days = collect(range(0, 6))->map(function ($day) use ($now, &$usersPerDay) {
            // Subtract days from the current day to get the date for each day of the week
            $date = $now->subDays($day);

            $count = BotUser::whereDate('created_at', $date)->count();
            $usersPerDay[] = $count;

            return $date->format('D M j'); // For format like "Wed Sep 28"
        })->toArray();  // reverse the collection to start from 7 days ago

        return [
            'usersPerDay' => array_reverse($usersPerDay),
            'days' => $days
        ];
    }
}
