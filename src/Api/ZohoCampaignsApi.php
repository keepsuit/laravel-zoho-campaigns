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

    public function campaignsListSubscribe(string $email, array $contactInfo = [], ?string $listKey = null): array
    {
        $params = http_build_query([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'contactinfo' => json_encode(array_merge([
                'Contact Email' => $email,
            ], $contactInfo)),
        ]);

        return Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->post(sprintf('/json/listsubscribe?%s', $params))
            ->json();
    }

    protected function endpoint(): string
    {
        return sprintf('https://campaigns.%s/api/v1', $this->region->domain());
    }
}
