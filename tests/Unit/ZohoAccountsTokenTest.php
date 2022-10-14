<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Keepsuit\Campaigns\Api\ZohoAccessToken;
use Keepsuit\Campaigns\Api\ZohoAccountsApi;
use Keepsuit\Campaigns\Database\Factories\TokenFactory;

uses(RefreshDatabase::class);

it('get active access token', function () {
    $accountsApi = mock(ZohoAccountsApi::class)->expect();

    $zohoAccessToken = new ZohoAccessToken($accountsApi);

    $accessToken = TokenFactory::new()->create();
    $refreshToken = TokenFactory::new()->refresh()->create();

    expect($zohoAccessToken->get())
        ->toBe('access-token');
});

it('refresh access token if expired', function () {
    $accountsApi = mock(ZohoAccountsApi::class)->expect(
        refreshAccessToken: fn () => [
            'access_token' => 'access-token-2',
            'expires_in' => 3600,
        ],
    );

    $zohoAccessToken = new ZohoAccessToken($accountsApi);

    $accessToken = TokenFactory::new()->create(['expires_at' => now()->subHour()]);
    $refreshToken = TokenFactory::new()->refresh()->create();

    expect($zohoAccessToken->get())
        ->toBe('access-token-2');
});

it('refresh access token if it will expire within a minute', function () {
    $accountsApi = mock(ZohoAccountsApi::class)->expect(
        refreshAccessToken: fn () => [
            'access_token' => 'access-token-2',
            'expires_in' => 3600,
        ],
    );

    $zohoAccessToken = new ZohoAccessToken($accountsApi);

    $accessToken = TokenFactory::new()->create(['expires_at' => now()->addSeconds(30)]);
    $refreshToken = TokenFactory::new()->refresh()->create();

    expect($zohoAccessToken->get())
        ->toBe('access-token-2');
});

it('return empty token if no active access token', function () {
    $accountsApi = mock(ZohoAccountsApi::class)->expect();

    $zohoAccessToken = new ZohoAccessToken($accountsApi);

    expect($zohoAccessToken->get())
        ->toBe('');
});

it('return empty token if refresh fails', function () {
    $accountsApi = mock(ZohoAccountsApi::class)->expect(
        refreshAccessToken: fn () => [
            'error' => 'invalid_client',
        ],
    );

    $zohoAccessToken = new ZohoAccessToken($accountsApi);

    $accessToken = TokenFactory::new()->create(['expires_at' => now()->subHour()]);
    $refreshToken = TokenFactory::new()->refresh()->create();

    expect($zohoAccessToken->get())
        ->toBe('');
});
