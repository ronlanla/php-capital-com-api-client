<?php

namespace CapitalCom\Auth;

use CapitalCom\Configuration;
use CapitalCom\Exception\CapitalComException;
use CapitalCom\Http\HttpClient;
use Psr\Log\LoggerInterface;

class SessionManager
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string|null
     */
    private $cstToken;

    /**
     * @var string|null
     */
    private $securityToken;

    /**
     * @var array|null
     */
    private $sessionData;

    /**
     * @var int|null
     */
    private $sessionExpiry;

    public function __construct(HttpClient $httpClient, Configuration $config, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Create new session with encrypted password
     *
     * @param string $identifier
     * @param string $password
     * @param bool $encryptedPassword
     * @return array
     * @throws CapitalComException
     */
    public function createSession(string $identifier, string $password, bool $encryptedPassword = false): array
    {
        // If password is not already encrypted, encrypt it
        if (!$encryptedPassword) {
            $password = $this->encryptPassword($password);
            $encryptedPassword = true;
        }

        $response = $this->httpClient->post('/session', [
            'identifier' => $identifier,
            'password' => $password,
            'encryptedPassword' => $encryptedPassword,
        ], [
            'X-CAP-API-KEY' => $this->config->getApiKey(),
        ]);

        // Extract session tokens from headers
        $headers = $response->getHeaders();
        $this->cstToken = $headers['CST'][0] ?? null;
        $this->securityToken = $headers['X-SECURITY-TOKEN'][0] ?? null;

        if (!$this->cstToken || !$this->securityToken) {
            throw new CapitalComException('Session tokens not received');
        }

        // Set session expiry (10 minutes from now)
        $this->sessionExpiry = time() + 600;

        $body = json_decode($response->getBody()->getContents(), true);
        $this->sessionData = $body;

        $this->logger->info('Session created successfully', [
            'account_id' => $body['currentAccountId'] ?? null,
        ]);

        // Set tokens in HTTP client for future requests
        $this->httpClient->setAuthTokens($this->cstToken, $this->securityToken);

        return $body;
    }

    /**
     * Get current session details
     *
     * @return array
     * @throws CapitalComException
     */
    public function getSession(): array
    {
        $this->ensureValidSession();
        
        $response = $this->httpClient->get('/session');

        return json_decode($response->getBody()->getContents(), true);
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
        $this->ensureValidSession();
        
        $response = $this->httpClient->put('/session', [
            'accountId' => $accountId,
        ]);

        // Update security token if provided in response
        $headers = $response->getHeaders();
        if (isset($headers['X-SECURITY-TOKEN'][0])) {
            $this->securityToken = $headers['X-SECURITY-TOKEN'][0];
            $this->httpClient->setAuthTokens($this->cstToken, $this->securityToken);
        }

        // Update cached session data with new account ID
        if ($this->sessionData) {
            $this->sessionData['currentAccountId'] = $accountId;
        }

        $this->logger->info('Switched to account: ' . $accountId);
    }

    /**
     * Destroy session (logout)
     *
     * @return void
     * @throws CapitalComException
     */
    public function destroySession(): void
    {
        if ($this->cstToken) {
            try {
                $this->httpClient->delete('/session');
            } catch (\Exception $e) {
                $this->logger->warning('Error during logout: ' . $e->getMessage());
            }
        }

        $this->cstToken = null;
        $this->securityToken = null;
        $this->sessionData = null;
        $this->sessionExpiry = null;
        
        $this->httpClient->setAuthTokens(null, null);
        
        $this->logger->info('Session destroyed');
    }

    /**
     * Get CST token
     *
     * @return string|null
     */
    public function getCstToken(): ?string
    {
        return $this->cstToken;
    }

    /**
     * Get security token
     *
     * @return string|null
     */
    public function getSecurityToken(): ?string
    {
        return $this->securityToken;
    }

    /**
     * Get cached session data
     *
     * @return array|null
     */
    public function getCachedSessionData(): ?array
    {
        return $this->sessionData;
    }

    /**
     * Check if session is valid
     *
     * @return bool
     */
    public function hasValidSession(): bool
    {
        return $this->cstToken 
            && $this->securityToken 
            && $this->sessionExpiry 
            && time() < $this->sessionExpiry;
    }

    /**
     * Encrypt password using encryption key from API
     *
     * @param string $password
     * @return string
     * @throws CapitalComException
     */
    private function encryptPassword(string $password): string
    {
        $response = $this->httpClient->get('/session/encryptionKey', [], [
            'X-CAP-API-KEY' => $this->config->getApiKey(),
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        
        if (!isset($data['encryptionKey']) || !isset($data['timeStamp'])) {
            throw new CapitalComException('Invalid encryption key response');
        }

        return PasswordEncryptor::encrypt(
            $data['encryptionKey'],
            $data['timeStamp'],
            $password
        );
    }

    /**
     * Ensure session is valid, refresh if needed
     *
     * @throws CapitalComException
     */
    private function ensureValidSession(): void
    {
        if (!$this->hasValidSession()) {
            throw new CapitalComException('No valid session. Please login first.');
        }

        // Refresh session expiry on any activity
        $this->sessionExpiry = time() + 600;
    }
}