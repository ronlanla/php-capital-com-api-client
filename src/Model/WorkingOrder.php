<?php

namespace CapitalCom\Model;

class WorkingOrder
{
    /**
     * @var string
     */
    public $dealId;

    /**
     * @var string
     */
    public $epic;

    /**
     * @var string
     */
    public $direction;

    /**
     * @var float
     */
    public $size;

    /**
     * @var float
     */
    public $level;

    /**
     * @var string
     */
    public $type;

    /**
     * @var float|null
     */
    public $stopLevel;

    /**
     * @var float|null
     */
    public $limitLevel;

    /**
     * @var string
     */
    public $timeInForce;

    /**
     * @var string|null
     */
    public $goodTillDate;

    /**
     * @var string
     */
    public $createdDate;

    public function __construct(array $data = [])
    {
        $this->dealId = $data['dealId'] ?? '';
        $this->epic = $data['epic'] ?? '';
        $this->direction = $data['direction'] ?? '';
        $this->size = $data['size'] ?? 0.0;
        $this->level = $data['level'] ?? 0.0;
        $this->type = $data['type'] ?? '';
        $this->stopLevel = $data['stopLevel'] ?? null;
        $this->limitLevel = $data['limitLevel'] ?? null;
        $this->timeInForce = $data['timeInForce'] ?? '';
        $this->goodTillDate = $data['goodTillDate'] ?? null;
        $this->createdDate = $data['createdDate'] ?? '';
    }

    /**
     * Check if this is a buy order
     *
     * @return bool
     */
    public function isBuyOrder(): bool
    {
        return strtoupper($this->direction) === 'BUY';
    }

    /**
     * Check if this is a sell order
     *
     * @return bool
     */
    public function isSellOrder(): bool
    {
        return strtoupper($this->direction) === 'SELL';
    }

    /**
     * Check if this is a limit order
     *
     * @return bool
     */
    public function isLimitOrder(): bool
    {
        return strtoupper($this->type) === 'LIMIT';
    }

    /**
     * Check if this is a stop order
     *
     * @return bool
     */
    public function isStopOrder(): bool
    {
        return strtoupper($this->type) === 'STOP';
    }

    /**
     * Check if order is Good Till Cancelled
     *
     * @return bool
     */
    public function isGoodTillCancelled(): bool
    {
        return strtoupper($this->timeInForce) === 'GOOD_TILL_CANCELLED';
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'dealId' => $this->dealId,
            'epic' => $this->epic,
            'direction' => $this->direction,
            'size' => $this->size,
            'level' => $this->level,
            'type' => $this->type,
            'stopLevel' => $this->stopLevel,
            'limitLevel' => $this->limitLevel,
            'timeInForce' => $this->timeInForce,
            'goodTillDate' => $this->goodTillDate,
            'createdDate' => $this->createdDate,
        ];
    }
}