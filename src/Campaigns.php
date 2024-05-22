<?php

namespace Keepsuit\Campaigns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
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
     * @return array{success: bool, message?: string}
     */
    public function subscribe(string $email, ?array $contactInfo = [], ?string $listName = null): array
    {
        $listKey = $this->resolveListKey($listName);

        $response = $this->zohoApi->listSubscribe($listKey, $email, $contactInfo);

        return [
            'success' => Arr::get($response, 'status') === 'success',
            'message' => Arr::get($response, 'message'),
        ];
    }

    /**
     * @return array{success: bool, message?: string}
     */
    public function unsubscribe(string $email, ?string $listName = null): array
    {
        $listKey = $this->resolveListKey($listName);

        $response = $this->zohoApi->listUnsubscribe($listKey, $email);

        return [
            'success' => Arr::get($response, 'status') === 'success',
            'message' => Arr::get($response, 'message'),
        ];
    }

    /**
     * @return array{success: bool, message?: string}
     */
    public function resubscribe(string $email, ?array $contactInfo = [], ?string $listName = null): array
    {
        $listKey = $this->resolveListKey($listName);

        $additionalParams = ['donotmail_resub' => 'true'];

        $response = $this->zohoApi->listSubscribe($listKey, $email, $contactInfo, $additionalParams);

        return [
            'success' => Arr::get($response, 'status') === 'success',
            'message' => Arr::get($response, 'message'),
        ];
    }

    /**
     * Retrieves subscribers for a given list name.
     *
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'recent', 'mostrecent', 'unsub', and 'bounce'. Default is 'active'
     * @param  string  $sort  The sort order of the results. Possible values are 'asc' and 'desc'. Default is 'asc'.
     * @param  string|null  $listName  The name of the list. If null, the default list name will be used.
     * @return LazyCollection<array-key, array{
     *      zuid: string,
     *      phone: string,
     *      contact_email: string,
     *      firstname: string,
     *      lastname: string,
     *      companyname: string,
     *  }> The list of subscribers.
     */
    public function subscribers(string $status = 'active', string $sort = 'asc', ?string $listName = null): LazyCollection
    {
        $listKey = $this->resolveListKey($listName);

        return LazyCollection::make(function () use ($status, $sort, $listKey) {
            $fromIndex = 1;
            $range = 20;

            while (true) {
                $response = $this->zohoApi->listSubscribers($listKey, status: $status, sort: $sort, fromIndex: $fromIndex, range: $range);

                foreach ($response as $subscriber) {
                    yield $subscriber;
                }

                if (count($response) < $range) {
                    break;
                }

                $fromIndex += $range;
            }
        });
    }

    /**
     * Retrieves the count of subscribers for a given list name and status.
     *
     * @param  string  $status  The status of the subscribers to count. Possible values are 'active', 'unsub', 'bounce', and 'spam'.
     * @param  string|null  $listName  The name of the list. If null, the default list name will be used.
     * @return int The count of subscribers.
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
