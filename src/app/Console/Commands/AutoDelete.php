<?php

namespace App\Console\Commands;

use App\Email;
use App\Statistic;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoDelete extends Command
{
    protected $signature = 'mailcare:delete';

    protected $description = 'Automatically soft delete emails according the previous usage';

    public function handle()
    {
        $this->line('--------------------------------------------------');
        $this->line('AutoDelete command executed at '.Carbon::now());
        $size = $this->getCalculatedSize();

        Email::where('favorite', false)->oldest()->chunkById(100, function ($emails) use ($size) {
            foreach ($emails as $email) {
                if ($size <= 0) {
                    return false;
                }

                $size = $size - $email->size_in_bytes;
                $email->delete();
                $this->line("$size deleted");
            }
        });

        return 0;
    }

    public function getCalculatedSize()
    {
        $storageUsedPeriod1 = Statistic::storageUsedBetween(
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonth()
        );

        $storageUsedPeriod2 = Statistic::storageUsedBetween(
            Carbon::now()->subMonth(),
            Carbon::now()
        );

        if (empty($storageUsedPeriod1) || empty($storageUsedPeriod2)) {
            $this->comment('Not enough periods to calculate');

            return 0;
        }
        $this->line("Storage used for period 1 ($storageUsedPeriod1) and for period 2 ($storageUsedPeriod2)");

        $percentage = (($storageUsedPeriod2 - $storageUsedPeriod1) / $storageUsedPeriod1);
        $calculatedStorage = $storageUsedPeriod2 + ($percentage * $storageUsedPeriod2);
        $this->line("Calculated size for the next period: $calculatedStorage");

        $storageToDelete = $calculatedStorage - Email::onlyTrashed()->sum('size_in_bytes');
        $this->line("Calculated size to delete without emails trashed: $storageToDelete");

        $delete = $this->getDiskFreeSpace() - $storageToDelete;

        if ($delete > 0) {
            $this->comment('Final calculated size to delete: 0');

            return 0;
        }

        $this->info("Final calculated size to delete: $delete");

        return (int) $delete * -1;
    }

    public function getDiskFreeSpace()
    {
        $diskFreeSpace = disk_free_space(storage_path());
        $this->line("Disk free space: $diskFreeSpace");

        return $diskFreeSpace;
    }
}
