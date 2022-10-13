<?php

namespace Keepsuit\Campaigns;

use Illuminate\Support\Arr;
use Keepsuit\Campaigns\Api\ZohoCampaignsApi;

class Campaigns
{
    public function __construct(protected ZohoCampaignsApi $zohoApi)
    {
    }

    /**
     * @return array{success: bool, message?: string}
     */
    public function subscribe(string $email, ?array $contactInfo, string $listName = null): array
    {
        $listKey = $this->resolveListKey($listName);

        $response = $this->zohoApi->campaignsListSubscribe($email, $contactInfo, $listKey);

        return [
            'success' => Arr::get($response, 'status') === 'success',
            'message' => Arr::get($response, 'message'),
        ];
    }

    protected function resolveListKey(?string $listName = null): string
    {
        $listName = $listName ?? config('campaigns.defaultListName');

        $lists = collect(config('campaigns.lists'));

        $listKey = Arr::get($lists->get($listName, []), 'listKey');

        if ($listKey === null) {
            throw new \Error(sprintf('Cannot resolve list %s', $listName));
        }

        return $listKey;
    }
}
