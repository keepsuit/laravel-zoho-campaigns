<?php

namespace Keepsuit\Campaigns\Commands;

use Illuminate\Console\Command;

class CampaignsCommand extends Command
{
    public $signature = 'laravel-zoho-campaigns';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
