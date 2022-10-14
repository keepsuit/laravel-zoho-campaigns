<?php

namespace Keepsuit\Campaigns\Api;

use Illuminate\Support\Facades\Http;

class ZohoCampaignsApi
{
    public function __construct(
        protected ZohoRegion $region,
        protected ZohoAccessToken $accessToken
    ) {
    }

    /**
     * @return array{
     *     message: string,
     *     status: string,
     *     code: int,
     * }
     */
    public function listSubscribe(string $listKey, string $email, array $contactInfo = []): array
    {
        $params = [
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'contactinfo' => json_encode(array_merge([
                'Contact Email' => $email,
            ], $contactInfo)),
        ];

        return Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->post(sprintf('/json/listsubscribe?%s', http_build_query($params)))
            ->json();
    }

    /**
     * @return array{
     *     message: string,
     *     status: string,
     *     code: int,
     * }
     */
    public function listUnsubscribe(string $listKey, string $email): array
    {
        $params = [
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'contactinfo' => json_encode([
                'Contact Email' => $email,
            ]),
        ];

        return Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->post(sprintf('/json/listunsubscribe?%s', http_build_query($params)))
            ->json();
    }

    protected function endpoint(): string
    {
        return sprintf('https://campaigns.%s/api/v1.1', $this->region->domain());
    }
}
