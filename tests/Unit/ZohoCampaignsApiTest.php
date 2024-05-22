<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Keepsuit\Campaigns\Api\ZohoAccessToken;
use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Api\ZohoRegion;

beforeEach(function () {
    $accessToken = mock(ZohoAccessToken::class);
    $accessToken->shouldReceive('get')->andReturn('access-token');

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
        ->toBe('A confirmation email has been sent to the user.');
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
        ->toBe('User successfully unsubscribed.');
});

test('get list subscribers', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/getlistsubscribers?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'resfmt' => 'JSON',
                'listkey' => 'list-12345',
                'fromindex' => '1',
                'range' => '20',
                'sort' => 'asc',
                'status' => 'active',
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'status' => 'success',
                'code' => 0,
                'list_of_details' => [
                    [
                        'firstname' => 'First name',
                        'lastname' => 'Last name',
                        'companyname' => 'Company name',
                        'added_time' => '01/09/2022',
                        'phone' => '123456789',
                        'contact_email' => 'test@example.com',
                        'zuid' => '12345',
                    ],
                ],
            ]);
        },
    ]);

    $response = $this->campaignsApi->listSubscribers('list-12345');

    expect($response)
        ->toBeArray()
        ->toMatchArray([
            [
                'firstname' => 'First name',
                'lastname' => 'Last name',
                'companyname' => 'Company name',
                'added_time' => '01/09/2022',
                'phone' => '123456789',
                'contact_email' => 'test@example.com',
                'zuid' => '12345',
            ],
        ]);
});

test('get list subscribers count', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/listsubscriberscount?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'resfmt' => 'JSON',
                'listkey' => 'list-12345',
                'status' => 'active',
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'status' => 'success',
                'code' => 0,
                'no_of_contacts' => 2,
            ]);
        },
    ]);

    $response = $this->campaignsApi->listSubscribersCount('list-12345');

    expect($response)
        ->toBe(2);
});
