<?php

namespace App\Console\Commands;

use App\Services\PrivateReportStorage;
use Illuminate\Console\Command;
use Throwable;

class PrunePrivateReports extends Command
{
    protected $signature = 'reports:prune';
    protected $description = 'Remove expired artifacts from the managed private report disk only';

    public function handle(PrivateReportStorage $reports): int
    {
        try {
            $deleted = $reports->prune();
            $this->info('Expired private report artifacts removed: ' . $deleted);
            return self::SUCCESS;
        } catch (Throwable $error) {
            $this->error('Private report cleanup failed. Check storage availability and permissions.');
            return self::FAILURE;
        }
    }
}
