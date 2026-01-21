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
        #priceChart { max-height: 500px; }
        .price-info { 
            display: grid; 
            grid-template-columns: 1fr 1fr 1fr 1fr; 
            gap: 10px; 
            margin-bottom: 20px; 
        }
        .info-card { 
            background: var(--bg-secondary, #f5f5f5); 
            padding: 15px; 
            border-radius: 8px; 
            text-align: center; 
        }
        .info-card h4 { margin: 0 0 5px 0; font-size: 0.9em; color: #666; }
        .info-card .value { font-size: 1.3em; font-weight: bold; color: #3498db; }
    </style>
</head>

<body>

<header>Dashboard</header>

<main>
    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Actuele marktdata en grafieken</p>
    </div>

    <div class="card">
        <label for="companySelect"><strong>Selecteer bedrijf:</strong></label>
        <select id="companySelect" onchange="loadChart(this.value)">
            <option value="">Loading companies...</option>
        </select>
    </div>

    <div class="price-info" id="priceInfo">
        <div class="info-card">
            <h4>Current Price</h4>
            <div class="value" id="currentPrice">-</div>
        </div>
        <div class="info-card">
            <h4>Day High</h4>
            <div class="value" id="dayHigh">-</div>
        </div>
        <div class="info-card">
            <h4>Day Low</h4>
            <div class="value" id="dayLow">-</div>
        </div>
        <div class="info-card">
            <h4>Volume (Avg)</h4>
            <div class="value" id="avgVolume">-</div>
        </div>
    </div>

    <div class="card">
        <div style="position: relative; height: 400px; width: 100%;">
            <canvas id="priceChart"></canvas>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
let allSymbolsData = {};
let chartInstance = null;

async function loadAllData() {
    try {
        const response = await fetch("intraday_data.php");
        const data = await response.json();
        
        if (!data.data || !data.symbols) {
            console.error("Invalid data structure:", data);
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
        
        if (data.symbols.length > 0) {
            select.value = data.symbols[0].symbol;
            loadChart(data.symbols[0].symbol);
        }
    } catch (error) {
        console.error("Error loading data:", error);
        alert("Error loading market data: " + error.message);
    }
}

function loadChart(symbol) {
    if (!symbol || !allSymbolsData[symbol]) {
        console.error("Symbol not found:", symbol);
        return;
    }
    
    const company = allSymbolsData[symbol];
    const historical = company.historical || [];
    
    if (historical.length === 0) {
        console.error("No historical data");
        return;
    }
    
    // Calculate statistics
    const closes = historical.map(d => parseFloat(d.close) || 0);
    const highs = historical.map(d => parseFloat(d.high) || 0);
    const lows = historical.map(d => parseFloat(d.low) || 0);
    const volumes = historical.map(d => parseInt(d.volume) || 0);
    
    const dayHigh = Math.max(...highs).toFixed(2);
    const dayLow = Math.min(...lows).toFixed(2);
    const avgVolume = (volumes.reduce((a, b) => a + b, 0) / volumes.length).toFixed(0);
    
    // Update info cards
    document.getElementById("currentPrice").textContent = "$" + company.price.toFixed(2);
    document.getElementById("dayHigh").textContent = "$" + dayHigh;
    document.getElementById("dayLow").textContent = "$" + dayLow;
    document.getElementById("avgVolume").textContent = (avgVolume / 1000000).toFixed(2) + "M";
    
    // Prepare chart labels (times only)
    const labels = historical.map(d => {
        try {
            const dt = new Date(d.date);
            return dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        } catch (e) {
            return d.date;
        }
    });
    
    // Destroy old chart
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    // Create new chart
    const ctx = document.getElementById("priceChart");
    chartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: `${symbol} Closing Price`,
                data: closes,
                borderColor: "#3498db",
                backgroundColor: "rgba(52, 152, 219, 0.08)",
                tension: 0.2,
                fill: true,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: true,
                    labels: { padding: 15, usePointStyle: true }
                },
                title: {
                    display: true,
                    text: `${symbol} - 5-Minute Intraday Price Chart`,
                    padding: 15,
                    font: { size: 14, weight: 'bold' }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    },
                    title: {
                        display: true,
                        text: 'Price (USD)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Time (EST)'
                    }
                }
            }
        }
    });
}

// Load data when page is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadAllData);
} else {
    loadAllData();
}
</script>

</body>
</html>
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
