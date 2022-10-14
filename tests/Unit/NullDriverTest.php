<?php

use Illuminate\Support\Facades\Log;
use Keepsuit\Campaigns\Campaigns;
use Keepsuit\Campaigns\NullDriver;

it('can construct null driver', function () {
    config()->set('campaigns.driver', 'null');

    $driver = app(Campaigns::class);

    expect($driver)->toBeInstanceOf(Keepsuit\Campaigns\NullDriver::class);
});

it('can call any method on null driver', function () {
    $driver = new NullDriver();

    expect($driver->subscribe('test@example.com'))->toBeNull();
    expect($driver->unsubscribe('test@example.com'))->toBeNull();
    expect($driver->nonExistingMethod())->toBeNull();
});

it('can log calls on null driver', function () {
    $driver = new NullDriver(true);

    Log::swap($log = Mockery::mock());

    $log->shouldReceive('debug')->once();

    $driver->subscribe('test@example.com');

    $log->shouldHaveReceived('debug', [
        'Called Campaigns::subscribe',
        [
            'test@example.com',
        ],
    ]);
});
