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
