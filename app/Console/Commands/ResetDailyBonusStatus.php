<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BotUser;

class ResetDailyBonusStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:daily-bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily bonus status for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        BotUser::where(['daily_bonus_status' => true])->update(['daily_bonus_status' => false]);

        $this->info('Daily bonus status reset successfully.');
    }
}
