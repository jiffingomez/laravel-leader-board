<?php

namespace App\Console\Commands;

use App\Http\Controllers\LeaderBoardController;
use Illuminate\Console\Command;

class ResetLeaderBoardPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-leader-board-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to reset the leader board points';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new LeaderBoardController();
        $controller->reset_points();
        $this->info('Leader Board Points reset successfully');
    }
}
