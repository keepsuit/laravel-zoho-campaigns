<?php

namespace Keepsuit\Campaigns\Api;

use Illuminate\Support\Facades\Http;

class ZohoAccountsApi
{
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected ZohoRegion $region,
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

        return Http::post(sprintf('%s/token?%s', $this->endpoint(), $params))
            ->json();
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

        return Http::post(sprintf('%s/token?%s', $this->endpoint(), $params))
            ->json();
    }

    protected function endpoint(): string
    {
        return sprintf('https://accounts.%s/oauth/v2', $this->region->domain());
    }
}
