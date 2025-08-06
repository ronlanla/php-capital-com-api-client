<?php

namespace CapitalCom\Exception;

use Exception;
use Throwable;

class CapitalComException extends Exception
{
    /**
     * @var string|null
     */
    private $errorCode;

    /**
     * @var array|null
     */
    private $context;

    public function __construct(
        string $message = '',
        int $code = 0,
        Throwable $previous = null,
        string $errorCode = null,
        array $context = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    /**
     * Get Capital.com specific error code
     *
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get additional context information
     *
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * Check if this is an authentication error
     *
     * @return bool
     */
    public function isAuthenticationError(): bool
    {
        return in_array($this->getCode(), [401, 403]) || 
               in_array($this->errorCode, ['INVALID_CREDENTIALS', 'SESSION_EXPIRED', 'INVALID_SESSION']);
    }

    /**
     * Check if this is a rate limit error
     *
     * @return bool
     */
    public function isRateLimitError(): bool
    {
        return $this->getCode() === 429 || $this->errorCode === 'RATE_LIMIT_EXCEEDED';
    }

    /**
     * Check if this is a validation error
     *
     * @return bool
     */
    public function isValidationError(): bool
    {
        return $this->getCode() === 400 || 
               in_array($this->errorCode, ['INVALID_PARAMETERS', 'VALIDATION_ERROR']);
    }

    /**
     * Get formatted error message with context
     *
     * @return string
     */
    public function getFullMessage(): string
    {
        $message = $this->getMessage();
        
        if ($this->errorCode) {
            $message = "[{$this->errorCode}] {$message}";
        }
        
        if ($this->context) {
            $contextStr = json_encode($this->context, JSON_PRETTY_PRINT);
            $message .= "\nContext: {$contextStr}";
        }
        
        return $message;
    }
}