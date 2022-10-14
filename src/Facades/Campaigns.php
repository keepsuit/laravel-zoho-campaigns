<?php

namespace Keepsuit\Campaigns\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array subscribe(string $email, ?array $contactInfo = [], string $listName = null)
 * @method static array unsubscribe(string $email, string $listName = null)
 *
 * @see \Keepsuit\Campaigns\Campaigns
 */
class Campaigns extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Keepsuit\Campaigns\Campaigns::class;
    }
}
