<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CapitalCom\Client;
use CapitalCom\Configuration;
use CapitalCom\Exception\CapitalComException;

try {
    // Initialize configuration
    $config = new Configuration(
        'your-api-key-here',
        true // Use demo environment
    );

    // Create client
    $client = new Client($config);

    // Login with credentials
    echo "Logging in...\n";
    $session = $client->login('your-email@example.com', 'your-password');
    echo "Session created for account: " . $session['currentAccountId'] . "\n";

    // Get server time
    echo "\nGetting server time...\n";
    $time = $client->market()->getTime();
    echo "Server time: " . $time['serverTime'] . "\n";

    // Search for markets
    echo "\nSearching for Bitcoin markets...\n";
    $markets = $client->market()->searchMarkets('Bitcoin');
    echo "Found " . count($markets['markets']) . " Bitcoin markets\n";

    if (!empty($markets['markets'])) {
        $btcMarket = $markets['markets'][0];
        echo "First market: " . $btcMarket['instrumentName'] . " (" . $btcMarket['epic'] . ")\n";

        // Get detailed market information
        echo "\nGetting detailed market info...\n";
        $marketDetail = $client->market()->getMarket($btcMarket['epic']);
        echo "Market status: " . $marketDetail['snapshot']['marketStatus'] . "\n";
        echo "Bid: " . $marketDetail['snapshot']['bid'] . "\n";
        echo "Offer: " . $marketDetail['snapshot']['offer'] . "\n";
    }

    // Get account information
    echo "\nGetting accounts...\n";
    $accounts = $client->account()->getAccounts();
    echo "Number of accounts: " . count($accounts['accounts']) . "\n";

    // Get current positions
    echo "\nGetting positions...\n";
    $positions = $client->trading()->getPositions();
    echo "Number of open positions: " . count($positions['positions']) . "\n";

    // Get working orders
    echo "\nGetting working orders...\n";
    $orders = $client->trading()->getWorkingOrders();
    echo "Number of working orders: " . count($orders['workingOrders']) . "\n";

    // Get watchlists
    echo "\nGetting watchlists...\n";
    $watchlists = $client->watchlist()->getWatchlists();
    echo "Number of watchlists: " . count($watchlists['watchlists']) . "\n";

    // Example: Create a limit order (commented out for safety)
    /*
    echo "\nCreating limit order...\n";
    $orderResult = $client->trading()->createWorkingOrder(
        'SILVER',
        'BUY',
        1.0,
        24.50,
        'LIMIT',
        'GOOD_TILL_CANCELLED',
        null,
        24.00, // Stop loss
        25.00  // Take profit
    );
    echo "Order created with deal reference: " . $orderResult['dealReference'] . "\n";
    */

    // Logout
    echo "\nLogging out...\n";
    $client->logout();
    echo "Logged out successfully\n";

} catch (CapitalComException $e) {
    echo "Capital.com API Error: " . $e->getFullMessage() . "\n";
    
    if ($e->isAuthenticationError()) {
        echo "This is an authentication error. Please check your credentials.\n";
    }
    
    if ($e->isRateLimitError()) {
        echo "Rate limit exceeded. Please wait before making more requests.\n";
    }
    
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}