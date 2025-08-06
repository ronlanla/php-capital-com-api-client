<?php

namespace CapitalCom\Exception;

class AuthenticationException extends CapitalComException
{
    public function __construct(string $message = 'Authentication failed', $code = 401, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}