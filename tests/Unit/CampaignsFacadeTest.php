<?php

use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Facades\Campaigns;

it('can subscribe user to a list', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class)->expect(
        listSubscribe: function ($listKey, $email, $contactInfo) {
            expect($listKey)->toBe('subscribers-list-key');
            expect($email)->toBe('test@example.com');
            expect($contactInfo)->toBe(['First Name' => 'abc', 'Last Name' => 'def']);

            return ['status' => 'success', 'message' => 'User subscribed successfully'];
        }
    );

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
    $campaignsApi = mock(ZohoCampaignsApi::class)->expect(
        listUnsubscribe: function ($listKey, $email) {
            expect($listKey)->toBe('subscribers-list-key');
            expect($email)->toBe('test@example.com');

            return ['status' => 'success', 'message' => 'User unsubscribed successfully'];
        }
    );

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::unsubscribe('test@example.com');

    expect($response)->toMatchArray([
        'success' => true,
        'message' => 'User unsubscribed successfully',
    ]);
});
