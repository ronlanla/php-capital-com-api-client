<?php

namespace CapitalCom\Api\Watchlist;

use CapitalCom\Exception\CapitalComException;
use CapitalCom\Http\HttpClient;

class WatchlistApi
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
     * Get all watchlists
     *
     * @return array
     * @throws CapitalComException
     */
    public function getWatchlists(): array
    {
        $response = $this->httpClient->get('/watchlists');
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get specific watchlist details
     *
     * @param string $watchlistId Watchlist ID
     * @return array
     * @throws CapitalComException
     */
    public function getWatchlist(string $watchlistId): array
    {
        $response = $this->httpClient->get("/watchlists/{$watchlistId}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Create new watchlist
     *
     * @param string $name Watchlist name
     * @param array $epics Array of instrument epics to add
     * @return array
     * @throws CapitalComException
     */
    public function createWatchlist(string $name, array $epics = []): array
    {
        $data = [
            'name' => $name,
            'epics' => $epics,
        ];

        $response = $this->httpClient->post('/watchlists', $data);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Update watchlist details
     *
     * @param string $watchlistId Watchlist ID
     * @param array $updates Updates to apply (name, epics, etc.)
     * @return array
     * @throws CapitalComException
     */
    public function updateWatchlist(string $watchlistId, array $updates): array
    {
        $response = $this->httpClient->put("/watchlists/{$watchlistId}", $updates);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Delete watchlist
     *
     * @param string $watchlistId Watchlist ID
     * @return array
     * @throws CapitalComException
     */
    public function deleteWatchlist(string $watchlistId): array
    {
        $response = $this->httpClient->delete("/watchlists/{$watchlistId}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Add instrument to watchlist
     *
     * @param string $watchlistId Watchlist ID
     * @param string $epic Instrument epic to add
     * @return array
     * @throws CapitalComException
     */
    public function addInstrument(string $watchlistId, string $epic): array
    {
        $response = $this->httpClient->put("/watchlists/{$watchlistId}/{$epic}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Remove instrument from watchlist
     *
     * @param string $watchlistId Watchlist ID
     * @param string $epic Instrument epic to remove
     * @return array
     * @throws CapitalComException
     */
    public function removeInstrument(string $watchlistId, string $epic): array
    {
        $response = $this->httpClient->delete("/watchlists/{$watchlistId}/{$epic}");
        return json_decode($response->getBody()->getContents(), true);
    }
}