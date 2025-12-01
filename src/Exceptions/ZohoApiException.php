<?php

namespace Keepsuit\Campaigns\Exceptions;

class ZohoApiException extends \Exception
{
    final public function __construct(
        protected string $errorId,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function getErrorId(): string
    {
        return $this->errorId;
    }
}
