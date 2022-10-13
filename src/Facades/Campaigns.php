<?php

namespace Keepsuit\Campaigns\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Keepsuit\Campaigns\Campaigns
 */
class Campaigns extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Keepsuit\Campaigns\Campaigns::class;
    }
}
