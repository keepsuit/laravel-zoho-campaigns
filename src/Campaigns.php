<?php

namespace Keepsuit\Campaigns;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Keepsuit\Campaigns\Api\ZohoApiException;
use Keepsuit\Campaigns\Api\ZohoCampaignsApi;

class Campaigns
{
    protected string $defaultListName;

    /**
     * @var Collection<string,array{listKey:string}>
     */
    protected Collection $lists;

    public function __construct(protected ZohoCampaignsApi $zohoApi)
    {
        $this->defaultListName = config('campaigns.defaultListName', '');
        $this->lists = collect(config('campaigns.lists', []));
    }

    /**
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function subscribe(string $email, array $contactInfo = [], ?string $listName = null): string
    {
        $listKey = $this->resolveListKey($listName);

        return $this->zohoApi->listSubscribe($listKey, $email, $contactInfo);
    }

    /**
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function resubscribe(string $email, array $contactInfo = [], ?string $listName = null): string
    {
        $listKey = $this->resolveListKey($listName);

        $additionalParams = ['donotmail_resub' => 'true'];

        return $this->zohoApi->listSubscribe($listKey, $email, $contactInfo, $additionalParams);
    }

    /**
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function unsubscribe(string $email, ?string $listName = null): string
    {
        $listKey = $this->resolveListKey($listName);

        return $this->zohoApi->listUnsubscribe($listKey, $email);
    }

    /**
     * Retrieves subscribers for a given list name.
     *
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'recent', 'mostrecent', 'unsub', and 'bounce'. Default is 'active'
     * @param  string  $sort  The sort order of the results. Possible values are 'asc' and 'desc'. Default is 'asc'.
     * @param  int  $chunkSize  The number of subscribers to retrieve per request.
     * @param  string|null  $listName  The name of the list. If null, the default list name will be used.
     * @return LazyCollection<array-key, array{
     *      zuid: string,
     *      phone: string,
     *      contact_email: string,
     *      firstname: string,
     *      lastname: string,
     *      companyname: string,
     *  }> The list of subscribers.
     *
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function subscribers(string $status = 'active', string $sort = 'asc', int $chunkSize = 500, ?string $listName = null): LazyCollection
    {
        // Zoho API has a limit of 650 subscribers per request.
        $chunkSize = min(650, max(1, $chunkSize));

        $listKey = $this->resolveListKey($listName);

        return LazyCollection::make(function () use ($status, $sort, $listKey, $chunkSize) {
            $fromIndex = 1;

            while (true) {
                try {
                    $response = $this->zohoApi->listSubscribers($listKey, status: $status, sort: $sort, fromIndex: $fromIndex, range: $chunkSize);
                } catch (ZohoApiException $exception) {
                    // If there are no other subscribers the api will return error 2502 with message "Yet,There are no contacts in this list."
                    if ($exception->getCode() === 2502) {
                        break;
                    }

                    throw $exception;
                }

                foreach ($response as $subscriber) {
                    yield $subscriber;
                }

                if (count($response) < $chunkSize) {
                    break;
                }

                $fromIndex += $chunkSize;
            }
        });
    }

    /**
     * Retrieves the count of subscribers for a given list name and status.
     *
     * @param  string  $status  The status of the subscribers to count. Possible values are 'active', 'unsub', 'bounce', and 'spam'.
     * @param  string|null  $listName  The name of the list. If null, the default list name will be used.
     * @return int The count of subscribers.
     *
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function subscribersCount(string $status = 'active', ?string $listName = null): int
    {
        $listKey = $this->resolveListKey($listName);

        return $this->zohoApi->listSubscribersCount($listKey, $status);
    }

    protected function resolveListKey(?string $listName = null): string
    {
        $listName = $listName ?? $this->defaultListName;

        $listKey = Arr::get($this->lists->get($listName, []), 'listKey');

        if ($listKey === null) {
            throw new \Error(sprintf('Cannot resolve list %s', $listName));
        }

        return $listKey;
    }
}
