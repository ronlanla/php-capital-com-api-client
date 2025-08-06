<?php

namespace CapitalCom;

use CapitalCom\Api\Account\AccountApi;
use CapitalCom\Api\Market\MarketApi;
use CapitalCom\Api\Trading\TradingApi;
use CapitalCom\Api\Watchlist\WatchlistApi;
use CapitalCom\Auth\SessionManager;
use CapitalCom\Exception\CapitalComException;
use CapitalCom\Http\HttpClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Client
{
    const LIVE_URL = 'https://api-capital.backend-capital.com';
    const DEMO_URL = 'https://demo-api-capital.backend-capital.com';
    const WEBSOCKET_LIVE_URL = 'wss://api-streaming-capital.backend-capital.com/connect';
    const WEBSOCKET_DEMO_URL = 'wss://demo-api-streaming-capital.backend-capital.com/connect';

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AccountApi|null
     */
    private $accountApi;

    /**
     * @var MarketApi|null
     */
    private $marketApi;

    /**
     * @var TradingApi|null
     */
    private $tradingApi;

    /**
     * @var WatchlistApi|null
     */
    private $watchlistApi;

    public function __construct(Configuration $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger ?: new NullLogger();
        
        $baseUrl = $config->isDemo() ? self::DEMO_URL : self::LIVE_URL;
        $this->httpClient = new HttpClient($baseUrl, $this->logger);
        $this->sessionManager = new SessionManager($this->httpClient, $config, $this->logger);
    }

    /**
     * Initialize session with API key and credentials
     *
     * @param string $identifier Email or username
     * @param string $password Plain or encrypted password
     * @param bool $encryptedPassword Whether the password is already encrypted
     * @return array Session data
     * @throws CapitalComException
     */
    public function login(string $identifier, string $password, bool $encryptedPassword = false): array
    {
        return $this->sessionManager->createSession($identifier, $password, $encryptedPassword);
    }

    /**
     * Logout and destroy session
     *
     * @return void
     * @throws CapitalComException
     */
    public function logout(): void
    {
        $this->sessionManager->destroySession();
    }

    /**
     * Get current session details
     *
     * @return array
     * @throws CapitalComException
     */
    public function getSession(): array
    {
        return $this->sessionManager->getSession();
    }

    /**
     * Switch active account
     *
     * @param string $accountId
     * @return void
     * @throws CapitalComException
     */
    public function switchAccount(string $accountId): void
    {
        $this->sessionManager->switchAccount($accountId);
    }

    /**
     * Get Account API instance
     *
     * @return AccountApi
     */
    public function account(): AccountApi
    {
        if (!$this->accountApi) {
            $this->accountApi = new AccountApi($this->httpClient);
        }

        return $this->accountApi;
    }

    /**
     * Get Market API instance
     *
     * @return MarketApi
     */
    public function market(): MarketApi
    {
        if (!$this->marketApi) {
            $this->marketApi = new MarketApi($this->httpClient);
        }

        return $this->marketApi;
    }

    /**
     * Get Trading API instance
     *
     * @return TradingApi
     */
    public function trading(): TradingApi
    {
        if (!$this->tradingApi) {
            $this->tradingApi = new TradingApi($this->httpClient);
        }

        return $this->tradingApi;
    }

    /**
     * Get Watchlist API instance
     *
     * @return WatchlistApi
     */
    public function watchlist(): WatchlistApi
    {
        if (!$this->watchlistApi) {
            $this->watchlistApi = new WatchlistApi($this->httpClient);
        }

        return $this->watchlistApi;
    }

    /**
     * Get WebSocket URL for streaming
     *
     * @return string
     */
    public function getWebSocketUrl(): string
    {
        return $this->config->isDemo() ? self::WEBSOCKET_DEMO_URL : self::WEBSOCKET_LIVE_URL;
    }

    /**
     * Get current CST token for WebSocket authentication
     *
     * @return string|null
     */
    public function getCstToken(): ?string
    {
        return $this->sessionManager->getCstToken();
    }

    /**
     * Get current Security token for WebSocket authentication
     *
     * @return string|null
     */
    public function getSecurityToken(): ?string
    {
        return $this->sessionManager->getSecurityToken();
    }

    /**
     * Ping to keep session alive
     *
     * @return void
     * @throws CapitalComException
     */
    public function ping(): void
    {
        $this->httpClient->post('/ping');
    }
}