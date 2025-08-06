<?php

namespace CapitalCom;

class Configuration
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var bool
     */
    private $demo;

    /**
     * @var array
     */
    private $options;

    public function __construct(string $apiKey, bool $demo = false, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->demo = $demo;
        $this->options = array_merge([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => true,
            'debug' => false,
        ], $options);
    }

    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Check if using demo environment
     *
     * @return bool
     */
    public function isDemo(): bool
    {
        return $this->demo;
    }

    /**
     * Get configuration options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get specific option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }
}