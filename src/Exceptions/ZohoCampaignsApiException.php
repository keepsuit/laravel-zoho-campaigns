<?php

namespace Keepsuit\Campaigns\Exceptions;

class ZohoCampaignsApiException extends ZohoApiException
{
    public static function fromResponse(array $response): static
    {
        return new static(
            errorId: (string) $response['code'],
            message: $response['message'] ?? 'An error occurred',
        );
    }
}
