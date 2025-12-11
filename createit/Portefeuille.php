<?php include "navbar.php"; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Portefeuille</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="light">

<button class="toggle" onclick="toggleMode()">Light/Dark</button>

<header>Portefeuille</header>

<main>
    <div class="page-title">
        <h1>Jouw Portefeuille</h1>
        <p>Overzicht van posities & totale waarde</p>
    </div>

    <div class="portfolio-summary">
        <div class="summary-card card">
            <h3>Total Portfolio Value</h3>
            <div class="value" id="totalValue">Laden...</div>
        </div>
    </div>

    <div class="chart-section card">
        <h3>Portfolio Verdeling</h3>

        <div class="chart-container">
            <div class="chart-placeholder">Grafiek komt later</div>

            <div class="legend">
                <div class="legend-item"><div class="legend-color"></div> Apple</div>
                <div class="legend-item"><div class="legend-color"></div> Tesla</div>
                <div class="legend-item"><div class="legend-color"></div> ASML</div>
            </div>
        </div>
    </div>

</main>

<script>
async function loadPortfolio() {
    const res = await fetch("portfolio_data.php");
    const data = await res.json();

    document.getElementById("totalValue").innerText =
        "€" + data.total.toFixed(2);
}

loadPortfolio();

function toggleMode() {
    document.body.classList.toggle("dark");
    document.body.classList.toggle("light");
}
</script>

</body>
</html>
