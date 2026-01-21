<?php
// intraday_data.php
// Batch load all company intraday data (5-minute intervals) from Alpha Vantage
// Returns all 20 companies' data in one API call batch

require_once 'api.php';

// List of 20 companies to load
$symbols = ['AAPL', 'MSFT', 'GOOGL', 'AMZN', 'NVDA', 'TSLA', 'META', 'IBM', 'INTC', 'AMD', 
            'ASML', 'JPM', 'V', 'JNJ', 'KO', 'PG', 'NFLX', 'DIS', 'CSCO', 'BA'];

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => [],
    'symbols' => [],
    'status' => 'success'
];

// Load intraday data for each symbol
foreach ($symbols as $symbol) {
    try {
        // Call Alpha Vantage TIME_SERIES_INTRADAY endpoint
        $endpoint = "function=TIME_SERIES_INTRADAY&symbol=$symbol&interval=5min&outputsize=compact&apikey={$ALPHA_VANTAGE_KEY}";
        $url = "https://www.alphavantage.co/query?" . $endpoint;
        
        // Fetch from API with timeout
        $context = stream_context_create([
            'http' => ['timeout' => 10]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            // Fallback to mock data if API fails
            $result['data'][$symbol] = generateMockIntradayData($symbol);
            continue;
        }
        
        $apiData = json_decode($response, true);
        
        // Check for API errors or rate limits
        if (isset($apiData['Note']) || isset($apiData['Error Message'])) {
            // Rate limited or error - use mock data
            $result['data'][$symbol] = generateMockIntradayData($symbol);
            continue;
        }
        
        // Extract intraday time series
        $timeSeries = null;
        if (isset($apiData['Time Series (5min)'])) {
            $timeSeries = $apiData['Time Series (5min)'];
        } else {
            // No data returned, use mock
            $result['data'][$symbol] = generateMockIntradayData($symbol);
            continue;
        }
        
        // Process the time series data
        $historical = [];
        $latestPrice = null;
        $latestTime = null;
        
        foreach ($timeSeries as $time => $candle) {
            if ($latestPrice === null) {
                $latestPrice = floatval($candle['4. close']);
                $latestTime = $time;
            }
            
            $historical[] = [
                'date' => $time,
                'open' => floatval($candle['1. open']),
                'high' => floatval($candle['2. high']),
                'low' => floatval($candle['3. low']),
                'close' => floatval($candle['4. close']),
                'volume' => intval($candle['5. volume'])
            ];
        }
        
        // Reverse to get oldest-to-newest order
        $historical = array_reverse($historical);
        
        // Get the LAST (newest) price from historical data
        $currentPrice = end($historical)['close'];
        
        $result['data'][$symbol] = [
            'symbol' => $symbol,
            'price' => $currentPrice,
            'historical' => $historical,
            'lastRefreshed' => $latestTime,
            'interval' => '5min'
        ];
        
    } catch (Exception $e) {
        // Fallback for any errors
        $result['data'][$symbol] = generateMockIntradayData($symbol);
    }
}

// Build symbols array with current prices
foreach ($result['data'] as $symbol => $data) {
    $result['symbols'][] = [
        'symbol' => $symbol,
        'price' => $data['price']
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($result);

// Generate mock intraday data as fallback
function generateMockIntradayData($symbol) {
    // Base prices for each symbol
    $basePrices = [
        'AAPL' => 195.50, 'MSFT' => 420.00, 'GOOGL' => 180.75, 'AMZN' => 185.75,
        'NVDA' => 875.00, 'TSLA' => 245.00, 'META' => 520.00, 'IBM' => 195.00,
        'INTC' => 46.25, 'AMD' => 210.00, 'ASML' => 850.00, 'JPM' => 205.00,
        'V' => 285.00, 'JNJ' => 160.00, 'KO' => 65.75, 'PG' => 165.00,
        'NFLX' => 275.00, 'DIS' => 92.50, 'CSCO' => 52.00, 'BA' => 185.00
    ];
    
    $basePrice = $basePrices[$symbol] ?? 200.00;
    $historical = [];
    
    // Generate 96 data points (one trading day at 5-min intervals)
    $now = new DateTime('now', new DateTimeZone('America/New_York'));
    $now->setTime(16, 0); // 4 PM closing time
    
    for ($i = 0; $i < 96; $i++) {
        $variance = (rand(-200, 200) / 100) * 0.02; // 2% max variance
        $price = $basePrice * (1 + $variance);
        
        $historical[] = [
            'date' => $now->format('Y-m-d H:i:s'),
            'open' => round($price * (1 + rand(-50, 50) / 10000), 2),
            'high' => round($price * 1.002, 2),
            'low' => round($price * 0.998, 2),
            'close' => round($price, 2),
            'volume' => rand(500000, 5000000)
        ];
        
        $now->sub(new DateInterval('PT5M'));
    }
    
    return [
        'symbol' => $symbol,
        'price' => $basePrice,
        'historical' => array_reverse($historical),
        'lastRefreshed' => date('Y-m-d H:i:s'),
        'interval' => '5min'
    ];
}
?>
