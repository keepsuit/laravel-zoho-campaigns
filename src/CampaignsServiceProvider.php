<?php

namespace Keepsuit\Campaigns;

use Keepsuit\Campaigns\Api\ZohoApi;
use Keepsuit\Campaigns\Commands\SetupCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CampaignsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('campaigns')
            ->hasConfigFile()
            ->hasMigrations([
                'create_zoho_campaigns_tokens_table',
            ])
            ->runsMigrations()
            ->hasCommands([
                SetupCommand::class,
            ]);
    }

    public function registeringPackage()
    {
        $this->app->bind(ZohoApi::class, function () {
            return new ZohoApi(
                config('campaigns.client_id'),
                config('campaigns.client_secret'),
                config('campaigns.region'),
            );
        });
    }
}
