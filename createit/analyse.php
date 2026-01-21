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
    <style>
        select {
            padding: 12px 15px; 
            font-size: 1em; 
            border-radius: 6px; 
            border: 2px solid #667eea;
            background: white;
            cursor: pointer;
            min-width: 250px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        select:hover {
            border-color: #764ba2;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }
        select:focus {
            outline: none;
            border-color: #764ba2;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .selector-card {
            margin-bottom: 20px;
        }
        .selector-card small {
            display: block;
            margin-top: 8px;
            color: #666;
            font-style: italic;
        }
    </style>

<body>

<header>Interactieve Analyse</header>

<main>
    <div class="page-title">
        <h1>Analyse &amp; Scenario Simulatie</h1>
        <p>Pas cijfers aan en bekijk voorspellingen per bedrijf</p>
    </div>

    <div class="card selector-card">
        <label for="companySelect"><strong>Selecteer een bedrijf</strong></label>
        <select id="companySelect">
            <option value="">Loading...</option>
        </select>
        <small>Jaar 1 cijfers worden automatisch via de API geladen.</small>
    </div>

    <div class="container analyse-cards">

        <div class="card analyse-card">
            <h3>Jaar 1 (API data)</h3>

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
        </div>

        <div class="card analyse-card">
            <h3>Jaar 2 (scenario)</h3>

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
        </div>

        <div class="card analyse-card">
            <h3>Jaar 3 (scenario)</h3>

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
        </div>

    </div>
</main>

<script>
// Global cache for all intraday data
let allSymbolsData = {};

// Load all company data on page start
async function loadAllData() {
    try {
        const res = await fetch("intraday_data.php");
        const data = await res.json();
        allSymbolsData = data.data;
        
        // Populate dropdown with symbols
        const select = document.getElementById("companySelect");
        select.innerHTML = "";
        
        const companyNames = {
            'AAPL': 'Apple',
            'MSFT': 'Microsoft',
            'GOOGL': 'Google',
            'AMZN': 'Amazon',
            'NVDA': 'NVIDIA',
            'TSLA': 'Tesla',
            'META': 'Meta',
            'IBM': 'IBM',
            'INTC': 'Intel',
            'AMD': 'AMD',
            'ASML': 'ASML',
            'JPM': 'JPMorgan',
            'V': 'Visa',
            'JNJ': 'Johnson & Johnson',
            'KO': 'Coca-Cola',
            'PG': 'Procter & Gamble',
            'NFLX': 'Netflix',
            'DIS': 'Disney',
            'CSCO': 'Cisco',
            'BA': 'Boeing'
        };
        
        data.symbols.forEach(item => {
            const option = document.createElement("option");
            option.value = item.symbol;
            const name = companyNames[item.symbol] || item.symbol;
            option.textContent = `${item.symbol} - ${name} ($${item.price.toFixed(2)})`;
            select.appendChild(option);
        });
        
        // Load analysis for first symbol
        if (data.symbols.length > 0) {
            loadYear1();
        }
    } catch (e) {
        console.error("Error loading all data:", e);
        // Fallback
        document.getElementById("companySelect").innerHTML = '<option value="AAPL">AAPL - Apple</option>';
        loadYear1();
    }
}

// Load Jaar 1 (Year 1) from cached data - use current price as base
function loadYear1() {
    const symbol = document.getElementById("companySelect").value;
    
    if (!symbol || !allSymbolsData[symbol]) {
        console.error("Symbol not found in cache:", symbol);
        return;
    }
    
    const company = allSymbolsData[symbol];
    const currentPrice = company.price || 200;
    
    // Year 1: Use current price to calculate revenue base
    // Assume 1 million units * current price = revenue
    const y1rev = currentPrice * 1000000;  // 1M units
    const y1cost = y1rev * 0.6;  // 60% cost ratio
    const y1net = y1rev - y1cost;  // 40% net margin
    
    // Fill Year 1 fields
    document.getElementById("y1rev").value = y1rev.toFixed(0);
    document.getElementById("y1cost").value = y1cost.toFixed(0);
    document.getElementById("y1net").value = y1net.toFixed(0);
    
    // Calculate scenarios for Year 2 & 3
    // Year 2: 5% growth
    const y2rev = y1rev * 1.05;
    const y2cost = y2rev * 0.6;
    
    // Year 3: 10% growth
    const y3rev = y1rev * 1.10;
    const y3cost = y3rev * 0.6;
    
    document.getElementById("y2rev").value = y2rev.toFixed(0);
    document.getElementById("y2cost").value = y2cost.toFixed(0);
    document.getElementById("y3rev").value = y3rev.toFixed(0);
    document.getElementById("y3cost").value = y3cost.toFixed(0);
    
    recalcScenario();
}

// Calculate Net Income based on Revenue & Cost (auto-calculate)
function recalcScenario() {
    const y2rev  = Number(document.getElementById("y2rev").value || 0);
    const y2cost = Number(document.getElementById("y2cost").value || 0);
    const y3rev  = Number(document.getElementById("y3rev").value || 0);
    const y3cost = Number(document.getElementById("y3cost").value || 0);

    document.getElementById("y2net").value = (y2rev - y2cost).toFixed(0);
    document.getElementById("y3net").value = (y3rev - y3cost).toFixed(0);
}

// Listen to user input and update Net Income in real-time
["y2rev","y2cost","y3rev","y3cost"].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener("input", recalcScenario);
    }
});

// Change company when dropdown changes
const companySelect = document.getElementById("companySelect");
if (companySelect) {
    companySelect.addEventListener("change", loadYear1);
}

// Load all data when page starts
loadAllData();
</script>

</body>
</html>
