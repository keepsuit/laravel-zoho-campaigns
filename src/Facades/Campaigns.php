<?php

namespace Keepsuit\Campaigns\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;

/**
 * @phpstan-import-type ZohoCustomer from \Keepsuit\Campaigns\Api\ZohoCampaignsApi
 * @phpstan-import-type ZohoTag from \Keepsuit\Campaigns\Api\ZohoCampaignsApi
 *
 * @method static string subscribe(string $email, array $contactInfo = [], ?string $list = null)
 * @method static string resubscribe(string $email, array $contactInfo = [], ?string $list = null)
 * @method static string unsubscribe(string $email, ?string $list = null)
 * @method static LazyCollection<array-key, ZohoCustomer> subscribers(string $status = 'active', string $sort = 'asc', int $chunkSize = 500, ?string $list = null)
 * @method static int subscribersCount(string $status = 'active', ?string $list = null)
 * @method static Collection<array-key,ZohoTag> tags()
 * @method static string attachTag(string $email, string $tag)
 * @method static string detachTag(string $email, string $tag)
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
