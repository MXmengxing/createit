<?php include "navbar.php"; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script>
        const savedTheme = localStorage.getItem("theme") || "light";
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => {
                document.body.className = savedTheme;
            });
        } else {
            document.body.className = savedTheme;
        }
    </script>
    <script src="theme.js"></script>
</head>

<body>

<header>Dashboard</header>

<main>
    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Actuele marktdata en grafieken</p>
    </div>

    <select id="companySelect" onchange="loadChart(this.value)">
        <option value="AAPL">Apple</option>
        <option value="TSLA">Tesla</option>
        <option value="NVDA">NVIDIA</option>
        <option value="ASML">ASML</option>
    </select>

    <div class="card">
        <canvas id="priceChart" height="100"></canvas>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="charts.js"></script>

<script>
loadChart("AAPL");
</script>

</body>
</html>
