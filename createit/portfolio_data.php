<?php
include "api.php";

$portfolio = [
    ["symbol" => "AAPL", "shares" => 10],
    ["symbol" => "TSLA", "shares" => 5],
    ["symbol" => "ASML", "shares" => 8]
];

$total = 0;
$positions = [];

foreach ($portfolio as $p) {
    $quote = api("quote/" . $p["symbol"]);

    if (!isset($quote[0])) continue;

    $price = $quote[0]["price"];
    $value = $price * $p["shares"];

    $total += $value;

    $positions[] = [
        "symbol" => $p["symbol"],
        "price"  => $price,
        "shares" => $p["shares"],
        "value"  => $value,
        "pe"     => $quote[0]["pe"]
    ];
}

echo json_encode([
    "total" => $total,
    "positions" => $positions
]);
?>
