<?php

namespace App\Jobs;

use App\Http\Controllers\LeaderBoardController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QRCodeGenerator implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $filename = $this->data['filename'];
        $address = $this->data['address'];
        $leaderboard = new LeaderBoardController();
        $leaderboard->qr_code($address, $filename);
    }
}
