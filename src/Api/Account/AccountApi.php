<?php

namespace CapitalCom\Api\Account;

use CapitalCom\Exception\CapitalComException;
use CapitalCom\Http\HttpClient;

class AccountApi
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get all accounts
     *
     * @return array
     * @throws CapitalComException
     */
    public function getAccounts(): array
    {
        $response = $this->httpClient->get('/accounts');
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get account preferences including trading modes and leverage settings
     *
     * @return array
     * @throws CapitalComException
     */
    public function getPreferences(): array
    {
        $response = $this->httpClient->get('/accounts/preferences');
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Update account preferences
     *
     * @param array $preferences Preferences to update
     * @return array
     * @throws CapitalComException
     */
    public function updatePreferences(array $preferences): array
    {
        $response = $this->httpClient->put('/accounts/preferences', $preferences);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Set hedging mode
     *
     * @param bool $enabled Whether to enable hedging mode
     * @return array
     * @throws CapitalComException
     */
    public function setHedgingMode(bool $enabled): array
    {
        return $this->updatePreferences(['hedgingMode' => $enabled]);
    }

    /**
     * Update leverage for specific markets
     *
     * @param array $leverages Array of epic => leverage pairs
     * @return array
     * @throws CapitalComException
     */
    public function updateLeverages(array $leverages): array
    {
        return $this->updatePreferences(['leverages' => $leverages]);
    }
}