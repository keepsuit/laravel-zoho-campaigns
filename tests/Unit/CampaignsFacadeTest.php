<?php

use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Facades\Campaigns;

it('can subscribe user to a list', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribe')
        ->with('subscribers-list-key', 'test@example.com', ['First Name' => 'abc', 'Last Name' => 'def'])
        ->andReturn('User subscribed successfully');

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::subscribe('test@example.com', [
        'First Name' => 'abc',
        'Last Name' => 'def',
    ]);

    expect($response)->toBe('User subscribed successfully');
});

it('can resubscribe user to a list', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribe')
        ->with('subscribers-list-key', 'test@example.com', ['First Name' => 'abc', 'Last Name' => 'def'], ['donotmail_resub' => 'true'])
        ->andReturn('User resubscribed successfully');

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::resubscribe('test@example.com', [
        'First Name' => 'abc',
        'Last Name' => 'def',
    ]);

    expect($response)->toBe('User resubscribed successfully');
});

it('can unsubscribe user from a list', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listUnsubscribe')
        ->with('subscribers-list-key', 'test@example.com')
        ->andReturn('User unsubscribed successfully');

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::unsubscribe('test@example.com');

    expect($response)->toBe('User unsubscribed successfully');
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

    $response = Campaigns::subscribers(chunkSize: 20);

    expect($response)->count()->toBe(23);
});

it('handle no contacts in the list error in list subscribers', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribersCount')
        ->with('subscribers-list-key', 'active')
        ->andReturn(23); // Adjust this value as per your test requirement

    $campaignsApi->shouldReceive('listSubscribers')
        ->with('subscribers-list-key', 'active', 'asc', 1, 20)
        ->andReturn(array_map(fn (int $i) => ['email' => "test{$i}@example.com"], range(1, 20)));
    $campaignsApi->shouldReceive('listSubscribers')
        ->with('subscribers-list-key', 'active', 'asc', 21, 20)
        ->andThrow(new \Keepsuit\Campaigns\Api\ZohoApiException('Yet,There are no contacts in this list.', 2502));

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::subscribers(chunkSize: 20);

    expect($response)->count()->toBe(20);
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
