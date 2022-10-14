<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Keepsuit\Campaigns\Api\ZohoAccessToken;
use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Api\ZohoRegion;

beforeEach(function () {
    $accessToken = mock(ZohoAccessToken::class)->expect(
        get: fn () => 'access-token'
    );

    $this->campaignsApi = new ZohoCampaignsApi(ZohoRegion::Europe, $accessToken);
});

test('list subscribe', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/json/listsubscribe?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'resfmt' => 'JSON',
                'contactinfo' => json_encode([
                    'Contact Email' => 'john@example.com',
                    'First Name' => 'John',
                    'Last Name' => 'Doe',
                ]),
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'message' => 'A confirmation email has been sent to the user.',
                'status' => 'success',
                'code' => 0,
            ]);
        },
    ]);

    $response = $this->campaignsApi->listSubscribe('list-12345', 'john@example.com', [
        'First Name' => 'John',
        'Last Name' => 'Doe',
    ]);

    expect($response)
        ->toBeArray()
        ->toMatchArray([
            'message' => 'A confirmation email has been sent to the user.',
            'status' => 'success',
            'code' => 0,
        ]);
});

test('list unsubscribe', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/json/listunsubscribe?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'resfmt' => 'JSON',
                'contactinfo' => json_encode([
                    'Contact Email' => 'john@example.com',
                ]),
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'message' => 'User successfully unsubscribed.',
                'status' => 'success',
                'code' => 0,
            ]);
        },
    ]);

    $response = $this->campaignsApi->listUnsubscribe('list-12345', 'john@example.com');

    expect($response)
        ->toBeArray()
        ->toMatchArray([
            'message' => 'User successfully unsubscribed.',
            'status' => 'success',
            'code' => 0,
        ]);
});
