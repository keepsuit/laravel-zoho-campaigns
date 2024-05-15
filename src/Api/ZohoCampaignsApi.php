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

    /**
     * Retrieves the list of subscribers for a given list key with various options.
     *
     * @param string $listKey The list key.
     * @param array $options An array of options for the request. Possible keys include:
     *     - 'sort': The sort order of the results. Possible values are 'asc' for ascending order and 'desc' for descending order.
     *     - 'fromindex': The starting index for the results. This is a number.
     *     - 'range': The range of results to retrieve. This is a number.
     *     - 'status': The status of the subscribers to retrieve. Possible values are 'active', 'recent', 'mostrecent', 'unsub', and 'bounce'.
     * @return array The list of subscribers.
     */
    public function getListSubscribers(string $listKey, array $options = []): array
    {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
        ], $options);

        $response = Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->get(sprintf('/getlistsubscribers?%s', http_build_query($params)))
            ->json();
        
        dump($response);

        return $response['list_of_details'] ?? [];
    }


    /**
     * Retrieves the count of subscribers for a given list key and status.
     *
     * @param string $listKey The list key.
     * @param string $status The status of the subscribers to count. Possible values are 'active', 'unsub', 'bounce', and 'spam'.
     * @return int The count of subscribers.
     */
    public function getListSubscribersCount(string $listKey, string $status='active'): int
    {
        $params = [
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'status' => $status,
        ];

        $response = Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->get(sprintf('/listsubscriberscount?%s', http_build_query($params)))
            ->json();
        
            dump($response);
        return $response['no_of_contacts'] ?? 0;
    }


    protected function endpoint(): string
    {
        return sprintf('https://campaigns.%s/api/v1.1', $this->region->domain());
    }
}
