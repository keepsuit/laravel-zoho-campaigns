<?php

use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Exceptions\ZohoCampaignsApiException;
use Keepsuit\Campaigns\Facades\Campaigns;

it('can subscribe user to a list', function (?string $list, string $expectedListKey) {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribe')
        ->with($expectedListKey, 'test@example.com', ['First Name' => 'abc', 'Last Name' => 'def'])
        ->andReturn('User subscribed successfully');

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    expect(fn () => Campaigns::subscribe('test@example.com', [
        'First Name' => 'abc',
        'Last Name' => 'def',
    ], list: $list))->not->toThrow(ZohoCampaignsApiException::class);
})->with([
    'default' => [null, 'subscribers-list-key'],
    'list name' => ['subscribers', 'subscribers-list-key'],
    'list key' => ['custom-list-key', 'custom-list-key'],
]);

it('can resubscribe user to a list', function (?string $list, string $expectedListKey) {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribe')
        ->with($expectedListKey, 'test@example.com', ['First Name' => 'abc', 'Last Name' => 'def'], ['donotmail_resub' => 'true'])
        ->andReturn('User resubscribed successfully');

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    expect(fn () => Campaigns::resubscribe('test@example.com', [
        'First Name' => 'abc',
        'Last Name' => 'def',
    ], list: $list))->not->toThrow(ZohoCampaignsApiException::class);
})->with([
    'default' => [null, 'subscribers-list-key'],
    'list name' => ['subscribers', 'subscribers-list-key'],
    'list key' => ['custom-list-key', 'custom-list-key'],
]);

it('can unsubscribe user from a list', function (?string $list, string $expectedListKey) {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listUnsubscribe')
        ->with($expectedListKey, 'test@example.com')
        ->andReturn('User unsubscribed successfully');

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    expect(fn () => Campaigns::unsubscribe('test@example.com', list: $list))->not->toThrow(ZohoCampaignsApiException::class);
})->with([
    'default' => [null, 'subscribers-list-key'],
    'list name' => ['subscribers', 'subscribers-list-key'],
    'list key' => ['custom-list-key', 'custom-list-key'],
]);

it('can get list subscribers', function (?string $list, string $expectedListKey) {
    $campaignsApi = mock(ZohoCampaignsApi::class);

    $campaignsApi->shouldReceive('listSubscribers')
        ->with($expectedListKey, 'active', 'asc', 1, 20)
        ->andReturn(array_map(fn (int $i) => ['email' => "test{$i}@example.com"], range(1, 20)));
    $campaignsApi->shouldReceive('listSubscribers')
        ->with($expectedListKey, 'active', 'asc', 21, 20)
        ->andReturn(array_map(fn (int $i) => ['email' => "test{$i}@example.com"], range(21, 23)));

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    expect(fn () => Campaigns::subscribers(chunkSize: 20, list: $list))->not->toThrow(ZohoCampaignsApiException::class);
})->with([
    'default' => [null, 'subscribers-list-key'],
    'list name' => ['subscribers', 'subscribers-list-key'],
    'list key' => ['custom-list-key', 'custom-list-key'],
]);

it('handle list subscribers end', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribersCount')
        ->with('subscribers-list-key', 'active')
        ->andReturn(23); // Adjust this value as per your test requirement

    $campaignsApi->shouldReceive('listSubscribers')
        ->with('subscribers-list-key', 'active', 'asc', 1, 20)
        ->andReturn(array_map(fn (int $i) => ['email' => "test{$i}@example.com"], range(1, 20)));
    $campaignsApi->shouldReceive('listSubscribers')
        ->with('subscribers-list-key', 'active', 'asc', 21, 20)
        ->andReturn([]);

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::subscribers(chunkSize: 20);

    expect($response)->count()->toBe(20);
});

it('can get list subscribers count', function (?string $list, string $expectedListKey) {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('listSubscribersCount')
        ->with($expectedListKey, 'active')
        ->andReturn(3);

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    $response = Campaigns::subscribersCount(list: $list);

    expect($response)->toBe(3);
})->with([
    'default' => [null, 'subscribers-list-key'],
    'list name' => ['subscribers', 'subscribers-list-key'],
    'list key' => ['custom-list-key', 'custom-list-key'],
]);

it('can attach a tag to a subscriber', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('tagAssociate')
        ->with('TEST', 'test@example.com')
        ->andReturn('Tag attached successfully');

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    expect(fn () => Campaigns::attachTag('test@example.com', 'TEST'))->not->toThrow(ZohoCampaignsApiException::class);
});

it('can detach a tag from a subscriber', function () {
    $campaignsApi = mock(ZohoCampaignsApi::class);
    $campaignsApi->shouldReceive('tagDeassociate')
        ->with('TEST', 'test@example.com')
        ->andReturn('Tag detached successfully');

    app()->bind(ZohoCampaignsApi::class, fn () => $campaignsApi);

    expect(fn () => Campaigns::detachTag('test@example.com', 'TEST'))->not->toThrow(ZohoCampaignsApiException::class);
});
