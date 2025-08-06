<?php

namespace CapitalCom\Model;

class Market
{
    /**
     * @var string
     */
    public $epic;

    /**
     * @var string
     */
    public $instrumentName;

    /**
     * @var string
     */
    public $marketStatus;

    /**
     * @var float
     */
    public $bid;

    /**
     * @var float
     */
    public $offer;

    /**
     * @var float
     */
    public $netChange;

    /**
     * @var float
     */
    public $percentageChange;

    /**
     * @var float
     */
    public $high;

    /**
     * @var float
     */
    public $low;

    /**
     * @var string
     */
    public $updateTime;

    public function __construct(array $data = [])
    {
        $this->epic = $data['epic'] ?? '';
        $this->instrumentName = $data['instrumentName'] ?? '';
        $this->marketStatus = $data['marketStatus'] ?? '';
        $this->bid = $data['bid'] ?? 0.0;
        $this->offer = $data['offer'] ?? 0.0;
        $this->netChange = $data['netChange'] ?? 0.0;
        $this->percentageChange = $data['percentageChange'] ?? 0.0;
        $this->high = $data['high'] ?? 0.0;
        $this->low = $data['low'] ?? 0.0;
        $this->updateTime = $data['updateTime'] ?? '';
    }

    /**
     * Get the spread between bid and offer
     *
     * @return float
     */
    public function getSpread(): float
    {
        return $this->offer - $this->bid;
    }

    /**
     * Get the midpoint price
     *
     * @return float
     */
    public function getMidPrice(): float
    {
        return ($this->bid + $this->offer) / 2;
    }

    /**
     * Check if market is trading
     *
     * @return bool
     */
    public function isTrading(): bool
    {
        return strtoupper($this->marketStatus) === 'TRADEABLE';
    }

    /**
     * Check if price is rising
     *
     * @return bool
     */
    public function isRising(): bool
    {
        return $this->netChange > 0;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'epic' => $this->epic,
            'instrumentName' => $this->instrumentName,
            'marketStatus' => $this->marketStatus,
            'bid' => $this->bid,
            'offer' => $this->offer,
            'netChange' => $this->netChange,
            'percentageChange' => $this->percentageChange,
            'high' => $this->high,
            'low' => $this->low,
            'updateTime' => $this->updateTime,
        ];
    }
}