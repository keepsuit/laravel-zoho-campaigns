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
