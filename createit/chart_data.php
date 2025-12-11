<?php
include "api.php";

$symbol = $_GET["symbol"] ?? "AAPL";

$data = api("historical-price-full/$symbol?serietype=line");

if (isset($data["error"])) {
    echo json_encode(["error" => true]);
    exit;
}

$prices = [];
$dates = [];

foreach ($data["historical"] as $row) {
    $prices[] = $row["close"];
    $dates[]  = $row["date"];
}

echo json_encode([
    "dates"  => array_reverse($dates),
    "prices" => array_reverse($prices)
]);
?>
