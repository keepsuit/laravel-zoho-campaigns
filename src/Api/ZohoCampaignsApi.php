<?php

namespace Keepsuit\Campaigns\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ZohoCampaignsApi
{
    public function __construct(
        protected ZohoRegion $region,
        protected ZohoAccessToken $accessToken
    ) {
    }

    /**
     * Subscribes a contact to a list.
     *
     * @link https://www.zoho.com/campaigns/help/developers/contact-subscribe.html
     *
     * @param  string  $listKey  The list key.
     * @param  string  $email  The email address to subscribe.
     * @param  array  $contactInfo  Additional contact information to subscribe.
     * @param  array  $additionalParams  Additional parameters to pass to the API.
     * @return string Response message from the API.
     *
     * @throws ZohoApiException
     * @throws ConnectionException
     */
    public function listSubscribe(string $listKey, string $email, array $contactInfo = [], array $additionalParams = []): string
    {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'contactinfo' => json_encode(array_merge([
                'Contact Email' => $email,
            ], $contactInfo)),
        ], $additionalParams);

        $response = $this->newRequest()
            ->post(sprintf('/json/listsubscribe?%s', http_build_query($params)))
            ->json();

        if ($response['status'] === 'error') {
            throw ZohoApiException::fromResponse($response);
        }

        return $response['message'] ?? '';
    }

    /**
     * Unsubscribes a contact from a list.
     *
     * @link https://www.zoho.com/campaigns/help/developers/contact-unsubscribe.html
     *
     * @param  string  $listKey  The list key.
     * @param  string  $email  The email address to unsubscribe.
     * @param  array  $additionalParams  Additional parameters to pass to the API.
     * @return string Response message from the API.
     *
     * @throws ZohoApiException
     * @throws ConnectionException
     */
    public function listUnsubscribe(string $listKey, string $email, array $additionalParams = []): string
    {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'contactinfo' => json_encode([
                'Contact Email' => $email,
            ]),
        ], $additionalParams);

        $response = $this->newRequest()
            ->post(sprintf('/json/listunsubscribe?%s', http_build_query($params)))
            ->json();

        if ($response['status'] === 'error') {
            throw ZohoApiException::fromResponse($response);
        }

        return $response['message'] ?? '';
    }

    /**
     * Retrieves the list of subscribers for a given list key with various options.
     *
     * @link https://www.zoho.com/campaigns/help/developers/get-list-subscribers.html
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
     *
     * @throws ZohoApiException
     * @throws ConnectionException
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

        $response = $this->newRequest()
            ->get(sprintf('/getlistsubscribers?%s', http_build_query($params)))
            ->json();

        if ($response['status'] === 'error') {
            throw ZohoApiException::fromResponse($response);
        }

        return $response['list_of_details'] ?? [];
    }

    /**
     * Retrieves the count of subscribers for a given list key and status.
     *
     * @link https://www.zoho.com/campaigns/help/developers/view-total-contacts.html
     *
     * @param  string  $listKey  The list key.
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'unsub', 'bounce' and 'spam'. Default is 'active'
     * @return int The count of subscribers.
     *
     * @throws ZohoApiException
     * @throws ConnectionException
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

        $response = $this->newRequest()
            ->get(sprintf('/listsubscriberscount?%s', http_build_query($params)))
            ->json();

        if ($response['status'] === 'error') {
            throw ZohoApiException::fromResponse($response);
        }

        return $response['no_of_contacts'] ?? 0;
    }

    protected function newRequest(): PendingRequest
    {
        return Http::baseUrl($this->endpoint())
            ->withToken($this->accessToken->get(), 'Zoho-oauthtoken')
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]);
    }

    protected function endpoint(): string
    {
        return sprintf('https://campaigns.%s/api/v1.1', $this->region->domain());
    }
}
