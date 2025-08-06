# Capital.com PHP API Client

A PHP client library for the Capital.com trading API, providing easy access to trading, market data, and account management functionality.

## Features

- **Complete API Coverage**: All Capital.com REST API endpoints
- **Type-Safe**: Full type hints and PHPDoc annotations
- **Error Handling**: Comprehensive exception handling with specific error types
- **Session Management**: Automatic authentication and session handling
- **Rate Limiting**: Built-in protection against API rate limits
- **Logging**: PSR-3 compatible logging support
- **Testing**: Full PHPUnit test suite
- **Examples**: Comprehensive usage examples

## Installation

Install via Composer:

```bash
composer require capital-com/api-client
```

## Requirements

- PHP 7.4 or higher
- OpenSSL extension (for password encryption)
- JSON extension
- Guzzle HTTP client

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use CapitalCom\Client;
use CapitalCom\Configuration;

// Initialize configuration
$config = new Configuration(
    'your-api-key',
    true // Use demo environment
);

// Create client
$client = new Client($config);

// Login
$session = $client->login('your-email@example.com', 'your-password');

// Get market data
$markets = $client->market()->searchMarkets('Bitcoin');
$serverTime = $client->market()->getTime();

// Trading operations
$positions = $client->trading()->getPositions();
$orders = $client->trading()->getWorkingOrders();

// Account management
$accounts = $client->account()->getAccounts();
$preferences = $client->account()->getPreferences();

// Logout
$client->logout();
```

## Configuration

### Basic Configuration

```php
use CapitalCom\Configuration;

$config = new Configuration(
    'your-api-key',
    false, // false = live, true = demo
    [
        'timeout' => 30,
        'connect_timeout' => 10,
        'verify' => true,
        'debug' => false
    ]
);
```

### Getting an API Key

1. Create a Capital.com trading account
2. Enable Two-Factor Authentication (2FA)
3. Go to Settings > API integrations > Generate new key
4. Save your API key securely

## API Documentation

### Market Data

```php
// Search markets
$markets = $client->market()->searchMarkets('oil');

// Get specific market
$market = $client->market()->getMarket('OIL_CRUDE');

// Get historical prices
$prices = $client->market()->getPrices(
    'OIL_CRUDE',
    'DAY',
    100,
    '2023-01-01T00:00:00Z',
    '2023-12-31T23:59:59Z'
);

// Get client sentiment
$sentiment = $client->market()->getClientSentiment();
```

### Trading

```php
// Get positions
$positions = $client->trading()->getPositions();

// Open position
$result = $client->trading()->openPosition(
    'SILVER',
    'BUY',
    1.0,
    'MARKET',
    'FILL_OR_KILL',
    null,
    24.00, // Stop loss
    25.00  // Take profit
);

// Create working order
$order = $client->trading()->createWorkingOrder(
    'GOLD',
    'BUY',
    0.5,
    2000.00,
    'LIMIT',
    'GOOD_TILL_CANCELLED'
);

// Get transaction history
$history = $client->trading()->getTransactionHistory(
    '2023-01-01T00:00:00Z',
    '2023-12-31T23:59:59Z'
);
```

### Account Management

```php
// Get accounts
$accounts = $client->account()->getAccounts();

// Get preferences
$preferences = $client->account()->getPreferences();

// Update leverage
$client->account()->updateLeverages([
    'SILVER' => 10,
    'GOLD' => 20
]);

// Enable hedging mode
$client->account()->setHedgingMode(true);
```

### Watchlists

```php
// Get watchlists
$watchlists = $client->watchlist()->getWatchlists();

// Create watchlist
$watchlist = $client->watchlist()->createWatchlist(
    'My Metals',
    ['GOLD', 'SILVER', 'PLATINUM']
);

// Add instrument
$client->watchlist()->addInstrument($watchlistId, 'COPPER');
```

## Error Handling

The library provides comprehensive error handling with specific exception types:

```php
use CapitalCom\Exception\CapitalComException;
use CapitalCom\Exception\AuthenticationException;
use CapitalCom\Exception\RateLimitException;

try {
    $client->login('user@example.com', 'password');
} catch (AuthenticationException $e) {
    echo "Login failed: " . $e->getMessage();
} catch (RateLimitException $e) {
    echo "Rate limit exceeded, please wait";
} catch (CapitalComException $e) {
    echo "API Error: " . $e->getFullMessage();
    
    // Check error type
    if ($e->isAuthenticationError()) {
        // Handle auth error
    } elseif ($e->isValidationError()) {
        // Handle validation error
    }
}
```

## Logging

The client supports PSR-3 compatible logging:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('capital-com');
$logger->pushHandler(new StreamHandler('capital-com.log', Logger::DEBUG));

$client = new Client($config, $logger);
```

## Rate Limiting

The API has the following rate limits:

- Maximum 10 requests per second per user
- POST /session: 1 request per second per API key
- POST /positions and /workingorders: 1000 requests per hour in Demo
- Session duration: 10 minutes

## WebSocket Support

For real-time market data, use the WebSocket connection:

```php
$websocketUrl = $client->getWebSocketUrl();
$cstToken = $client->getCstToken();
$securityToken = $client->getSecurityToken();

// Use with your preferred WebSocket client library
```

## Testing

Run the test suite:

```bash
composer test
composer phpstan
composer phpcs
```

## Examples

See the `examples/` directory for complete usage examples:

- `basic_usage.php` - Basic API operations
- `trading_example.php` - Trading operations
- `market_data_example.php` - Market data retrieval
- `websocket_example.php` - WebSocket streaming

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## Support

- API Documentation: https://capital.com/api-documentation
- Support Email: support@capital.com
- Issues: https://github.com/ronlanla/php-capital-com-api-client/issues

## Disclaimer

Trading CFDs and forex involves significant risk of loss. This library is for educational and development purposes only. Always use proper risk management when trading with real money.