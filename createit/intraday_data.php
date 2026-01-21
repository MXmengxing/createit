<?php
// intraday_data.php
// Fetches real intraday data from Alpha Vantage API with caching
// Caches data to spare API calls (limited calls available)

require_once 'api.php';

// Cache file path
$cacheDir = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/intraday_cache.json';

// Create cache directory if it doesn't exist
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Check if cache exists and is less than 1 hour old
$useCache = false;
if (file_exists($cacheFile)) {
    $cacheAge = time() - filemtime($cacheFile);
    if ($cacheAge < 3600) { // 1 hour cache
        $useCache = true;
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cachedData, JSON_PRETTY_PRINT);
        exit;
    }
}

// 20 companies to fetch
$symbols = ['AAPL', 'MSFT', 'GOOGL', 'AMZN', 'NVDA', 'TSLA', 'META', 'IBM', 
            'INTC', 'AMD', 'ASML', 'JPM', 'V', 'JNJ', 'KO', 'PG', 
            'NFLX', 'DIS', 'CSCO', 'BA'];

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => [],
    'symbols' => [],
    'source' => 'alpha-vantage'
];

// Fetch data for each symbol from Alpha Vantage
foreach ($symbols as $symbol) {
    $url = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol={$symbol}&interval=5min&apikey=" . urlencode($ALPHA_VANTAGE_KEY);
    
    $context = stream_context_create(['http' => ['timeout' => 10]]);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        error_log("Failed to fetch data for $symbol from Alpha Vantage");
        continue;
    }
    
    $data = json_decode($response, true);
    
    // Check for rate limit or error
    if (isset($data['Error Message']) || isset($data['Note'])) {
        error_log("Alpha Vantage API limit or error for $symbol: " . ($data['Error Message'] ?? $data['Note']));
        continue;
    }
    
    // Extract time series data
    if (!isset($data['Time Series (5min)'])) {
        error_log("No intraday data for $symbol");
        continue;
    }
    
    $timeSeries = $data['Time Series (5min)'];
    $historical = [];
    $currentPrice = 0;
    
    foreach ($timeSeries as $timestamp => $ohlcv) {
        $historical[] = [
            'date' => $timestamp,
            'open' => (float)$ohlcv['1. open'],
            'high' => (float)$ohlcv['2. high'],
            'low' => (float)$ohlcv['3. low'],
            'close' => (float)$ohlcv['4. close'],
            'volume' => (int)$ohlcv['5. volume']
        ];
        
        if ($currentPrice === 0) {
            $currentPrice = (float)$ohlcv['4. close'];
        }
    }
    
    // Reverse to get chronological order (oldest to newest)
    $historical = array_reverse($historical);
    
    $result['data'][$symbol] = [
        'symbol' => $symbol,
        'price' => $currentPrice,
        'historical' => $historical,
        'lastRefreshed' => $data['Meta Data']['3. last refreshed'] ?? date('Y-m-d H:i:s'),
        'interval' => '5min'
    ];
    
    $result['symbols'][] = [
        'symbol' => $symbol,
        'price' => $currentPrice
    ];
    
    // Rate limit: sleep between API calls (5 calls per minute = 12 second wait)
    sleep(13);
}

// Save to cache for next hour
file_put_contents($cacheFile, json_encode($result, JSON_PRETTY_PRINT));

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT);
?>
