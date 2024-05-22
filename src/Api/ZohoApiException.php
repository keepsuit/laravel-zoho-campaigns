<?php

namespace Keepsuit\Campaigns\Api;

use RuntimeException;

class ZohoApiException extends RuntimeException
{
    final public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            message: $response['message'] ?? 'An error occurred',
            code: (int) $response['Code'],
        );
    }
}
