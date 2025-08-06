<?php

namespace CapitalCom\Model;

class Position
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
    public $currency;

    /**
     * @var float
     */
    public $profit;

    /**
     * @var string
     */
    public $status;

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
        $this->stopLevel = $data['stopLevel'] ?? null;
        $this->limitLevel = $data['limitLevel'] ?? null;
        $this->currency = $data['currency'] ?? '';
        $this->profit = $data['profit'] ?? 0.0;
        $this->status = $data['status'] ?? '';
        $this->createdDate = $data['createdDate'] ?? '';
    }

    /**
     * Check if position is profitable
     *
     * @return bool
     */
    public function isProfitable(): bool
    {
        return $this->profit > 0;
    }

    /**
     * Check if position is long (BUY)
     *
     * @return bool
     */
    public function isLong(): bool
    {
        return strtoupper($this->direction) === 'BUY';
    }

    /**
     * Check if position is short (SELL)
     *
     * @return bool
     */
    public function isShort(): bool
    {
        return strtoupper($this->direction) === 'SELL';
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
            'stopLevel' => $this->stopLevel,
            'limitLevel' => $this->limitLevel,
            'currency' => $this->currency,
            'profit' => $this->profit,
            'status' => $this->status,
            'createdDate' => $this->createdDate,
        ];
    }
}