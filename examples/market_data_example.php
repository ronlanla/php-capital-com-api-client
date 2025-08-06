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
    echo "=== Capital.com Market Data Example ===\n\n";
    echo "Logging in...\n";
    $session = $client->login('your-email@example.com', 'your-password');
    echo "Logged in successfully!\n\n";

    // Get server time
    echo "=== Server Information ===\n";
    $serverTime = $client->market()->getTime();
    echo "Server Time: " . $serverTime['serverTime'] . "\n\n";

    // Search for specific markets
    echo "=== Market Search ===\n";
    $searchTerms = ['Bitcoin', 'Gold', 'EUR/USD', 'Apple'];
    
    foreach ($searchTerms as $term) {
        echo "Searching for: {$term}\n";
        $results = $client->market()->searchMarkets($term);
        
        if (!empty($results['markets'])) {
            $market = $results['markets'][0];
            echo "  Found: {$market['instrumentName']} ({$market['epic']})\n";
            echo "  Type: {$market['instrumentType']}\n";
            echo "  Status: {$market['marketStatus']}\n";
            
            if (isset($market['snapshot'])) {
                $snapshot = $market['snapshot'];
                echo "  Bid: {$snapshot['bid']} | Ask: {$snapshot['offer']}\n";
                echo "  Change: {$snapshot['netChange']} ({$snapshot['percentageChange']}%)\n";
            }
        } else {
            echo "  No markets found\n";
        }
        echo "\n";
    }

    // Get specific market details
    echo "=== Market Details ===\n";
    $epicToCheck = 'SILVER';
    
    try {
        $marketDetail = $client->market()->getMarket($epicToCheck);
        echo "Market: {$marketDetail['instrument']['name']}\n";
        echo "Epic: {$marketDetail['instrument']['epic']}\n";
        echo "Type: {$marketDetail['instrument']['type']}\n";
        
        $snapshot = $marketDetail['snapshot'];
        echo "\nCurrent Snapshot:\n";
        echo "  Status: {$snapshot['marketStatus']}\n";
        echo "  Bid: {$snapshot['bid']}\n";
        echo "  Offer: {$snapshot['offer']}\n";
        echo "  Spread: " . ($snapshot['offer'] - $snapshot['bid']) . "\n";
        echo "  High: {$snapshot['high']}\n";
        echo "  Low: {$snapshot['low']}\n";
        echo "  Net Change: {$snapshot['netChange']}\n";
        echo "  % Change: {$snapshot['percentageChange']}%\n";
        echo "  Update Time: {$snapshot['updateTime']}\n";
        
        if (isset($marketDetail['dealingRules'])) {
            $rules = $marketDetail['dealingRules'];
            echo "\nDealing Rules:\n";
            echo "  Min Size: {$rules['minDealSize']['value']}\n";
            echo "  Max Size: {$rules['maxDealSize']['value']}\n";
            echo "  Min Stop/Limit Distance: {$rules['minControlledRiskStopDistance']['value']}\n";
        }
    } catch (CapitalComException $e) {
        echo "Could not get details for {$epicToCheck}: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Get historical prices
    echo "=== Historical Prices ===\n";
    $priceEpic = 'GOLD';
    $resolutions = ['MINUTE', 'MINUTE_5', 'HOUR', 'DAY'];
    
    foreach ($resolutions as $resolution) {
        echo "Getting {$resolution} prices for {$priceEpic}...\n";
        
        try {
            $prices = $client->market()->getPrices(
                $priceEpic,
                $resolution,
                10  // Get last 10 price points
            );
            
            if (!empty($prices['prices'])) {
                echo "  Retrieved " . count($prices['prices']) . " price points\n";
                
                // Show first and last price
                $firstPrice = $prices['prices'][0];
                $lastPrice = $prices['prices'][count($prices['prices']) - 1];
                
                echo "  First: {$firstPrice['snapshotTime']} - ";
                echo "O:{$firstPrice['openPrice']['bid']} ";
                echo "H:{$firstPrice['highPrice']['bid']} ";
                echo "L:{$firstPrice['lowPrice']['bid']} ";
                echo "C:{$firstPrice['closePrice']['bid']}\n";
                
                echo "  Last:  {$lastPrice['snapshotTime']} - ";
                echo "O:{$lastPrice['openPrice']['bid']} ";
                echo "H:{$lastPrice['highPrice']['bid']} ";
                echo "L:{$lastPrice['lowPrice']['bid']} ";
                echo "C:{$lastPrice['closePrice']['bid']}\n";
            }
        } catch (CapitalComException $e) {
            echo "  Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

    // Get market navigation structure
    echo "=== Market Navigation ===\n";
    $navigation = $client->market()->getMarketNavigation();
    
    if (!empty($navigation['nodes'])) {
        echo "Top-level market categories:\n";
        foreach (array_slice($navigation['nodes'], 0, 10) as $node) {
            echo "  - {$node['name']} (ID: {$node['id']})\n";
        }
        
        // Get markets in first category
        if (!empty($navigation['nodes'][0])) {
            $firstNode = $navigation['nodes'][0];
            echo "\nGetting markets in category: {$firstNode['name']}\n";
            
            try {
                $nodeMarkets = $client->market()->getMarketNavigationNode($firstNode['id']);
                
                if (!empty($nodeMarkets['markets'])) {
                    echo "Markets in this category: " . count($nodeMarkets['markets']) . "\n";
                    foreach (array_slice($nodeMarkets['markets'], 0, 5) as $market) {
                        echo "  - {$market['instrumentName']} ({$market['epic']})\n";
                    }
                }
            } catch (CapitalComException $e) {
                echo "Could not get markets for node: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "\n";

    // Get client sentiment
    echo "=== Client Sentiment ===\n";
    $sentimentEpics = ['GOLD', 'SILVER', 'EUR_USD', 'GBP_USD'];
    
    foreach ($sentimentEpics as $epic) {
        try {
            $sentiment = $client->market()->getMarketClientSentiment($epic);
            
            echo "{$epic}:\n";
            echo "  Long: {$sentiment['longPositionPercentage']}%\n";
            echo "  Short: {$sentiment['shortPositionPercentage']}%\n";
            
            // Interpret sentiment
            $longPercent = floatval($sentiment['longPositionPercentage']);
            if ($longPercent > 60) {
                echo "  Sentiment: Strongly Bullish\n";
            } elseif ($longPercent > 50) {
                echo "  Sentiment: Slightly Bullish\n";
            } elseif ($longPercent < 40) {
                echo "  Sentiment: Strongly Bearish\n";
            } elseif ($longPercent < 50) {
                echo "  Sentiment: Slightly Bearish\n";
            } else {
                echo "  Sentiment: Neutral\n";
            }
        } catch (CapitalComException $e) {
            echo "{$epic}: Unable to get sentiment - " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

    // Get multiple markets at once
    echo "=== Batch Market Data ===\n";
    $epicsToGet = ['GOLD', 'SILVER', 'OIL_CRUDE', 'NATURAL_GAS'];
    $batchMarkets = $client->market()->searchMarkets(null, $epicsToGet);
    
    if (!empty($batchMarkets['markets'])) {
        echo "Retrieved " . count($batchMarkets['markets']) . " markets:\n";
        
        foreach ($batchMarkets['markets'] as $market) {
            echo "\n{$market['instrumentName']} ({$market['epic']})\n";
            
            if (isset($market['snapshot'])) {
                $snapshot = $market['snapshot'];
                echo "  Bid: {$snapshot['bid']} | Ask: {$snapshot['offer']}\n";
                echo "  Day High: {$snapshot['high']} | Day Low: {$snapshot['low']}\n";
                echo "  Change: {$snapshot['netChange']} ({$snapshot['percentageChange']}%)\n";
            }
        }
    }

    // Calculate some market statistics
    echo "\n=== Market Analysis ===\n";
    $analysisEpic = 'EUR_USD';
    
    try {
        // Get daily prices for analysis
        $dailyPrices = $client->market()->getPrices(
            $analysisEpic,
            'DAY',
            30  // Last 30 days
        );
        
        if (!empty($dailyPrices['prices'])) {
            $closes = array_map(function($price) {
                return $price['closePrice']['bid'];
            }, $dailyPrices['prices']);
            
            // Calculate simple statistics
            $avg = array_sum($closes) / count($closes);
            $min = min($closes);
            $max = max($closes);
            $latest = end($closes);
            
            echo "Analysis for {$analysisEpic} (Last 30 days):\n";
            echo "  Current Price: {$latest}\n";
            echo "  Average: " . number_format($avg, 5) . "\n";
            echo "  Min: {$min}\n";
            echo "  Max: {$max}\n";
            echo "  Range: " . ($max - $min) . "\n";
            
            // Simple trend analysis
            $firstHalf = array_slice($closes, 0, count($closes) / 2);
            $secondHalf = array_slice($closes, count($closes) / 2);
            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);
            
            if ($secondAvg > $firstAvg * 1.01) {
                echo "  Trend: Upward\n";
            } elseif ($secondAvg < $firstAvg * 0.99) {
                echo "  Trend: Downward\n";
            } else {
                echo "  Trend: Sideways\n";
            }
        }
    } catch (CapitalComException $e) {
        echo "Could not analyze {$analysisEpic}: " . $e->getMessage() . "\n";
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
    }
    
} catch (Exception $e) {
    echo "\n=== Unexpected Error ===\n";
    echo "Error: " . $e->getMessage() . "\n";
}