<?php include "navbar.php"; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Portefeuille</title>
    <link rel="stylesheet" href="style.css">
    <script>
        const savedTheme = localStorage.getItem("theme") || "light";
        document.documentElement.style.backgroundColor = savedTheme === "dark" ? "#000000" : "#ffffff";
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
// Load portfolio value from cached intraday data
async function loadPortfolio() {
    try {
        const res = await fetch("intraday_data.php");
        const data = await res.json();
        
        // Calculate total portfolio value
        // Assume 10 shares of each company for demo purposes
        let totalValue = 0;
        
        if (data.symbols && data.symbols.length > 0) {
            data.symbols.forEach(item => {
                totalValue += item.price * 10;  // 10 shares per company
            });
        }
        
        document.getElementById("totalValue").innerText = "€" + totalValue.toFixed(2);
    } catch (e) {
        console.error("Error loading portfolio:", e);
        document.getElementById("totalValue").innerText = "Error loading data";
    }
}

// Load portfolio on page start
loadPortfolio();
</script>

</body>
</html>
