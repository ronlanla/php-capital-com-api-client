<?php

namespace CapitalCom\Api\Market;

use CapitalCom\Exception\CapitalComException;
use CapitalCom\Http\HttpClient;

class MarketApi
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
     * Get server time
     *
     * @return array
     * @throws CapitalComException
     */
    public function getTime(): array
    {
        $response = $this->httpClient->get('/time');
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Search markets
     *
     * @param string|null $searchTerm Search term for market names
     * @param array|null $epics Array of specific epics to get
     * @return array
     * @throws CapitalComException
     */
    public function searchMarkets(?string $searchTerm = null, ?array $epics = null): array
    {
        $query = [];
        
        if ($searchTerm !== null) {
            $query['searchTerm'] = $searchTerm;
        }
        
        if ($epics !== null) {
            $query['epics'] = implode(',', $epics);
        }

        $response = $this->httpClient->get('/markets', $query);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get single market details
     *
     * @param string $epic Market epic
     * @return array
     * @throws CapitalComException
     */
    public function getMarket(string $epic): array
    {
        $response = $this->httpClient->get("/markets/{$epic}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get historical prices for a market
     *
     * @param string $epic Market epic
     * @param string $resolution Price resolution (MINUTE, MINUTE_5, MINUTE_15, MINUTE_30, HOUR, HOUR_4, DAY, WEEK)
     * @param int|null $max Maximum number of price points to return
     * @param string|null $from Start date (ISO 8601 format)
     * @param string|null $to End date (ISO 8601 format)
     * @return array
     * @throws CapitalComException
     */
    public function getPrices(
        string $epic,
        string $resolution = 'DAY',
        ?int $max = null,
        ?string $from = null,
        ?string $to = null
    ): array {
        $query = ['resolution' => $resolution];
        
        if ($max !== null) {
            $query['max'] = $max;
        }
        
        if ($from !== null) {
            $query['from'] = $from;
        }
        
        if ($to !== null) {
            $query['to'] = $to;
        }

        $response = $this->httpClient->get("/prices/{$epic}", $query);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get market navigation structure
     *
     * @return array
     * @throws CapitalComException
     */
    public function getMarketNavigation(): array
    {
        $response = $this->httpClient->get('/marketnavigation');
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get markets under specific navigation node
     *
     * @param string $nodeId Navigation node ID
     * @return array
     * @throws CapitalComException
     */
    public function getMarketNavigationNode(string $nodeId): array
    {
        $response = $this->httpClient->get("/marketnavigation/{$nodeId}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get client sentiment for markets
     *
     * @param array|null $marketIds Array of market IDs (optional)
     * @return array
     * @throws CapitalComException
     */
    public function getClientSentiment(?array $marketIds = null): array
    {
        $query = [];
        
        if ($marketIds !== null) {
            $query['marketIds'] = implode(',', $marketIds);
        }

        $response = $this->httpClient->get('/clientsentiment', $query);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get client sentiment for specific market
     *
     * @param string $marketId Market ID
     * @return array
     * @throws CapitalComException
     */
    public function getMarketClientSentiment(string $marketId): array
    {
        $response = $this->httpClient->get("/clientsentiment/{$marketId}");
        return json_decode($response->getBody()->getContents(), true);
    }
}