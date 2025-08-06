<?php

namespace CapitalCom\Api\Trading;

use CapitalCom\Exception\CapitalComException;
use CapitalCom\Http\HttpClient;

class TradingApi
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
     * Get all positions
     *
     * @return array
     * @throws CapitalComException
     */
    public function getPositions(): array
    {
        $response = $this->httpClient->get('/positions');
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get specific position by deal ID
     *
     * @param string $dealId Deal ID of the position
     * @return array
     * @throws CapitalComException
     */
    public function getPosition(string $dealId): array
    {
        $response = $this->httpClient->get("/positions/{$dealId}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Open new position
     *
     * @param string $epic Market epic
     * @param string $direction BUY or SELL
     * @param float $size Position size
     * @param string $orderType MARKET or LIMIT
     * @param string $timeInForce FILL_OR_KILL, GOOD_TILL_CANCELLED, GOOD_TILL_DATE
     * @param float|null $level Order level (for limit orders)
     * @param float|null $stopLevel Stop loss level
     * @param float|null $limitLevel Take profit level
     * @param bool $guaranteedStop Whether to use guaranteed stop
     * @param array $options Additional options
     * @return array
     * @throws CapitalComException
     */
    public function openPosition(
        string $epic,
        string $direction,
        float $size,
        string $orderType = 'MARKET',
        string $timeInForce = 'FILL_OR_KILL',
        ?float $level = null,
        ?float $stopLevel = null,
        ?float $limitLevel = null,
        bool $guaranteedStop = false,
        array $options = []
    ): array {
        $data = array_merge([
            'epic' => $epic,
            'direction' => strtoupper($direction),
            'size' => $size,
            'orderType' => $orderType,
            'timeInForce' => $timeInForce,
            'guaranteedStop' => $guaranteedStop,
        ], $options);

        if ($level !== null) {
            $data['level'] = $level;
        }
        
        if ($stopLevel !== null) {
            $data['stopLevel'] = $stopLevel;
        }
        
        if ($limitLevel !== null) {
            $data['limitLevel'] = $limitLevel;
        }

        $response = $this->httpClient->post('/positions', $data);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Update existing position
     *
     * @param string $dealId Deal ID of position to update
     * @param array $updates Updates to apply
     * @return array
     * @throws CapitalComException
     */
    public function updatePosition(string $dealId, array $updates): array
    {
        $response = $this->httpClient->put("/positions/{$dealId}", $updates);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Close position
     *
     * @param string $dealId Deal ID of position to close
     * @param string $direction Close direction (opposite of position direction)
     * @param float|null $size Size to close (null = close entire position)
     * @return array
     * @throws CapitalComException
     */
    public function closePosition(string $dealId, string $direction, ?float $size = null): array
    {
        $data = ['direction' => strtoupper($direction)];
        
        if ($size !== null) {
            $data['size'] = $size;
        }

        $response = $this->httpClient->delete("/positions/{$dealId}", $data);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get all working orders
     *
     * @return array
     * @throws CapitalComException
     */
    public function getWorkingOrders(): array
    {
        $response = $this->httpClient->get('/workingorders');
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get specific working order
     *
     * @param string $dealId Deal ID of the working order
     * @return array
     * @throws CapitalComException
     */
    public function getWorkingOrder(string $dealId): array
    {
        $response = $this->httpClient->get("/workingorders/{$dealId}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Create working order
     *
     * @param string $epic Market epic
     * @param string $direction BUY or SELL
     * @param float $size Order size
     * @param float $level Order level
     * @param string $type LIMIT or STOP
     * @param string $timeInForce GOOD_TILL_CANCELLED or GOOD_TILL_DATE
     * @param string|null $goodTillDate Expiry date for GTD orders (ISO 8601 format)
     * @param float|null $stopLevel Stop loss level
     * @param float|null $limitLevel Take profit level
     * @param bool $guaranteedStop Whether to use guaranteed stop
     * @return array
     * @throws CapitalComException
     */
    public function createWorkingOrder(
        string $epic,
        string $direction,
        float $size,
        float $level,
        string $type = 'LIMIT',
        string $timeInForce = 'GOOD_TILL_CANCELLED',
        ?string $goodTillDate = null,
        ?float $stopLevel = null,
        ?float $limitLevel = null,
        bool $guaranteedStop = false
    ): array {
        $data = [
            'epic' => $epic,
            'direction' => strtoupper($direction),
            'size' => $size,
            'level' => $level,
            'type' => $type,
            'timeInForce' => $timeInForce,
            'guaranteedStop' => $guaranteedStop,
        ];

        if ($goodTillDate !== null) {
            $data['goodTillDate'] = $goodTillDate;
        }
        
        if ($stopLevel !== null) {
            $data['stopLevel'] = $stopLevel;
        }
        
        if ($limitLevel !== null) {
            $data['limitLevel'] = $limitLevel;
        }

        $response = $this->httpClient->post('/workingorders', $data);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Update working order
     *
     * @param string $dealId Deal ID of order to update
     * @param array $updates Updates to apply
     * @return array
     * @throws CapitalComException
     */
    public function updateWorkingOrder(string $dealId, array $updates): array
    {
        $response = $this->httpClient->put("/workingorders/{$dealId}", $updates);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Cancel working order
     *
     * @param string $dealId Deal ID of order to cancel
     * @return array
     * @throws CapitalComException
     */
    public function cancelWorkingOrder(string $dealId): array
    {
        $response = $this->httpClient->delete("/workingorders/{$dealId}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get deal confirmation
     *
     * @param string $dealReference Deal reference from position/order response
     * @return array
     * @throws CapitalComException
     */
    public function getDealConfirmation(string $dealReference): array
    {
        $response = $this->httpClient->get("/confirms/{$dealReference}");
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get account activity history
     *
     * @param string|null $from Start date (ISO 8601 format)
     * @param string|null $to End date (ISO 8601 format)
     * @param string|null $lastPeriod Last period (e.g., "1d", "1w", "1m")
     * @param bool $detailed Whether to include detailed information
     * @param string|null $dealId Filter by specific deal ID
     * @param string|null $filter FIQL filter string
     * @return array
     * @throws CapitalComException
     */
    public function getActivityHistory(
        ?string $from = null,
        ?string $to = null,
        ?string $lastPeriod = null,
        bool $detailed = false,
        ?string $dealId = null,
        ?string $filter = null
    ): array {
        $query = ['detailed' => $detailed ? 'true' : 'false'];
        
        if ($from !== null) {
            $query['from'] = $from;
        }
        
        if ($to !== null) {
            $query['to'] = $to;
        }
        
        if ($lastPeriod !== null) {
            $query['lastPeriod'] = $lastPeriod;
        }
        
        if ($dealId !== null) {
            $query['dealId'] = $dealId;
        }
        
        if ($filter !== null) {
            $query['filter'] = $filter;
        }

        $response = $this->httpClient->get('/history/activity', $query);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get transaction history
     *
     * @param string|null $from Start date (ISO 8601 format)
     * @param string|null $to End date (ISO 8601 format)
     * @param string|null $lastPeriod Last period (e.g., "1d", "1w", "1m")
     * @param string|null $type Transaction type filter
     * @return array
     * @throws CapitalComException
     */
    public function getTransactionHistory(
        ?string $from = null,
        ?string $to = null,
        ?string $lastPeriod = null,
        ?string $type = null
    ): array {
        $query = [];
        
        if ($from !== null) {
            $query['from'] = $from;
        }
        
        if ($to !== null) {
            $query['to'] = $to;
        }
        
        if ($lastPeriod !== null) {
            $query['lastPeriod'] = $lastPeriod;
        }
        
        if ($type !== null) {
            $query['type'] = $type;
        }

        $response = $this->httpClient->get('/history/transactions', $query);
        return json_decode($response->getBody()->getContents(), true);
    }
}