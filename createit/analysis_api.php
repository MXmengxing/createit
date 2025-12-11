<?php
// analysis_api.php
// 返回指定股票最近一年的收入/成本/净利润，给 analyse.php 用。

header("Content-Type: application/json; charset=utf-8");

include "api.php";

$symbol = $_GET["symbol"] ?? "AAPL";
$symbol = strtoupper($symbol);

// 调用 FMP stable income-statement 接口
$data = api("income-statement?symbol=$symbol&limit=1");

// 如果 api() 自己返回了 error
if (isset($data["error"]) && $data["error"] === true) {
    echo json_encode([
        "error" => true,
        "msg"   => $data["msg"] ?? "Geen API data"
    ]);
    exit;
}

// 正常情况：应该是一个数组，索引 0 是最近一年
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
