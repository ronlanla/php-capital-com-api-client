<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CapitalCom\Client;
use CapitalCom\Configuration;
use CapitalCom\Exception\CapitalComException;

try {
    // Initialize configuration for demo environment
    $config = new Configuration(
        'your-api-key-here',
        true // Use demo environment for safety
    );

    // Create client
    $client = new Client($config);

    // Login with credentials
    echo "=== Capital.com Trading Example ===\n\n";
    echo "Logging in...\n";
    $session = $client->login('your-email@example.com', 'your-password');
    echo "Logged in successfully!\n";
    echo "Account ID: " . $session['currentAccountId'] . "\n\n";

    // Get account preferences
    echo "Getting account preferences...\n";
    $preferences = $client->account()->getPreferences();
    echo "Hedging Mode: " . ($preferences['hedgingMode'] ? 'Enabled' : 'Disabled') . "\n";
    echo "Leverages configured: " . count($preferences['leverages'] ?? []) . "\n\n";

    // Get current positions
    echo "=== Current Positions ===\n";
    $positions = $client->trading()->getPositions();
    
    if (empty($positions['positions'])) {
        echo "No open positions\n\n";
    } else {
        echo "Open positions: " . count($positions['positions']) . "\n";
        foreach ($positions['positions'] as $position) {
            echo "- {$position['epic']}: {$position['direction']} {$position['size']} @ {$position['level']}\n";
            echo "  P&L: {$position['profit']} {$position['currency']}\n";
            
            if (isset($position['stopLevel'])) {
                echo "  Stop Loss: {$position['stopLevel']}\n";
            }
            if (isset($position['limitLevel'])) {
                echo "  Take Profit: {$position['limitLevel']}\n";
            }
        }
        echo "\n";
    }

    // Get working orders
    echo "=== Working Orders ===\n";
    $orders = $client->trading()->getWorkingOrders();
    
    if (empty($orders['workingOrders'])) {
        echo "No working orders\n\n";
    } else {
        echo "Working orders: " . count($orders['workingOrders']) . "\n";
        foreach ($orders['workingOrders'] as $order) {
            echo "- {$order['epic']}: {$order['type']} {$order['direction']} {$order['size']} @ {$order['level']}\n";
            echo "  Time in Force: {$order['timeInForce']}\n";
            
            if (isset($order['goodTillDate'])) {
                echo "  Good Till: {$order['goodTillDate']}\n";
            }
        }
        echo "\n";
    }

    // Example: Open a market position (commented out for safety)
    /*
    echo "=== Opening Market Position ===\n";
    $marketOrder = $client->trading()->openPosition(
        'SILVER',      // Epic
        'BUY',         // Direction
        0.5,           // Size
        'MARKET',      // Order type
        'FILL_OR_KILL', // Time in force
        null,          // Level (not needed for market orders)
        24.00,         // Stop loss
        25.50,         // Take profit
        false          // Guaranteed stop
    );
    
    echo "Position opened with deal reference: " . $marketOrder['dealReference'] . "\n";
    
    // Check deal confirmation
    $confirmation = $client->trading()->getDealConfirmation($marketOrder['dealReference']);
    echo "Deal status: " . $confirmation['dealStatus'] . "\n";
    
    if ($confirmation['dealStatus'] === 'ACCEPTED') {
        echo "Deal ID: " . $confirmation['affectedDeals'][0]['dealId'] . "\n";
        echo "Opened at level: " . $confirmation['level'] . "\n";
    } else {
        echo "Reason: " . $confirmation['reason'] . "\n";
    }
    */

    // Example: Create a limit order (commented out for safety)
    /*
    echo "=== Creating Limit Order ===\n";
    $limitOrder = $client->trading()->createWorkingOrder(
        'GOLD',        // Epic
        'BUY',         // Direction
        0.1,           // Size
        2000.00,       // Level (limit price)
        'LIMIT',       // Order type
        'GOOD_TILL_CANCELLED', // Time in force
        null,          // Good till date
        1990.00,       // Stop loss
        2050.00,       // Take profit
        false          // Guaranteed stop
    );
    
    echo "Limit order created with deal reference: " . $limitOrder['dealReference'] . "\n";
    
    // Check order confirmation
    $orderConfirm = $client->trading()->getDealConfirmation($limitOrder['dealReference']);
    echo "Order status: " . $orderConfirm['dealStatus'] . "\n";
    */

    // Example: Update position (add/modify stop loss and take profit)
    /*
    $dealId = 'YOUR_DEAL_ID_HERE';
    echo "=== Updating Position ===\n";
    
    $updateResult = $client->trading()->updatePosition($dealId, [
        'stopLevel' => 24.50,  // New stop loss
        'limitLevel' => 26.00, // New take profit
    ]);
    
    echo "Position updated with deal reference: " . $updateResult['dealReference'] . "\n";
    */

    // Example: Close position (commented out for safety)
    /*
    $dealId = 'YOUR_DEAL_ID_HERE';
    echo "=== Closing Position ===\n";
    
    $closeResult = $client->trading()->closePosition(
        $dealId,
        'SELL',  // Opposite direction to close
        null     // null = close entire position
    );
    
    echo "Position closed with deal reference: " . $closeResult['dealReference'] . "\n";
    */

    // Get activity history for the last 24 hours
    echo "=== Recent Activity (Last 24 Hours) ===\n";
    $history = $client->trading()->getActivityHistory(
        null,
        null,
        '1d',  // Last period: 1 day
        true   // Detailed information
    );
    
    if (empty($history['activities'])) {
        echo "No recent activity\n\n";
    } else {
        echo "Activities: " . count($history['activities']) . "\n";
        foreach (array_slice($history['activities'], 0, 5) as $activity) {
            echo "- {$activity['date']}: {$activity['type']} {$activity['epic']}\n";
            echo "  Status: {$activity['dealStatus']}\n";
            
            if (isset($activity['level'])) {
                echo "  Level: {$activity['level']}\n";
            }
            if (isset($activity['size'])) {
                echo "  Size: {$activity['size']}\n";
            }
        }
        echo "\n";
    }

    // Get transaction history
    echo "=== Recent Transactions ===\n";
    $transactions = $client->trading()->getTransactionHistory(
        null,
        null,
        '1w'  // Last week
    );
    
    if (empty($transactions['transactions'])) {
        echo "No recent transactions\n\n";
    } else {
        echo "Transactions: " . count($transactions['transactions']) . "\n";
        foreach (array_slice($transactions['transactions'], 0, 5) as $transaction) {
            echo "- {$transaction['date']}: {$transaction['instrumentName']}\n";
            echo "  Type: {$transaction['transactionType']}\n";
            
            if (isset($transaction['cashTransaction'])) {
                echo "  Amount: {$transaction['cashTransaction']} {$transaction['currency']}\n";
            }
        }
    }

    // Logout
    echo "\nLogging out...\n";
    $client->logout();
    echo "Logged out successfully\n";

} catch (CapitalComException $e) {
    echo "\n=== Error ===\n";
    echo "Capital.com API Error: " . $e->getFullMessage() . "\n";
    
    if ($e->isAuthenticationError()) {
        echo "Authentication failed. Please check your API key and credentials.\n";
    } elseif ($e->isRateLimitError()) {
        echo "Rate limit exceeded. Please wait before making more requests.\n";
    } elseif ($e->isValidationError()) {
        echo "Invalid parameters provided. Please check your request data.\n";
    }
    
} catch (Exception $e) {
    echo "\n=== Unexpected Error ===\n";
    echo "Error: " . $e->getMessage() . "\n";
}