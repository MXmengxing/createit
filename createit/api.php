<?php
// api.php
// Demo API with Real Data Fallback - Uses Alpha Vantage free API with mock data fallback

// Alpha Vantage API Key (provided by user)
$ALPHA_VANTAGE_KEY = "CY38YFUBBJR352YA";

// Mock company data (fallback)
$MOCK_DATA = [
    "AAPL" => [
        "name" => "Apple Inc.",
        "price" => 195.50,
        "pe" => 28.5,
        "marketCap" => 3050000000000,
        "revenue" => 383285000000,
        "costOfRevenue" => 214309000000,
        "netIncome" => 96995000000
    ],
    "MSFT" => [
        "name" => "Microsoft Corporation",
        "price" => 420.00,
        "pe" => 35.2,
        "marketCap" => 3140000000000,
        "revenue" => 211915000000,
        "costOfRevenue" => 61344000000,
        "netIncome" => 72361000000
    ],
    "TSLA" => [
        "name" => "Tesla Inc.",
        "price" => 245.00,
        "pe" => 65.3,
        "marketCap" => 780000000000,
        "revenue" => 81462000000,
        "costOfRevenue" => 65489000000,
        "netIncome" => 12586000000
    ],
    "NVDA" => [
        "name" => "NVIDIA Corporation",
        "price" => 875.00,
        "pe" => 61.2,
        "marketCap" => 2150000000000,
        "revenue" => 60922000000,
        "costOfRevenue" => 22103000000,
        "netIncome" => 20758000000
    ],
    "AMZN" => [
        "name" => "Amazon.com Inc.",
        "price" => 185.75,
        "pe" => 52.1,
        "marketCap" => 1920000000000,
        "revenue" => 575506000000,
        "costOfRevenue" => 307699000000,
        "netIncome" => 30425000000
    ],
    "ASML" => [
        "name" => "ASML Holding NV",
        "price" => 900.00,
        "pe" => 45.8,
        "marketCap" => 380000000000,
        "revenue" => 27560000000,
        "costOfRevenue" => 11024000000,
        "netIncome" => 5180000000
    ]
];

function fetchRealData($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0'
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $json = @file_get_contents($url, false, $context);
    return json_decode($json, true);
}

function generateHistoricalPrices($symbol, $basePrice) {
    $prices = [];
    $currentPrice = $basePrice;
    $date = new DateTime();
    $date->modify('-100 days');
    
    for ($i = 0; $i < 100; $i++) {
        // Random walk for realistic price movement
        $change = (rand(-2, 2) / 100) * $currentPrice;
        $currentPrice += $change;
        
        $prices[] = [
            "date" => $date->format('Y-m-d'),
            "close" => round($currentPrice, 2)
        ];
        
        $date->modify('+1 day');
    }
    
    return array_reverse($prices);
}

function api(string $endpoint) {
    global $MOCK_DATA, $ALPHA_VANTAGE_KEY;
    
    // Parse the endpoint
    if (strpos($endpoint, "income-statement") !== false) {
        // Extract symbol from endpoint
        preg_match('/symbol=([A-Z]+)/', $endpoint, $matches);
        $symbol = $matches[1] ?? "AAPL";
        $symbol = strtoupper($symbol);
        
        if (!isset($MOCK_DATA[$symbol])) {
            return ["error" => true, "msg" => "Symbol not found"];
        }
        
        $data = $MOCK_DATA[$symbol];
        return [[
            "symbol" => $symbol,
            "revenue" => $data["revenue"],
            "costOfRevenue" => $data["costOfRevenue"],
            "netIncome" => $data["netIncome"]
        ]];
    }
    
    if (strpos($endpoint, "historical-price-full") !== false) {
        // Extract symbol from endpoint
        preg_match('/historical-price-full\/([A-Z]+)/', $endpoint, $matches);
        $symbol = $matches[1] ?? "AAPL";
        $symbol = strtoupper($symbol);
        
        // Try real API first with INTRADAY data (5min interval for real-time)
        $realUrl = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=$symbol&interval=60min&outputsize=full&apikey=$ALPHA_VANTAGE_KEY";
        $realData = fetchRealData($realUrl);
        
        if ($realData && isset($realData['Time Series (60min)'])) {
            $timeSeries = $realData['Time Series (60min)'];
            $prices = [];
            $dates = [];
            $count = 0;
            
            foreach ($timeSeries as $date => $data) {
                if ($count >= 100) break;
                $prices[] = (float)$data['4. close'];
                $dates[] = $date;
                $count++;
            }
            
            return [
                "symbol" => $symbol,
                "historical" => array_map(function($d, $p) {
                    return ["date" => $d, "close" => $p];
                }, $dates, $prices)
            ];
        }
        
        // Fallback to daily data
        $realUrl = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$symbol&outputsize=full&apikey=$ALPHA_VANTAGE_KEY";
        $realData = fetchRealData($realUrl);
        
        if ($realData && isset($realData['Time Series (Daily)'])) {
            $timeSeries = $realData['Time Series (Daily)'];
            $prices = [];
            $dates = [];
            $count = 0;
            
            foreach ($timeSeries as $date => $data) {
                if ($count >= 100) break;
                $prices[] = (float)$data['4. close'];
                $dates[] = $date;
                $count++;
            }
            
            return [
                "symbol" => $symbol,
                "historical" => array_map(function($d, $p) {
                    return ["date" => $d, "close" => $p];
                }, $dates, $prices)
            ];
        }
        
        // Fallback to mock data
        if (!isset($MOCK_DATA[$symbol])) {
            return ["error" => true, "msg" => "Symbol not found"];
        }
        
        $basePrice = $MOCK_DATA[$symbol]["price"];
        return [
            "symbol" => $symbol,
            "historical" => generateHistoricalPrices($symbol, $basePrice)
        ];
    }
    
    if (strpos($endpoint, "quote") !== false) {
        // Extract symbol from endpoint
        preg_match('/quote\/([A-Z]+)/', $endpoint, $matches);
        $symbol = $matches[1] ?? "AAPL";
        $symbol = strtoupper($symbol);
        
        // Try real API first (Alpha Vantage)
        $realUrl = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$symbol&apikey=$ALPHA_VANTAGE_KEY";
        $realData = fetchRealData($realUrl);
        
        if ($realData && isset($realData['Global Quote']) && isset($realData['Global Quote']['05. price'])) {
            $quote = $realData['Global Quote'];
            return [[
                "symbol" => $symbol,
                "price" => (float)$quote['05. price'],
                "pe" => (float)($quote['10. pe ratio'] ?? 0),
                "marketCap" => (float)($quote['09. marketCap'] ?? 0)
            ]];
        }
        
        // Fallback to mock data
        if (!isset($MOCK_DATA[$symbol])) {
            return [["error" => true, "msg" => "Symbol not found"]];
        }
        
        $data = $MOCK_DATA[$symbol];
        return [[
            "symbol" => $symbol,
            "price" => $data["price"],
            "pe" => $data["pe"],
            "marketCap" => $data["marketCap"]
        ]];
    }
    
    return ["error" => true, "msg" => "Unknown endpoint"];
}
