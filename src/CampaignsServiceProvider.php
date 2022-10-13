<?php

namespace Keepsuit\Campaigns;

use Keepsuit\Campaigns\Commands\CampaignsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CampaignsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-zoho-campaigns')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-zoho-campaigns_table')
            ->hasCommand(CampaignsCommand::class);
    }
}
