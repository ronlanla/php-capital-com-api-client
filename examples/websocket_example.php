<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CapitalCom\Client;
use CapitalCom\Configuration;
use CapitalCom\Exception\CapitalComException;

/**
 * WebSocket Example for Capital.com API
 * 
 * Note: This example demonstrates the WebSocket connection setup and message formats.
 * You'll need to install a WebSocket client library like Ratchet/Pawl to run this:
 * 
 * composer require ratchet/pawl
 */

// Check if WebSocket library is available
if (!class_exists('\Ratchet\Client\Connector')) {
    echo "WebSocket library not installed. Please run:\n";
    echo "composer require ratchet/pawl\n\n";
    echo "Below is the example code that would run with the library installed:\n\n";
    
    // Show the example code structure
    showExampleCode();
    exit(1);
}

// If Ratchet/Pawl is installed, run the actual WebSocket example
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;

try {
    // Initialize configuration
    $config = new Configuration(
        'your-api-key-here',
        true // Use demo environment
    );

    // Create REST API client for authentication
    $client = new Client($config);

    echo "=== Capital.com WebSocket Example ===\n\n";
    echo "Authenticating via REST API...\n";
    
    // Login to get session tokens
    $session = $client->login('your-email@example.com', 'your-password');
    echo "Logged in successfully!\n";
    
    // Get authentication tokens for WebSocket
    $cstToken = $client->getCstToken();
    $securityToken = $client->getSecurityToken();
    $wsUrl = $client->getWebSocketUrl();
    
    if (!$cstToken || !$securityToken) {
        throw new Exception("Failed to get authentication tokens");
    }
    
    echo "CST Token: " . substr($cstToken, 0, 10) . "...\n";
    echo "Security Token: " . substr($securityToken, 0, 10) . "...\n";
    echo "WebSocket URL: {$wsUrl}\n\n";

    // Create WebSocket connector
    $connector = new Connector(Loop::get());
    
    echo "Connecting to WebSocket...\n";
    
    $connector($wsUrl)->then(function(WebSocket $conn) use ($cstToken, $securityToken) {
        echo "WebSocket connected!\n\n";
        
        // List of instruments to subscribe to
        $instruments = ['GOLD', 'SILVER', 'EUR_USD', 'GBP_USD', 'OIL_CRUDE'];
        
        // Subscribe to market data
        echo "Subscribing to market data for: " . implode(', ', $instruments) . "\n";
        
        $subscribeMessage = json_encode([
            'destination' => 'marketData.subscribe',
            'correlationId' => '1',
            'cst' => $cstToken,
            'securityToken' => $securityToken,
            'payload' => [
                'epics' => $instruments
            ]
        ]);
        
        $conn->send($subscribeMessage);
        
        // Subscribe to OHLC data
        echo "Subscribing to OHLC data...\n";
        
        $ohlcMessage = json_encode([
            'destination' => 'OHLCMarketData.subscribe',
            'correlationId' => '2',
            'cst' => $cstToken,
            'securityToken' => $securityToken,
            'payload' => [
                'epics' => ['GOLD', 'SILVER'],
                'resolutions' => ['MINUTE', 'MINUTE_5'],
                'type' => 'classic'
            ]
        ]);
        
        $conn->send($ohlcMessage);
        
        // Handle incoming messages
        $conn->on('message', function(MessageInterface $msg) {
            $data = json_decode($msg->getPayload(), true);
            
            if (!$data) {
                echo "Received invalid message\n";
                return;
            }
            
            // Handle different message types
            switch ($data['destination'] ?? '') {
                case 'marketData.subscribe':
                    handleSubscriptionConfirmation($data);
                    break;
                    
                case 'OHLCMarketData.subscribe':
                    handleOHLCSubscriptionConfirmation($data);
                    break;
                    
                case 'quote':
                    handleQuoteUpdate($data);
                    break;
                    
                case 'ohlc.event':
                    handleOHLCUpdate($data);
                    break;
                    
                case 'ping':
                    echo "Received ping response\n";
                    break;
                    
                default:
                    if (isset($data['status']) && $data['status'] === 'OK') {
                        echo "Received OK status for correlation ID: " . ($data['correlationId'] ?? 'unknown') . "\n";
                    } else {
                        echo "Unknown message type: " . json_encode($data) . "\n";
                    }
            }
        });
        
        // Send ping every 5 minutes to keep connection alive
        Loop::addPeriodicTimer(300, function() use ($conn, $cstToken, $securityToken) {
            echo "\nSending ping to keep connection alive...\n";
            
            $pingMessage = json_encode([
                'destination' => 'ping',
                'correlationId' => uniqid(),
                'cst' => $cstToken,
                'securityToken' => $securityToken
            ]);
            
            $conn->send($pingMessage);
        });
        
        // Handle connection close
        $conn->on('close', function($code = null, $reason = null) {
            echo "\nWebSocket connection closed";
            if ($code) {
                echo " (Code: {$code}";
                if ($reason) {
                    echo ", Reason: {$reason}";
                }
                echo ")";
            }
            echo "\n";
            Loop::stop();
        });
        
        // Handle errors
        $conn->on('error', function(\Exception $e) {
            echo "WebSocket error: " . $e->getMessage() . "\n";
        });
        
        // Example: Unsubscribe after 30 seconds (commented out)
        /*
        Loop::addTimer(30, function() use ($conn, $cstToken, $securityToken) {
            echo "\nUnsubscribing from GOLD...\n";
            
            $unsubscribeMessage = json_encode([
                'destination' => 'marketData.unsubscribe',
                'correlationId' => '99',
                'cst' => $cstToken,
                'securityToken' => $securityToken,
                'payload' => [
                    'epics' => ['GOLD']
                ]
            ]);
            
            $conn->send($unsubscribeMessage);
        });
        */
        
        // Stop after 60 seconds for demo purposes
        Loop::addTimer(60, function() use ($conn) {
            echo "\nDemo time limit reached. Closing connection...\n";
            $conn->close();
        });
        
    }, function(\Exception $e) {
        echo "Could not connect to WebSocket: " . $e->getMessage() . "\n";
        Loop::stop();
    });
    
    // Run the event loop
    echo "\nStarting event loop (will run for 60 seconds)...\n";
    echo "Press Ctrl+C to stop earlier.\n\n";
    Loop::run();
    
    // Logout from REST API
    echo "\nLogging out from REST API...\n";
    $client->logout();
    echo "Logged out successfully\n";
    
} catch (CapitalComException $e) {
    echo "\n=== Error ===\n";
    echo "Capital.com API Error: " . $e->getFullMessage() . "\n";
} catch (Exception $e) {
    echo "\n=== Unexpected Error ===\n";
    echo "Error: " . $e->getMessage() . "\n";
}

