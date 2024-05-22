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
    public function listSubscribe(string $listKey, string $email, array $contactInfo = [], array $additionalParams = []): array
    {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'contactinfo' => json_encode(array_merge([
                'Contact Email' => $email,
            ], $contactInfo)),
        ], $additionalParams);

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
    public function listUnsubscribe(string $listKey, string $email, array $additionalParams = []): array
    {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'contactinfo' => json_encode([
                'Contact Email' => $email,
            ]),
        ], $additionalParams);

        return Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->post(sprintf('/json/listunsubscribe?%s', http_build_query($params)))
            ->json();
    }

    /**
     * Retrieves the list of subscribers for a given list key with various options.
     *
     * @param  string  $listKey  The list key.
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'recent', 'mostrecent', 'unsub', and 'bounce'. Default is 'active'
     * @param  string  $sort  The sort order of the results. Possible values are 'asc' and 'desc'. Default is 'asc'.
     * @param  int  $fromIndex  The starting index for the results. Default is 1.
     * @param  int  $range  The range of results to retrieve. Default is 25.
     * @return array<array-key, array{
     *     zuid: string,
     *     phone: string,
     *     contact_email: string,
     *     firstname: string,
     *     lastname: string,
     *     companyname: string,
     * }> The list of subscribers.
     */
    public function listSubscribers(
        string $listKey,
        string $status = 'active',
        string $sort = 'asc',
        int $fromIndex = 1,
        int $range = 20,
        array $additionalParams = []
    ): array {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'fromindex' => $fromIndex,
            'range' => $range,
            'sort' => $sort,
            'status' => $status,
        ], $additionalParams);

        $response = Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->get(sprintf('/getlistsubscribers?%s', http_build_query($params)))
            ->json();

        return $response['list_of_details'] ?? [];
    }

    /**
     * Retrieves the count of subscribers for a given list key and status.
     *
     * @param  string  $listKey  The list key.
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'unsub', 'bounce' and 'spam'. Default is 'active'
     * @return int The count of subscribers.
     */
    public function listSubscribersCount(
        string $listKey,
        string $status = 'active',
        array $additionalParams = []
    ): int {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'status' => $status,
        ], $additionalParams);

        $response = Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->get(sprintf('/listsubscriberscount?%s', http_build_query($params)))
            ->json();

        return $response['no_of_contacts'] ?? 0;
    }

    protected function endpoint(): string
    {
        return sprintf('https://campaigns.%s/api/v1.1', $this->region->domain());
    }
}
