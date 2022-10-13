<?php

namespace Keepsuit\Campaigns\Api;

use Illuminate\Support\Facades\Http;

class ZohoApi
{
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected ?string $region = null
    ) {
    }

    /**
     * @return array{
     *     access_token: string,
     *     refresh_token: string,
     *     api_domain: string,
     *     token_type: string,
     *     expires_in: int,
     * }
     */
    public function generateAccessToken(string $authorizationCode): array
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
        ]);

        $response = Http::post(sprintf('%s/token?%s', $this->getAccountsEndpoint(), $params));

        return $response->json();
    }

    /**
     * @return array{
     *     access_token: string,
     *     api_domain: string,
     *     token_type: string,
     *     expires_in: int,
     * }
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        $response = Http::post(sprintf('%s/token?%s', $this->getAccountsEndpoint(), $params));

        return $response->json();
    }

    protected function domain(): string
    {
        return match ($this->region) {
            ZohoRegion::Europe->value => 'zoho.eu',
            ZohoRegion::Australia->value => 'zoho.com.au',
            ZohoRegion::India->value => 'zoho.in',
            ZohoRegion::Japan->value => 'zoho.jp',
            ZohoRegion::China->value => 'zoho.com.cn',
            default => 'zoho.com',
        };
    }

    protected function getAccountsEndpoint(): string
    {
        return sprintf('https://accounts.%s/oauth/v2', $this->domain());
    }
}
