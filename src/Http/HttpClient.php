<?php

namespace CapitalCom\Http;

use CapitalCom\Exception\CapitalComException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class HttpClient
{
    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

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

    public function __construct(string $baseUrl, LoggerInterface $logger, array $options = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->logger = $logger;

        $defaultOptions = [
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => true,
            'headers' => [
                'User-Agent' => 'CapitalCom-PHP-Client/1.0',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        $this->client = new GuzzleClient(array_merge($defaultOptions, $options));
    }

    /**
     * Set authentication tokens for subsequent requests
     *
     * @param string|null $cstToken
     * @param string|null $securityToken
     * @return void
     */
    public function setAuthTokens(?string $cstToken, ?string $securityToken): void
    {
        $this->cstToken = $cstToken;
        $this->securityToken = $securityToken;
    }

    /**
     * Perform GET request
     *
     * @param string $uri
     * @param array $query
     * @param array $headers
     * @return ResponseInterface
     * @throws CapitalComException
     */
    public function get(string $uri, array $query = [], array $headers = []): ResponseInterface
    {
        $options = [];
        
        if (!empty($query)) {
            $options['query'] = $query;
        }

        return $this->request('GET', $uri, $options, $headers);
    }

    /**
     * Perform POST request
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return ResponseInterface
     * @throws CapitalComException
     */
    public function post(string $uri, array $data = [], array $headers = []): ResponseInterface
    {
        $options = [];
        
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->request('POST', $uri, $options, $headers);
    }

    /**
     * Perform PUT request
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return ResponseInterface
     * @throws CapitalComException
     */
    public function put(string $uri, array $data = [], array $headers = []): ResponseInterface
    {
        $options = [];
        
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->request('PUT', $uri, $options, $headers);
    }

    /**
     * Perform DELETE request
     *
     * @param string $uri
     * @param array $headers
     * @return ResponseInterface
     * @throws CapitalComException
     */
    public function delete(string $uri, array $headers = []): ResponseInterface
    {
        return $this->request('DELETE', $uri, [], $headers);
    }

    /**
     * Perform HTTP request with error handling
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param array $headers
     * @return ResponseInterface
     * @throws CapitalComException
     */
    private function request(string $method, string $uri, array $options = [], array $headers = []): ResponseInterface
    {
        // Add authentication headers if available
        $requestHeaders = $this->buildHeaders($headers);
        
        if (!empty($requestHeaders)) {
            $options['headers'] = array_merge(
                $options['headers'] ?? [],
                $requestHeaders
            );
        }

        $this->logger->debug('Making HTTP request', [
            'method' => $method,
            'uri' => $uri,
            'options' => $this->sanitizeLogOptions($options),
        ]);

        try {
            $response = $this->client->request($method, $uri, $options);
            
            $this->logger->debug('HTTP response received', [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
            ]);

            return $response;

        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            throw new CapitalComException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Build request headers including authentication tokens
     *
     * @param array $additionalHeaders
     * @return array
     */
    private function buildHeaders(array $additionalHeaders = []): array
    {
        $headers = $additionalHeaders;

        if ($this->cstToken) {
            $headers['CST'] = $this->cstToken;
        }

        if ($this->securityToken) {
            $headers['X-SECURITY-TOKEN'] = $this->securityToken;
        }

        return $headers;
    }

    /**
     * Handle request exceptions and convert to CapitalComException
     *
     * @param RequestException $e
     * @throws CapitalComException
     */
    private function handleRequestException(RequestException $e): void
    {
        $response = $e->getResponse();
        
        if ($response) {
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            
            $this->logger->error('HTTP error response', [
                'status_code' => $statusCode,
                'response_body' => $body,
            ]);

            // Try to decode JSON error response
            $errorData = json_decode($body, true);
            
            if ($errorData && isset($errorData['errorCode'])) {
                throw new CapitalComException(
                    $errorData['message'] ?? 'API Error',
                    $statusCode,
                    $e,
                    $errorData['errorCode']
                );
            }

            // Fallback to HTTP status code
            $message = $this->getStatusCodeMessage($statusCode);
            throw new CapitalComException($message, $statusCode, $e);
        }

        throw new CapitalComException('Request failed: ' . $e->getMessage(), 0, $e);
    }

    /**
     * Get human-readable message for HTTP status codes
     *
     * @param int $statusCode
     * @return string
     */
    private function getStatusCodeMessage(int $statusCode): string
    {
        switch ($statusCode) {
            case 400:
                return 'Bad Request - Invalid parameters';
            case 401:
                return 'Unauthorized - Invalid credentials or session expired';
            case 403:
                return 'Forbidden - Access denied';
            case 404:
                return 'Not Found - Endpoint or resource not found';
            case 429:
                return 'Rate Limit Exceeded - Too many requests';
            case 500:
                return 'Internal Server Error';
            case 502:
                return 'Bad Gateway';
            case 503:
                return 'Service Unavailable';
            default:
                return "HTTP Error {$statusCode}";
        }
    }

    /**
     * Sanitize options for logging (remove sensitive data)
     *
     * @param array $options
     * @return array
     */
    private function sanitizeLogOptions(array $options): array
    {
        $sanitized = $options;

        // Remove sensitive headers
        if (isset($sanitized['headers'])) {
            foreach (['X-CAP-API-KEY', 'CST', 'X-SECURITY-TOKEN'] as $sensitiveHeader) {
                if (isset($sanitized['headers'][$sensitiveHeader])) {
                    $sanitized['headers'][$sensitiveHeader] = '***';
                }
            }
        }

        // Remove password from JSON data
        if (isset($sanitized['json']['password'])) {
            $sanitized['json']['password'] = '***';
        }

        return $sanitized;
    }
}