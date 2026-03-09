<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MarkCompletedApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:mark-completed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark applications as selesai when period_end is before today (for active statuses).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Update only applications that are currently active (accepted / in process)
        $affected = Application::whereIn('status', ['diterima', 'diproses'])
            ->whereDate('period_end', '<', $today)
            ->update([
                'status' => 'selesai',
                'updated_at' => now(),
            ]);

        $this->info("Marked {$affected} applications as selesai (period_end < {$today}).");
        Log::info("applications:mark-completed - marked {$affected} applications as selesai (period_end < {$today}).");

        return 0;
    }
}
