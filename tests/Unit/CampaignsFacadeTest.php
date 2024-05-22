<?php

use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Facades\Campaigns;

it('can subscribe user to a list', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribe')
        ->with('subscribers-list-key', 'test@example.com', ['First Name' => 'abc', 'Last Name' => 'def'])
        ->andReturn(['status' => 'success', 'message' => 'User subscribed successfully']);

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::subscribe('test@example.com', [
        'First Name' => 'abc',
        'Last Name' => 'def',
    ]);

    expect($response)->toMatchArray([
        'success' => true,
        'message' => 'User subscribed successfully',
    ]);
});

it('can resubscribe user to a list', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribe')
        ->with('subscribers-list-key', 'test@example.com', ['First Name' => 'abc', 'Last Name' => 'def'], ['donotmail_resub' => 'true'])
        ->andReturn(['status' => 'success', 'message' => 'User resubscribed successfully']);

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::resubscribe('test@example.com', [
        'First Name' => 'abc',
        'Last Name' => 'def',
    ]);

    expect($response)->toMatchArray([
        'success' => true,
        'message' => 'User resubscribed successfully',
    ]);
});

it('can unsubscribe user from a list', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listUnsubscribe')
        ->with('subscribers-list-key', 'test@example.com')
        ->andReturn(['status' => 'success', 'message' => 'User unsubscribed successfully']);

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::unsubscribe('test@example.com');

    expect($response)->toMatchArray([
        'success' => true,
        'message' => 'User unsubscribed successfully',
    ]);
});

it('can get list subscribers', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribers')
        ->with('subscribers-list-key', 'active', 'asc', 1, 20)
        ->andReturn(array_map(fn (int $i) => ['email' => "test{$i}@example.com"], range(1, 20)));
    $campaignsApi->shouldReceive('listSubscribers')
        ->with('subscribers-list-key', 'active', 'asc', 21, 20)
        ->andReturn(array_map(fn (int $i) => ['email' => "test{$i}@example.com"], range(21, 23)));

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::subscribers();

    expect($response)->count()->toBe(23);
});

it('can get list subscribers count', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribersCount')
        ->with('subscribers-list-key', 'active')
        ->andReturn(3);

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::subscribersCount();

    expect($response)->toBe(3);
});
