<?php

namespace Keepsuit\Campaigns\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;

/**
 * @phpstan-import-type ZohoCustomer from \Keepsuit\Campaigns\Api\ZohoCampaignsApi
 *
 * @method static string subscribe(string $email, array $contactInfo = [], string $listName = null, string $listKey = null)
 * @method static string resubscribe(string $email, array $contactInfo = [], string $listName = null, string $listKey = null)
 * @method static string unsubscribe(string $email, string $listName = null, string $listKey = null)
 * @method static LazyCollection<array-key, ZohoCustomer> subscribers(string $status = 'active', string $sort = 'asc', int $chunkSize = 500, ?string $listName = null, ?string $listKey = null)
 * @method static int subscribersCount(string $status = 'active', ?string $listName = null, ?string $listKey = null)
 *
 * @see \Keepsuit\Campaigns\Campaigns
 */
class Campaigns extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Keepsuit\Campaigns\Campaigns::class;
    }
}
