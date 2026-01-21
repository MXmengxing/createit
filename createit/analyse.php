<?php include "navbar.php"; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Interactieve Analyse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

<header>Interactieve Analyse</header>

<main>
    <div class="page-title">
        <h1>Analyse &amp; Scenario Simulatie</h1>
        <p>Pas cijfers aan en bekijk voorspellingen per bedrijf</p>
    </div>

    <!-- Bedrijf选择 + 说明 -->
    <div class="card selector-card">
        <label for="companySelect"><strong>Selecteer een bedrijf</strong></label>
        <select id="companySelect">
            <option value="">Loading...</option>
        </select>
        <small>Jaar 1 cijfers worden automatisch via de API geladen.</small>
    </div>

    <div class="container analyse-cards">

        <!-- Jaar 1：只读，来自 API -->
        <div class="card analyse-card">
            <h3>Jaar 1 (Huidig)</h3>

            <div class="row-line">
                <label for="y1rev">Revenue</label>
                <input id="y1rev" type="number" disabled>
            </div>

            <div class="row-line">
                <label for="y1cost">Cost of Revenue</label>
                <input id="y1cost" type="number" disabled>
            </div>

            <div class="row-line">
                <label for="y1net">Net Income</label>
                <input id="y1net" type="number" disabled>
            </div>

            <div class="row-line">
                <label for="y1margin">Margin %</label>
                <input id="y1margin" type="text" disabled>
            </div>
        </div>

        <!-- Jaar 2：用户改 revenue/cost，净利润自动 -->
        <div class="card analyse-card">
            <h3>Jaar 2 (Scenario 5%)</h3>

            <div class="row-line">
                <label for="y2rev">Revenue</label>
                <input id="y2rev" type="number">
            </div>

            <div class="row-line">
                <label for="y2cost">Cost</label>
                <input id="y2cost" type="number">
            </div>

            <div class="row-line">
                <label for="y2net">Net Income (auto)</label>
                <input id="y2net" type="number" disabled>
            </div>

            <div class="row-line">
                <label for="y2margin">Margin %</label>
                <input id="y2margin" type="text" disabled>
            </div>
        </div>

        <!-- Jaar 3：同上 -->
        <div class="card analyse-card">
            <h3>Jaar 3 (Scenario 10%)</h3>

            <div class="row-line">
                <label for="y3rev">Revenue</label>
                <input id="y3rev" type="number">
            </div>

            <div class="row-line">
                <label for="y3cost">Cost</label>
                <input id="y3cost" type="number">
            </div>

            <div class="row-line">
                <label for="y3net">Net Income (auto)</label>
                <input id="y3net" type="number" disabled>
            </div>

            <div class="row-line">
                <label for="y3margin">Margin %</label>
                <input id="y3margin" type="text" disabled>
            </div>
        </div>

    </div>
</main>

<script>
// Global cache for intraday data
let allSymbolsData = {};

// Load all company data on page start
async function loadAllData() {
    try {
        const res = await fetch("intraday_data.php");
        const data = await res.json();
        
        if (!data.data || !data.symbols) {
            console.error("Invalid data from intraday_data.php");
            return;
        }
        
        allSymbolsData = data.data;
        
        const select = document.getElementById("companySelect");
        select.innerHTML = "";
        
        const companyNames = {
            'AAPL': 'Apple', 'MSFT': 'Microsoft', 'GOOGL': 'Google', 'AMZN': 'Amazon',
            'NVDA': 'NVIDIA', 'TSLA': 'Tesla', 'META': 'Meta', 'IBM': 'IBM',
            'INTC': 'Intel', 'AMD': 'AMD', 'ASML': 'ASML', 'JPM': 'JPMorgan',
            'V': 'Visa', 'JNJ': 'J&J', 'KO': 'Coca-Cola', 'PG': 'P&G',
            'NFLX': 'Netflix', 'DIS': 'Disney', 'CSCO': 'Cisco', 'BA': 'Boeing'
        };
        
        data.symbols.forEach(item => {
            const option = document.createElement("option");
            option.value = item.symbol;
            const name = companyNames[item.symbol] || item.symbol;
            option.textContent = `${item.symbol} - ${name} ($${item.price.toFixed(2)})`;
            select.appendChild(option);
        });
        
        // Load first company
        if (data.symbols.length > 0) {
            select.value = data.symbols[0].symbol;
            loadYear1();
        }
    } catch (error) {
        console.error("Error loading data:", error);
    }
}

