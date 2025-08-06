<?php

namespace CapitalCom\Exception;

class RateLimitException extends CapitalComException
{
    public function __construct(string $message = 'Rate limit exceeded', $code = 429, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}