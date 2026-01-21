<?php
// api.php
// Demo API - Returns mock financial data for demonstration

// Mock company data
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
    global $MOCK_DATA;
    
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
