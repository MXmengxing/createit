<?php
// api.php
// Kleine helper om de FinancialModelingPrep "stable" API aan te spreken.

$API_KEY = "8PIWLjeZ2vIgSPkLYCiGd2y9cXa4SVea"; // ← 在这里填你自己的 FMP API key

function api(string $endpoint) {
    global $API_KEY;

    // 使用 stable 路径（官方文档推荐）
    $url = "https://financialmodelingprep.com/stable/" . $endpoint;

    // 拼接 apikey 参数
    if (strpos($url, '?') !== false) {
        $url .= "&apikey=" . urlencode($API_KEY);
    } else {
        $url .= "?apikey=" . urlencode($API_KEY);
    }

    $json = @file_get_contents($url);

    if ($json === false) {
        return [
            "error" => true,
            "msg"   => "API unreachable: $url"
        ];
    }

    $data = json_decode($json, true);

    if ($data === null) {
        return [
            "error" => true,
            "msg"   => "Invalid JSON from API: $url"
        ];
    }

    return $data;
}
