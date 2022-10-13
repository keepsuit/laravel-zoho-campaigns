<?php

namespace Keepsuit\Campaigns;

use Illuminate\Foundation\Application;
use Keepsuit\Campaigns\Api\ZohoAccessToken;
use Keepsuit\Campaigns\Api\ZohoAccountsApi;
use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Api\ZohoRegion;
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
        $this->app->singleton(ZohoAccessToken::class);

        $this->app->bind(ZohoAccountsApi::class, function (Application $app) {
            return new ZohoAccountsApi(
                config('campaigns.client_id'),
                config('campaigns.client_secret'),
                ZohoRegion::tryFrom(config('campaigns.region')) ?? ZohoRegion::UnitedStates,
            );
        });

        $this->app->bind(ZohoCampaignsApi::class, function (Application $app) {
            return new ZohoCampaignsApi(
                ZohoRegion::tryFrom(config('campaigns.region')) ?? ZohoRegion::UnitedStates,
                $app->make(ZohoAccessToken::class)
            );
        });
    }
}
