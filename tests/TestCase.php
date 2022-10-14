<?php

namespace Keepsuit\Campaigns\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Keepsuit\Campaigns\CampaignsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Keepsuit\\Campaigns\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            CampaignsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:GCQcZ6SU+ngsW8LV1yFPvVzQ4bvCoC2RGQXBcmAvVP8=');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-zoho-campaigns_table.php.stub';
        $migration->up();
        */
    }
}
