<?php
// symbol_search.php
// Returns list of available symbols

header("Content-Type: application/json; charset=utf-8");

$popular_symbols = [
    ["symbol" => "AAPL", "name" => "Apple Inc."],
    ["symbol" => "MSFT", "name" => "Microsoft Corporation"],
    ["symbol" => "GOOGL", "name" => "Alphabet Inc."],
    ["symbol" => "AMZN", "name" => "Amazon.com Inc."],
    ["symbol" => "NVDA", "name" => "NVIDIA Corporation"],
    ["symbol" => "TSLA", "name" => "Tesla Inc."],
    ["symbol" => "META", "name" => "Meta Platforms Inc."],
    ["symbol" => "IBM", "name" => "IBM"],
    ["symbol" => "INTC", "name" => "Intel Corporation"],
    ["symbol" => "AMD", "name" => "Advanced Micro Devices"],
    ["symbol" => "ASML", "name" => "ASML Holding NV"],
    ["symbol" => "JPM", "name" => "JPMorgan Chase & Co."],
    ["symbol" => "V", "name" => "Visa Inc."],
    ["symbol" => "JNJ", "name" => "Johnson & Johnson"],
    ["symbol" => "KO", "name" => "The Coca-Cola Company"],
    ["symbol" => "PG", "name" => "Procter & Gamble"],
    ["symbol" => "NFLX", "name" => "Netflix Inc."],
    ["symbol" => "DIS", "name" => "The Walt Disney Company"],
    ["symbol" => "CSCO", "name" => "Cisco Systems Inc."],
    ["symbol" => "BA", "name" => "The Boeing Company"]
];

$search = $_GET["search"] ?? "";
$search = strtoupper($search);

if ($search) {
    $filtered = array_filter($popular_symbols, function($item) use ($search) {
        return strpos($item["symbol"], $search) === 0 || 
               strpos(strtoupper($item["name"]), $search) !== false;
    });
    echo json_encode(array_values($filtered));
} else {
    echo json_encode($popular_symbols);
}
?>
