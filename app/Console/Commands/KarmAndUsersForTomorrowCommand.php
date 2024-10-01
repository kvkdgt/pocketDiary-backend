<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\KarmController;
use Carbon\Carbon;

class KarmAndUsersForTomorrowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karm:get-users-for-tomorrow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1 day before karm reminder';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $controller = app(KarmController::class);
        $karmUsersData = $controller->getKarmAndUsersForTomorrow();
        \Log::info('Karm data for tomorrow: ', $karmUsersData->toArray());
        return Command::SUCCESS;
    }
}
