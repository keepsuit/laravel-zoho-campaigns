<?php

namespace Keepsuit\Campaigns\Api;

use Illuminate\Support\Arr;
use Keepsuit\Campaigns\Models\Token;

class ZohoAccessToken
{
    protected ?Token $accessToken = null;

    protected ?Token $refreshToken = null;

    public function __construct(
        protected ZohoAccountsApi $accountsApi
    ) {
    }

    public function get(): string
    {
        if ($this->accessToken === null) {
            $this->accessToken = Token::findActiveAccessToken() ?? $this->refreshAccessToken();
        }

        if ($this->accessToken === null) {
            return '';
        }

        if (! $this->accessToken->isValid(now()->addMinute())) {
            $this->accessToken = $this->refreshAccessToken();
        }

        return $this->accessToken?->token ?? '';
    }

    protected function refreshAccessToken(): ?Token
    {
        if ($this->refreshToken === null) {
            $this->refreshToken = Token::findRefreshToken();
        }

        if ($this->refreshToken === null) {
            return null;
        }

        $response = $this->accountsApi->refreshAccessToken($this->refreshToken->token);

        if (Arr::get($response, 'access_token') === null) {
            return null;
        }

        return Token::saveAccessToken($response['access_token'], $response['expires_in']);
    }
}
