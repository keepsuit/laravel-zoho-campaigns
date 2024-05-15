<?php

namespace Keepsuit\Campaigns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
    public function resubscribe(string $email, ?array $contactInfo = [], string $listName = null): array
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
     * @param string|null $listName The name of the list. If null, the default list name will be used.
     * @param array $options An array of options for the request. Possible keys include:
     *     - 'sort': The sort order of the results. Possible values are 'asc' for ascending order and 'desc' for descending order.
     *     - 'fromindex': The starting index for the results. This is a number.
     *     - 'range': The range of results to retrieve. This is a number.
     *     - 'status': The status of the subscribers to retrieve. Possible values are 'active', 'recent', 'mostrecent', 'unsub', and 'bounce'.
     * @return array The list of subscribers.
     */
    public function getSubscribers(?string $listName = null, array $options = []): array
    {
        $listKey = $this->resolveListKey($listName);

        return $this->zohoApi->getListSubscribers($listKey, $options);
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
