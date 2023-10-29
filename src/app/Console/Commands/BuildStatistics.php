<?php

namespace App\Console\Commands;

use App\Email;
use App\Inbox;
use App\Statistic;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BuildStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailcare:build-statistics {date? : The date of the statistics to build (AAAA-MM-JJ)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('date')) {
            $date = (new Carbon($this->argument('date')))->toDateString();
        } else {
            $date = Carbon::now()->subDay()->toDateString();
        }

        $emailsReceived = Email::whereDate('created_at', $date)->count();
        $inboxesCreated = Inbox::whereDate('created_at', $date)->count();
        $storageUsed = Email::whereDate('created_at', $date)->sum('size_in_bytes');
        $cumulativeStorageUsed = disk_total_space(storage_path()) - disk_free_space(storage_path());
        $emailsDeleted = Email::onlyTrashed()->whereDate('deleted_at', $date)->count();

        $statistic = Statistic::updateOrCreate(
            ['created_at' => $date],
            [
                'emails_received' => $emailsReceived,
                'inboxes_created' => $inboxesCreated,
                'storage_used' => $storageUsed,
                'cumulative_storage_used' => $cumulativeStorageUsed,
                'emails_deleted' => $emailsDeleted,
            ]
        );

        $this->info("Statistics builded successfully for $date");

        return 0;
    }
}