// Load Jaar 1 from cached data
function loadYear1() {
    const symbol = document.getElementById("companySelect").value;
    
    if (!symbol || !allSymbolsData[symbol]) {
        console.error("Symbol not found:", symbol);
        return;
    }
    
    const company = allSymbolsData[symbol];
    const currentPrice = parseFloat(company.price) || 200;
    
    // Year 1: Current state based on price
    const y1rev = currentPrice * 1000000;  // 1M units × price
    const y1cost = y1rev * 0.60;           // 60% cost ratio
    const y1net = y1rev - y1cost;          // 40% net margin
    const y1margin = ((y1net / y1rev) * 100).toFixed(1);
    
    document.getElementById("y1rev").value = y1rev.toFixed(0);
    document.getElementById("y1cost").value = y1cost.toFixed(0);
    document.getElementById("y1net").value = y1net.toFixed(0);
    document.getElementById("y1margin").value = y1margin + "%";
    
    // Year 2: 5% revenue growth
    const y2rev = y1rev * 1.05;
    const y2cost = y2rev * 0.60;
    const y2net = y2rev - y2cost;
    const y2margin = ((y2net / y2rev) * 100).toFixed(1);
    
    document.getElementById("y2rev").value = y2rev.toFixed(0);
    document.getElementById("y2cost").value = y2cost.toFixed(0);
    document.getElementById("y2net").value = y2net.toFixed(0);
    document.getElementById("y2margin").value = y2margin + "%";
    
    // Year 3: 10% revenue growth
    const y3rev = y1rev * 1.10;
    const y3cost = y3rev * 0.60;
    const y3net = y3rev - y3cost;
    const y3margin = ((y3net / y3rev) * 100).toFixed(1);
    
    document.getElementById("y3rev").value = y3rev.toFixed(0);
    document.getElementById("y3cost").value = y3cost.toFixed(0);
    document.getElementById("y3net").value = y3net.toFixed(0);
    document.getElementById("y3margin").value = y3margin + "%";
}

// Recalculate on user input
function recalcScenario() {
    // Year 2
    const y2rev = parseFloat(document.getElementById("y2rev").value) || 0;
    const y2cost = parseFloat(document.getElementById("y2cost").value) || 0;
    const y2net = y2rev - y2cost;
    const y2margin = y2rev > 0 ? ((y2net / y2rev) * 100).toFixed(1) : 0;
    
    document.getElementById("y2net").value = y2net.toFixed(0);
    document.getElementById("y2margin").value = y2margin + "%";
    
    // Year 3
    const y3rev = parseFloat(document.getElementById("y3rev").value) || 0;
    const y3cost = parseFloat(document.getElementById("y3cost").value) || 0;
    const y3net = y3rev - y3cost;
    const y3margin = y3rev > 0 ? ((y3net / y3rev) * 100).toFixed(1) : 0;
    
    document.getElementById("y3net").value = y3net.toFixed(0);
    document.getElementById("y3margin").value = y3margin + "%";
}

// Add listeners for user input
["y2rev", "y2cost", "y3rev", "y3cost"].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener("input", recalcScenario);
    }
});

// Change on company select
const select = document.getElementById("companySelect");
if (select) {
    select.addEventListener("change", loadYear1);
}

// Load on page ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadAllData);
} else {
    loadAllData();
}
</script>

</body>
</html>
