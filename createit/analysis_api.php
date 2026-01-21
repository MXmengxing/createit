<?php
// analysis_api.php

header("Content-Type: application/json; charset=utf-8");

include "api.php";

$symbol = $_GET["symbol"] ?? "AAPL";
$symbol = strtoupper($symbol);

$data = api("income-statement?symbol=$symbol&limit=1");

if (isset($data["error"]) && $data["error"] === true) {
    echo json_encode([
        "error" => true,
        "msg"   => $data["msg"] ?? "Geen API data"
    ]);
    exit;
}

if (!isset($data[0]) || !is_array($data[0])) {
    echo json_encode([
        "error" => true,
        "msg"   => "Geen resultaten voor symbool: $symbol"
    ]);
    exit;
}

$y = $data[0];

echo json_encode([
    "error"   => false,
    "symbol"  => $symbol,
    "revenue" => $y["revenue"]       ?? 0,
    "cost"    => $y["costOfRevenue"] ?? 0,
    "net"     => $y["netIncome"]     ?? 0
]);
