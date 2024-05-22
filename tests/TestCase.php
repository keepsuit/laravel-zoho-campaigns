<?php

namespace Keepsuit\Campaigns\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Keepsuit\Campaigns\CampaignsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Keepsuit\\Campaigns\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            CampaignsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:GCQcZ6SU+ngsW8LV1yFPvVzQ4bvCoC2RGQXBcmAvVP8=');
        config()->set('campaigns.lists', [
            'subscribers' => [
                'listKey' => 'subscribers-list-key',
            ],
        ]);
    }
}
