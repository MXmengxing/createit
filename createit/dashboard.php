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
        <option value="">Loading...</option>
    </select>

    <div class="card">
        <canvas id="priceChart" height="100"></canvas>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            option.textContent = `${item.symbol} - ${name}`;
            select.appendChild(option);
        });
        
        // Load chart for first symbol
        if (data.symbols.length > 0) {
            loadChart(data.symbols[0].symbol);
        }
    } catch (e) {
        console.error("Error loading all data:", e);
        // Fallback
        document.getElementById("companySelect").innerHTML = '<option value="AAPL">AAPL - Apple</option>';
        loadChart("AAPL");
    }
}

// Load and display chart from cached data
function loadChart(symbol) {
    if (!symbol || !allSymbolsData[symbol]) {
        console.error("Symbol not found in cache:", symbol);
        return;
    }
    
    const company = allSymbolsData[symbol];
    const historical = company.historical || [];
    
    // Prepare chart data (oldest to newest)
    const labels = historical.map(d => {
        const date = new Date(d.date);
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    });
    
    const prices = historical.map(d => d.close);
    
    // Create or update chart
    const ctx = document.getElementById("priceChart").getContext("2d");
    
    // Destroy previous chart if exists
    if (window.priceChartInstance) {
        window.priceChartInstance.destroy();
    }
    
    window.priceChartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: `${symbol} Price (5min intervals)`,
                data: prices,
                borderColor: "#3498db",
                backgroundColor: "rgba(52, 152, 219, 0.1)",
                tension: 0.3,
                fill: true,
                pointRadius: 0,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    title: { display: true, text: "Price (USD)" }
                }
            }
        }
    });
}

// Load all data when page starts
loadAllData();

</body>
</html>
