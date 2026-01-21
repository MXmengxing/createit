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
    <style>
        .dashboard-container { max-width: 1200px; margin: 0 auto; }
        .company-selector { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 25px; 
            align-items: center;
        }
        .company-selector select,
        .selector-card select,
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
        .loading-status { 
            font-size: 0.9em; 
            color: #666; 
            font-style: italic;
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
            margin-bottom: 25px; 
        }
        .stat-card { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 20px; 
            border-radius: 8px; 
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card.high { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.low { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card.volume { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .stat-card h4 { 
            margin: 0 0 8px 0; 
            font-size: 0.85em; 
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card .value { 
            font-size: 1.6em; 
            font-weight: bold;
        }
        .chart-card { 
            background: white; 
            border-radius: 10px; 
            padding: 20px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .chart-card h3 { 
            margin-top: 0; 
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .chart-wrapper { 
            position: relative; 
            height: 450px; 
            width: 100%;
        }
        .data-info { 
            font-size: 0.85em; 
            color: #888; 
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
    </style>
</head>

<body>

<header>Dashboard</header>

<main>
    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Actuele marktdata en real-time grafieken van Alpha Vantage</p>
    </div>

    <div class="dashboard-container">
        <div class="company-selector">
            <label for="companySelect"><strong>Selecteer bedrijf:</strong></label>
            <select id="companySelect" onchange="loadChart(this.value)">
                <option value="">Loading companies...</option>
            </select>
            <span class="loading-status" id="loadingStatus"></span>
        </div>

        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <h4>Huidige Prijs</h4>
                <div class="value" id="currentPrice">-</div>
            </div>
            <div class="stat-card high">
                <h4>Dagelijks Maximum</h4>
                <div class="value" id="dayHigh">-</div>
            </div>
            <div class="stat-card low">
                <h4>Dagelijks Minimum</h4>
                <div class="value" id="dayLow">-</div>
            </div>
            <div class="stat-card volume">
                <h4>Gemiddeld Volume</h4>
                <div class="value" id="avgVolume">-</div>
            </div>
        </div>

        <div class="chart-card">
            <h3>Intraday Koersprijsverloop (5-min intervallen)</h3>
            <div class="chart-wrapper">
                <canvas id="priceChart"></canvas>
            </div>
            <div class="data-info" id="dataInfo"></div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>

<script>
// Global cache for all intraday data
let allSymbolsData = {};

// Load all company data on page start
async function loadAllData() {
    try {
        document.getElementById("loadingStatus").textContent = "Loading real API data...";
        
        const res = await fetch("intraday_data.php");
        const data = await res.json();
        
        if (!data.data || !data.symbols) {
            throw new Error("Invalid data structure from API");
        }
        
        allSymbolsData = data.data;
        
        // Populate dropdown with symbols and current prices
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
        
        // Load chart for first symbol
        if (data.symbols.length > 0) {
            document.getElementById("loadingStatus").textContent = `✓ Loaded ${data.symbols.length} companies`;
            loadChart(data.symbols[0].symbol);
        }
    } catch (e) {
        console.error("Error loading all data:", e);
        document.getElementById("loadingStatus").textContent = "Error loading data";
        document.getElementById("companySelect").innerHTML = '<option value="AAPL">AAPL - Apple</option>';
    }
}

// Load and display chart from cached data with statistics
function loadChart(symbol) {
    if (!symbol || !allSymbolsData[symbol]) {
        console.error("Symbol not found in cache:", symbol);
        return;
    }
    
    const company = allSymbolsData[symbol];
    const historical = company.historical || [];
    
    if (historical.length === 0) {
        console.error("No historical data for", symbol);
        return;
    }
    
    // Calculate statistics
    const closes = historical.map(d => d.close);
    const highs = historical.map(d => d.high);
    const lows = historical.map(d => d.low);
    const volumes = historical.map(d => d.volume);
    
    const currentPrice = closes[closes.length - 1];
    const dayHigh = Math.max(...highs);
    const dayLow = Math.min(...lows);
    const avgVolume = (volumes.reduce((a, b) => a + b, 0) / volumes.length).toLocaleString();
    
    // Update stat cards
    document.getElementById("currentPrice").textContent = `$${currentPrice.toFixed(2)}`;
    document.getElementById("dayHigh").textContent = `$${dayHigh.toFixed(2)}`;
    document.getElementById("dayLow").textContent = `$${dayLow.toFixed(2)}`;
    document.getElementById("avgVolume").textContent = avgVolume;
    
    // Prepare chart data (oldest to newest)
    const labels = historical.map(d => {
        const date = new Date(d.date);
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    });
    
    const prices = closes;
    
    // Create or update chart
    const ctx = document.getElementById("priceChart").getContext("2d");
    
    // Destroy previous chart if exists
    if (window.priceChartInstance) {
        window.priceChartInstance.destroy();
    }
    
    // Determine if price went up or down
    const priceChange = closes[closes.length - 1] - closes[0];
    const priceChangePercent = ((priceChange / closes[0]) * 100).toFixed(2);
    const lineColor = priceChange >= 0 ? "#27ae60" : "#e74c3c";
    const bgColor = priceChange >= 0 ? "rgba(39, 174, 96, 0.1)" : "rgba(231, 76, 60, 0.1)";
    
    window.priceChartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: `${symbol} Price (5min intervals)`,
                data: prices,
                borderColor: lineColor,
                backgroundColor: bgColor,
                tension: 0.4,
                fill: true,
                pointRadius: 0,
                pointHoverRadius: 5,
                borderWidth: 2.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true,
                    labels: { font: { size: 12, weight: 'bold' } }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    titleFont: { size: 12 },
                    bodyFont: { size: 11 },
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(ctx) {
                            return `Price: $${ctx.parsed.y.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        },
                        font: { size: 10 }
                    },
                    title: { 
                        display: true, 
                        text: "Price (USD)",
                        font: { size: 12, weight: 'bold' }
                    }
                },
                x: {
                    ticks: { font: { size: 9 } }
                }
            }
        }
    });
    
    // Update data info
    const lastRefreshed = company.lastRefreshed || new Date().toLocaleString();
    document.getElementById("dataInfo").innerHTML = `
        <strong>Data Source:</strong> Alpha Vantage API | 
        <strong>Change:</strong> ${priceChange >= 0 ? '+' : ''}${priceChange.toFixed(2)} (${priceChangePercent}%) | 
        <strong>Last Refreshed:</strong> ${lastRefreshed}
    `;
}

// Load all data when page starts
loadAllData();

</script>

</body>
</html>
