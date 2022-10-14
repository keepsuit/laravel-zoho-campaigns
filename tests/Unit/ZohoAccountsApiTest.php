<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Keepsuit\Campaigns\Api\ZohoAccountsApi;
use Keepsuit\Campaigns\Api\ZohoRegion;

test('generate access token request', function () {
    Http::fake([
        'accounts.zoho.eu/oauth/v2/token?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'client_id' => 'client-id',
                'client_secret' => 'client-secret',
                'grant_type' => 'authorization_code',
                'code' => 'authorization-code-1',
            ]);

            return Http::response([
                'access_token' => 'access-token-1',
                'refresh_token' => 'refresh-token-1',
                'api_domain' => 'zohoapis.eu',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]);
        },
    ]);

    $api = new ZohoAccountsApi('client-id', 'client-secret', ZohoRegion::Europe);

    $response = $api->generateAccessToken('authorization-code-1');

    expect($response)
        ->toBeArray()
        ->toMatchArray([
            'access_token' => 'access-token-1',
            'refresh_token' => 'refresh-token-1',
            'api_domain' => 'zohoapis.eu',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);
});

test('refresh access token request', function () {
    Http::fake([
        'accounts.zoho.eu/oauth/v2/token?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'client_id' => 'client-id',
                'client_secret' => 'client-secret',
                'grant_type' => 'refresh_token',
                'refresh_token' => 'refresh-token-1',
            ]);

            return Http::response([
                'access_token' => 'access-token-2',
                'api_domain' => 'zohoapis.eu',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]);
        },
    ]);

    $api = new ZohoAccountsApi('client-id', 'client-secret', ZohoRegion::Europe);

    $response = $api->refreshAccessToken('refresh-token-1');

    expect($response)
        ->toBeArray()
        ->toMatchArray([
            'access_token' => 'access-token-2',
            'api_domain' => 'zohoapis.eu',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);
});
