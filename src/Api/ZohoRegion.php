<?php

namespace Keepsuit\Campaigns\Api;

enum ZohoRegion: string
{
    case UnitedStates = 'us';
    case Europe = 'eu';
    case India = 'in';
    case Australia = 'au';
    case Japan = 'jp';
    case China = 'cn';

    public function label(): string
    {
        return match ($this) {
            self::UnitedStates => 'United States',
            self::Europe => 'Europe',
            self::India => 'India',
            self::Australia => 'Australia',
            self::Japan => 'Japan',
            self::China => 'China',
        };
    }
}