/**
 * Handle subscription confirmation
 */
function handleSubscriptionConfirmation($data) {
    echo "\n=== Subscription Confirmation ===\n";
    if (isset($data['payload']['subscriptions'])) {
        foreach ($data['payload']['subscriptions'] as $epic => $status) {
            echo "  {$epic}: {$status}\n";
        }
    }
}

/**
 * Handle OHLC subscription confirmation
 */
function handleOHLCSubscriptionConfirmation($data) {
    echo "\n=== OHLC Subscription Confirmation ===\n";
    if (isset($data['payload']['subscriptions'])) {
        foreach ($data['payload']['subscriptions'] as $subscription => $status) {
            echo "  {$subscription}: {$status}\n";
        }
    }
}

/**
 * Handle real-time quote updates
 */
function handleQuoteUpdate($data) {
    $payload = $data['payload'] ?? [];
    
    $timestamp = date('H:i:s', $payload['timestamp'] / 1000);
    
    echo "[{$timestamp}] {$payload['epic']}: ";
    echo "Bid: {$payload['bid']} ";
    echo "Ask: {$payload['ofr']} ";
    echo "Spread: " . number_format($payload['ofr'] - $payload['bid'], 5);
    echo "\n";
}

/**
 * Handle OHLC candle updates
 */
function handleOHLCUpdate($data) {
    $payload = $data['payload'] ?? [];
    
    $timestamp = date('Y-m-d H:i:s', $payload['t'] / 1000);
    
    echo "[OHLC] {$payload['epic']} {$payload['resolution']} at {$timestamp}: ";
    echo "O:{$payload['o']} H:{$payload['h']} L:{$payload['l']} C:{$payload['c']}";
    echo "\n";
}

/**
 * Show example code structure when WebSocket library is not installed
 */
function showExampleCode() {
    $code = <<<'CODE'
// Example WebSocket connection and subscription code:

use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\Loop;

// 1. Authenticate via REST API to get tokens
$client = new Client($config);
$session = $client->login('email', 'password');
$cstToken = $client->getCstToken();
$securityToken = $client->getSecurityToken();

// 2. Connect to WebSocket
$connector = new Connector(Loop::get());
$wsUrl = $client->getWebSocketUrl();

$connector($wsUrl)->then(function(WebSocket $conn) use ($cstToken, $securityToken) {
    
    // 3. Subscribe to market data
    $subscribeMessage = json_encode([
        'destination' => 'marketData.subscribe',
        'correlationId' => '1',
        'cst' => $cstToken,
        'securityToken' => $securityToken,
        'payload' => [
            'epics' => ['GOLD', 'SILVER', 'EUR_USD']
        ]
    ]);
    $conn->send($subscribeMessage);
    
    // 4. Handle incoming price updates
    $conn->on('message', function($msg) {
        $data = json_decode($msg->getPayload(), true);
        
        if ($data['destination'] === 'quote') {
            // Real-time price update
            $payload = $data['payload'];
            echo "{$payload['epic']}: Bid={$payload['bid']} Ask={$payload['ofr']}\n";
        }
    });
    
    // 5. Keep connection alive with periodic ping
    Loop::addPeriodicTimer(300, function() use ($conn, $cstToken, $securityToken) {
        $conn->send(json_encode([
            'destination' => 'ping',
            'correlationId' => uniqid(),
            'cst' => $cstToken,
            'securityToken' => $securityToken
        ]));
    });
});

// 6. Run the event loop
Loop::run();
CODE;
    
    echo $code;
}