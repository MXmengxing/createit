<?php
include 'api.php';

$companies = explode(",", $_GET["list"]);
$response = [];

foreach ($companies as $symbol) {
    $quote = api("quote/$symbol")[0];

    $response[] = [
        "symbol" => $symbol,
        "price" => $quote["price"],
        "pe" => $quote["pe"],
        "marketcap" => $quote["marketCap"]
    ];
}

echo json_encode($response);
