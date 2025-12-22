<?php

namespace Keepsuit\Campaigns;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Exceptions\TagNotFoundException;
use Keepsuit\Campaigns\Exceptions\ZohoCampaignsApiException;

/**
 * @phpstan-import-type ZohoCustomer from \Keepsuit\Campaigns\Api\ZohoCampaignsApi
 * @phpstan-import-type ZohoTag from \Keepsuit\Campaigns\Api\ZohoCampaignsApi
 * @phpstan-import-type ZohoContactField from \Keepsuit\Campaigns\Api\ZohoCampaignsApi
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
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function subscribe(string $email, array $contactInfo = [], ?string $list = null): void
    {
        $listKey = $this->resolveListKey($list);

        $this->zohoApi->listSubscribe($listKey, $email, $contactInfo);
    }

    /**
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function resubscribe(string $email, array $contactInfo = [], ?string $list = null): void
    {
        $listKey = $this->resolveListKey($list);

        $additionalParams = ['donotmail_resub' => 'true'];

        $this->zohoApi->listSubscribe($listKey, $email, $contactInfo, $additionalParams);
    }

    /**
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function unsubscribe(string $email, ?string $list = null): void
    {
        $listKey = $this->resolveListKey($list);

        $this->zohoApi->listUnsubscribe($listKey, $email);
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
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function subscribers(string $status = 'active', string $sort = 'asc', int $chunkSize = 500, ?string $list = null): LazyCollection
    {
        // Zoho API has a limit of 650 subscribers per request.
        $chunkSize = min(650, max(1, $chunkSize));

        $listKey = $this->resolveListKey($list);

        return LazyCollection::make(function () use ($status, $sort, $listKey, $chunkSize) {
            $fromIndex = 1;

            while (true) {
                $response = $this->zohoApi->listSubscribers($listKey, status: $status, sort: $sort, fromIndex: $fromIndex, range: $chunkSize);

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
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
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
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function tags(): Collection
    {
        return Collection::make($this->zohoApi->tags());
    }

    /**
     * Attach a tag to a contact.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function attachTag(string $email, string $tag): void
    {
        try {
            $this->zohoApi->tagAssociate($tag, $email);
        } catch (TagNotFoundException) {
            $this->zohoApi->tagCreate($tag);

            $this->zohoApi->tagAssociate($tag, $email);
        }
    }

    /**
     * Detach a tag from a contact.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function detachTag(string $email, string $tag): void
    {
        try {
            $this->zohoApi->tagDeassociate($tag, $email);
        } catch (TagNotFoundException) {
        }
    }

    /**
     * Get all contact fields.
     *
     * @return Collection<array-key,ZohoContactField>
     *
     * @throws HttpClientException
     */
    public function contactFields(): Collection
    {
        return Collection::make($this->zohoApi->contactFields());
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
