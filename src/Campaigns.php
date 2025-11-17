<?php

namespace Keepsuit\Campaigns;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Keepsuit\Campaigns\Api\ZohoApiException;
use Keepsuit\Campaigns\Api\ZohoCampaignsApi;

/**
 * @phpstan-import-type ZohoCustomer from \Keepsuit\Campaigns\Api\ZohoCampaignsApi
 * @phpstan-import-type ZohoTag from \Keepsuit\Campaigns\Api\ZohoCampaignsApi
 */
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
    public function subscribe(string $email, array $contactInfo = [], ?string $list = null): string
    {
        $listKey = $this->resolveListKey($list);

        return $this->zohoApi->listSubscribe($listKey, $email, $contactInfo);
    }

    /**
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function resubscribe(string $email, array $contactInfo = [], ?string $list = null): string
    {
        $listKey = $this->resolveListKey($list);

        $additionalParams = ['donotmail_resub' => 'true'];

        return $this->zohoApi->listSubscribe($listKey, $email, $contactInfo, $additionalParams);
    }

    /**
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function unsubscribe(string $email, ?string $list = null): string
    {
        $listKey = $this->resolveListKey($list);

        return $this->zohoApi->listUnsubscribe($listKey, $email);
    }

    /**
     * Retrieves subscribers for a given list name.
     *
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'recent', 'mostrecent', 'unsub', and 'bounce'. Default is 'active'
     * @param  string  $sort  The sort order of the results. Possible values are 'asc' and 'desc'. Default is 'asc'.
     * @param  int  $chunkSize  The number of subscribers to retrieve per request.
     * @param  string|null  $list  The name or the key of the list. If null, the default list name will be used.
     * @return LazyCollection<array-key, ZohoCustomer> The list of subscribers.
     *
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function subscribers(string $status = 'active', string $sort = 'asc', int $chunkSize = 500, ?string $list = null): LazyCollection
    {
        // Zoho API has a limit of 650 subscribers per request.
        $chunkSize = min(650, max(1, $chunkSize));

        $listKey = $this->resolveListKey($list);

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
     * @param  string|null  $list  The name or the key of the list. If null, the default list name will be used.
     * @return int The count of subscribers.
     *
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function subscribersCount(string $status = 'active', ?string $list = null): int
    {
        $listKey = $this->resolveListKey($list);

        return $this->zohoApi->listSubscribersCount($listKey, $status);
    }

    /**
     * Retrieve all existing tags.
     *
     * @return Collection<array-key,ZohoTag>
     *
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function tags(): Collection
    {
        return Collection::make($this->zohoApi->tags());
    }

    /**
     * Attach a tag to a contact.
     *
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function attachTag(string $email, string $tag): string
    {
        return $this->zohoApi->tagAssociate($tag, $email);
    }

    /**
     * Detach a tag from a contact.
     *
     * @throws ConnectionException
     * @throws ZohoApiException
     */
    public function associateTag(string $email, string $tag): string
    {
        return $this->zohoApi->tagDeassociate($tag, $email);
    }

    protected function resolveListKey(?string $list = null): string
    {
        $listName = $list ?? $this->defaultListName;

        $listKey = Arr::get($this->lists->get($listName, []), 'listKey');

        if ($listKey === null && $list === null) {
            throw new \RuntimeException(sprintf('Cannot resolve list %s', $listName));
        }

        return $listKey ?? $list;
    }
}
